<?php

namespace Tests\Feature;

use App\Events\AntrianDiperbarui;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BatchFourFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_uploads_private_proof_and_admin_verifies_payment_once(): void
    {
        Storage::fake('local');
        [$pasien, $jadwal, $dokter, $pendaftaran] = $this->makeRegistration();
        $periksa = Periksa::create([
            'id_daftar_poli' => $pendaftaran->id,
            'tgl_periksa' => now(),
            'catatan' => 'Pemeriksaan selesai',
            'biaya_periksa' => 175000,
        ]);
        $pembayaran = $periksa->pembayaran;
        $admin = $this->makeUser('admin');
        $otherPatient = $this->makeUser('pasien');

        $this->actingAs($otherPatient)
            ->post(route('pasien.pembayaran.upload', $pembayaran), ['bukti' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertNotFound();

        $this->actingAs($pasien)
            ->get(route('pasien.pembayaran.index'))
            ->assertOk()
            ->assertSee('Rp175.000')
            ->assertSee('Belum dibayar');

        $this->actingAs($pasien)
            ->post(route('pasien.pembayaran.upload', $pembayaran), ['bukti' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertRedirect()
            ->assertSessionHas('success');

        $pembayaran->refresh();
        $this->assertSame('menunggu_verifikasi', $pembayaran->status);
        $this->assertNotNull($pembayaran->bukti_path);
        Storage::disk('local')->assertExists($pembayaran->bukti_path);
        $this->assertSame(1, $dokter->notifications()->count());
        $this->assertSame(0, $pasien->notifications()->count());

        $this->actingAs($otherPatient)
            ->get(route('payments.proof', $pembayaran))
            ->assertNotFound();
        $this->actingAs($pasien)
            ->get(route('payments.proof', $pembayaran))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.pembayaran.index'))
            ->assertOk()
            ->assertSee($pasien->nama)
            ->assertSee('Konfirmasi lunas');
        $this->actingAs($admin)
            ->post(route('admin.pembayaran.verify', $pembayaran))
            ->assertRedirect()
            ->assertSessionHas('success');

        $pembayaran->refresh();
        $this->assertSame('lunas', $pembayaran->status);
        $this->assertSame($admin->id, $pembayaran->diverifikasi_oleh);
        $this->assertNotNull($pembayaran->diverifikasi_pada);
        $this->assertSame(1, $pasien->notifications()->count());
        $this->assertSame(1, $dokter->notifications()->count());

        $this->actingAs($admin)
            ->post(route('admin.pembayaran.verify', $pembayaran))
            ->assertSessionHas('success', 'Pembayaran ini sudah berstatus lunas.');
        $this->assertSame(1, $pasien->notifications()->count());

        $this->actingAs($pasien)
            ->post(route('pasien.pembayaran.upload', $pembayaran), ['bukti' => UploadedFile::fake()->image('bukti-lain.jpg')])
            ->assertUnprocessable();
    }

    public function test_payment_proof_upload_rejects_non_images_and_files_over_limit(): void
    {
        Storage::fake('local');
        [$pasien, , , $pendaftaran] = $this->makeRegistration();
        $periksa = Periksa::create([
            'id_daftar_poli' => $pendaftaran->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 50000,
        ]);

        $this->actingAs($pasien)
            ->post(route('pasien.pembayaran.upload', $periksa->pembayaran), ['bukti' => UploadedFile::fake()->create('file.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('bukti');

        $this->actingAs($pasien)
            ->post(route('pasien.pembayaran.upload', $periksa->pembayaran), ['bukti' => UploadedFile::fake()->image('large.png')->size(6000)])
            ->assertSessionHasErrors('bukti');

        $this->assertSame('belum_dibayar', $periksa->pembayaran->fresh()->status);
    }

    public function test_doctor_exam_creates_medical_details_and_payment_for_called_patient(): void
    {
        [$pasien, $jadwal, $dokter, $pendaftaran] = $this->makeRegistration();
        $obat = Obat::create(['nama_obat' => 'Paracetamol', 'kemasan' => 'Tablet', 'harga' => 5000, 'stok' => 10]);
        Event::fake([AntrianDiperbarui::class]);
        $this->actingAs($dokter)
            ->get(route('dokter.pemeriksaan.create', $pendaftaran))
            ->assertForbidden();

        $this->actingAs($dokter)
            ->post(route('dokter.antrian.panggil-berikutnya', $jadwal))
            ->assertSessionHas('success');

        $this->actingAs($dokter)
            ->get(route('dokter.pemeriksaan.create', $pendaftaran))
            ->assertOk()
            ->assertSee($pasien->nama)
            ->assertSee($obat->nama_obat);

        $this->actingAs($dokter)
            ->post(route('dokter.pemeriksaan.store', $pendaftaran), [
                'catatan' => 'Minum obat setelah makan.',
                'obat_ids' => [$obat->id],
            ])
            ->assertRedirect(route('dokter.dashboard'))
            ->assertSessionHas('success');

        $periksa = Periksa::where('id_daftar_poli', $pendaftaran->id)->firstOrFail();
        $this->assertSame('Minum obat setelah makan.', $periksa->catatan);
        $this->assertSame(1, DetailPeriksa::where('id_periksa', $periksa->id)->count());
        $this->assertSame(155000, $periksa->biaya_periksa);
        $this->assertSame('belum_dibayar', $periksa->pembayaran->status);
        $this->assertDatabaseHas('antrian_harian', [
            'id_jadwal' => $jadwal->id,
            'no_antrian_dipanggil' => 1,
            'no_antrian_diperiksa' => 1,
        ]);
        Event::assertDispatched(
            AntrianDiperbarui::class,
            fn (AntrianDiperbarui $event): bool => $event->idJadwal === $jadwal->id
                && $event->tanggal === today()->toDateString()
                && $event->nomorDipanggil === 1
                && $event->nomorDiperiksa === 1
                && $event->broadcastWith() === [
                    'id_jadwal' => $jadwal->id,
                    'tanggal' => today()->toDateString(),
                    'nomor_dipanggil' => 1,
                    'nomor_diperiksa' => 1,
                    'jumlah_belum_diperiksa' => 0,
                ],
        );
    }

    public function test_all_roles_can_change_only_their_own_password_after_validation(): void
    {
        foreach (['admin', 'dokter', 'pasien'] as $role) {
            $user = $this->makeUser($role);

            $this->actingAs($user)
                ->get(route('profile.password.edit'))
                ->assertOk()
                ->assertSee('Password lama')
                ->assertSee('Konfirmasi password baru');

            $this->actingAs($user)
                ->put(route('profile.password.update'), [
                    'current_password' => 'wrong-password',
                    'password' => 'new-password-123',
                    'password_confirmation' => 'new-password-123',
                ])
                ->assertSessionHasErrors('current_password');

            $this->actingAs($user)
                ->put(route('profile.password.update'), [
                    'current_password' => 'old-password',
                    'password' => 'short',
                    'password_confirmation' => 'short',
                ])
                ->assertSessionHasErrors('password');

            $this->actingAs($user)
                ->put(route('profile.password.update'), [
                    'current_password' => 'old-password',
                    'password' => 'old-password',
                    'password_confirmation' => 'old-password',
                ])
                ->assertSessionHasErrors('password');

            $this->actingAs($user)
                ->put(route('profile.password.update'), [
                    'current_password' => 'old-password',
                    'password' => 'new-password-123',
                    'password_confirmation' => 'mismatch-password',
                ])
                ->assertSessionHasErrors('password');

            $this->actingAs($user)
                ->put(route('profile.password.update'), [
                    'current_password' => 'old-password',
                    'password' => 'new-password-123',
                    'password_confirmation' => 'new-password-123',
                ])
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
            $this->assertFalse(Str::contains($user->fresh()->password, 'new-password-123'));
        }
    }

    public function test_non_admin_cannot_verify_payments_and_non_doctor_cannot_submit_examination(): void
    {
        [$pasien, , $dokter, $pendaftaran] = $this->makeRegistration();
        $periksa = Periksa::create([
            'id_daftar_poli' => $pendaftaran->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 50000,
        ]);
        $periksa->pembayaran()->update([
            'status' => 'menunggu_verifikasi',
            'bukti_path' => 'bukti-pembayaran/test.jpg',
        ]);

        $this->actingAs($pasien)
            ->post(route('admin.pembayaran.verify', $periksa->pembayaran))
            ->assertForbidden();
        $this->actingAs($pasien)
            ->post(route('dokter.pemeriksaan.store', $pendaftaran), ['biaya_periksa' => 50000])
            ->assertForbidden();
        $this->actingAs($dokter)
            ->get(route('admin.pembayaran.index'))
            ->assertForbidden();
    }

    private function makeRegistration(): array
    {
        $poli = Poli::create(['nama_poli' => 'Umum']);
        $dokter = User::create([
            'nama' => 'Dokter Uji',
            'role' => 'dokter',
            'id_poli' => $poli->id,
            'email' => 'dokter-'.uniqid().'@example.test',
            'password' => 'old-password',
        ]);
        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => [
                0 => 'Minggu',
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            ][today()->dayOfWeek],
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);
        $pasien = $this->makeUser('pasien');
        $pendaftaran = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => today(),
            'keluhan' => 'Pemeriksaan',
            'no_antrian' => 1,
        ]);

        return [$pasien, $jadwal, $dokter, $pendaftaran];
    }

    private function makeUser(string $role): User
    {
        return User::create([
            'nama' => ucfirst($role).' Uji',
            'role' => $role,
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'old-password',
        ]);
    }
}
