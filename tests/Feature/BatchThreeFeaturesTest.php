<?php

namespace Tests\Feature;

use App\Exports\TableExport;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use App\Notifications\PaymentStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class BatchThreeFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_notifications_are_created_once_for_the_doctor_and_patient(): void
    {
        [$pasien, $jadwal, $dokter] = $this->makePatientAndSchedule();
        $pendaftaran = $this->makeRegistration($pasien, $jadwal);
        $periksa = Periksa::create([
            'id_daftar_poli' => $pendaftaran->id,
            'tgl_periksa' => now(),
            'catatan' => 'Istirahat cukup',
            'biaya_periksa' => 150000,
        ]);
        $pembayaran = $periksa->pembayaran;
        $pembayaran->update([
            'bukti_path' => 'bukti/bayar-1.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->assertSame(1, $dokter->notifications()->count());
        $this->assertSame('bukti_pembayaran', $dokter->notifications()->first()->data['event']);

        $pembayaran->update(['bukti_path' => 'bukti/bayar-2.jpg']);
        $this->assertSame(1, $dokter->notifications()->count());

        $pembayaran->update(['status' => 'lunas']);
        $this->assertSame(1, $pasien->notifications()->count());
        $this->assertSame('pembayaran_lunas', $pasien->notifications()->first()->data['event']);

        $pembayaran->update(['status' => 'menunggu_verifikasi']);
        $pembayaran->update(['status' => 'lunas']);
        $this->assertSame(1, $pasien->notifications()->count());
    }

    public function test_notification_list_and_read_action_are_limited_to_the_authenticated_user(): void
    {
        $owner = $this->makeUser('pasien', 'owner');
        $other = $this->makeUser('pasien', 'other');
        $notification = new PaymentStatusNotification('event:1', 1, 'pembayaran_lunas', 'Pembayaran lunas.', route('pasien.dashboard'));
        $owner->notify($notification);

        $this->actingAs($other)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Belum ada notifikasi.')
            ->assertDontSee('Pembayaran lunas.');

        $this->actingAs($other)
            ->post(route('notifications.read', $owner->notifications()->first()->id))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Pembayaran lunas.')
            ->assertSee('1 notifikasi belum dibaca.');

        $this->actingAs($owner)
            ->post(route('notifications.read', $owner->notifications()->first()->id))
            ->assertRedirect();

        $this->assertSame(0, $owner->unreadNotifications()->count());
    }

    public function test_patient_history_is_ordered_and_detail_is_private(): void
    {
        [$pasien, $jadwal] = $this->makePatientAndSchedule();
        $older = $this->makeRegistration($pasien, $jadwal, 1, '2026-10-28');
        $older->forceFill(['created_at' => Carbon::parse('2026-10-27 10:00:00')])->save();
        $newer = $this->makeRegistration($pasien, $jadwal, 2, '2026-10-29');
        $newer->forceFill(['created_at' => Carbon::parse('2026-10-28 10:00:00')])->save();

        $periksa = Periksa::create([
            'id_daftar_poli' => $older->id,
            'tgl_periksa' => now(),
            'catatan' => 'Kontrol kembali minggu depan.',
            'biaya_periksa' => 125000,
        ]);
        $obat = Obat::create(['nama_obat' => 'Paracetamol', 'kemasan' => 'Tablet', 'harga' => 5000, 'stok' => 10]);
        DetailPeriksa::create(['id_periksa' => $periksa->id, 'id_obat' => $obat->id]);
        $other = $this->makeUser('pasien', 'other');

        $this->actingAs($pasien)
            ->get(route('pasien.riwayat.index'))
            ->assertOk()
            ->assertSeeInOrder(['2', '1'])
            ->assertSee('Menunggu pemeriksaan')
            ->assertSee('Sudah diperiksa')
            ->assertSee(route('pasien.riwayat.show', $older));

        $this->actingAs($pasien)
            ->get(route('pasien.riwayat.show', $older))
            ->assertOk()
            ->assertSee('Kontrol kembali minggu depan.')
            ->assertSee('Paracetamol')
            ->assertSee('Rp125.000');

        $this->actingAs($other)
            ->get(route('pasien.riwayat.show', $older))
            ->assertNotFound();

        $this->actingAs($pasien)
            ->get(route('pasien.riwayat.show', $newer))
            ->assertNotFound();
    }

    public function test_exports_include_required_columns_and_are_scoped_by_role(): void
    {
        Excel::fake();
        [$pasien, $jadwal, $dokter] = $this->makePatientAndSchedule();
        $otherPoli = Poli::create(['nama_poli' => 'Gigi']);
        $otherDoctor = User::create([
            'nama' => 'Dokter Lain',
            'role' => 'dokter',
            'id_poli' => $otherPoli->id,
            'email' => 'dokter-lain@example.test',
            'password' => 'password',
        ]);
        $otherSchedule = JadwalPeriksa::create([
            'id_dokter' => $otherDoctor->id,
            'hari' => 'Senin',
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
        ]);
        $pendaftaran = $this->makeRegistration($pasien, $jadwal);
        Periksa::create([
            'id_daftar_poli' => $pendaftaran->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
        ]);
        $otherPatient = $this->makeUser('pasien', 'other');
        $otherRegistration = $this->makeRegistration($otherPatient, $otherSchedule, 1);
        Periksa::create([
            'id_daftar_poli' => $otherRegistration->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
        ]);
        $obat = Obat::create(['nama_obat' => 'Paracetamol', 'kemasan' => 'Tablet', 'harga' => 5000, 'stok' => 12]);
        $admin = $this->makeUser('admin', 'admin');

        $this->actingAs($admin)->get(route('admin.dokter.index'))->assertOk()->assertSee(route('admin.export.dokter'));
        $this->actingAs($admin)->get(route('admin.pasien.index'))->assertOk()->assertSee(route('admin.export.pasien'));
        $this->actingAs($admin)->get(route('admin.obat.index'))->assertOk()->assertSee(route('admin.export.obat'));
        $this->actingAs($dokter)->get(route('dokter.jadwal-periksa.index'))->assertOk()->assertSee(route('dokter.export.jadwal'));
        $this->actingAs($dokter)->get(route('dokter.riwayat-pasien.index'))->assertOk()->assertSee(route('dokter.export.riwayat'));

        $this->actingAs($admin)->get(route('admin.export.dokter'))->assertOk();
        Excel::assertDownloaded('dokter.xlsx');
        $this->actingAs($admin)->get(route('admin.export.pasien'))->assertOk();
        Excel::assertDownloaded('pasien.xlsx');
        $this->actingAs($admin)->get(route('admin.export.obat'))->assertOk();
        Excel::assertDownloaded('obat.xlsx', fn (TableExport $export): bool => in_array('Stok', $export->headings(), true)
            && in_array('Harga', $export->headings(), true)
            && in_array(['Paracetamol', 'Tablet', 12, 5000], $export->array(), true));

        $this->actingAs($dokter)->get(route('dokter.export.jadwal'))->assertOk();
        Excel::assertDownloaded('jadwal-periksa.xlsx', fn (TableExport $export): bool => count($export->array()) === 1
            && $export->array()[0][1] === $dokter->nama);
        $this->actingAs($dokter)->get(route('dokter.export.riwayat'))->assertOk();
        Excel::assertDownloaded('riwayat-pasien.xlsx', fn (TableExport $export): bool => count($export->array()) === 1
            && $export->array()[0][0] === $pasien->nama);

        $this->actingAs($pasien)->get(route('admin.export.obat'))->assertForbidden();
        $this->actingAs($admin)->get(route('dokter.export.riwayat'))->assertForbidden();
    }

    private function makePatientAndSchedule(): array
    {
        $poli = Poli::create(['nama_poli' => 'Umum']);
        $dokter = User::create([
            'nama' => 'Dokter Uji',
            'role' => 'dokter',
            'id_poli' => $poli->id,
            'email' => 'dokter-'.uniqid().'@example.test',
            'password' => 'password',
        ]);
        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Rabu',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);
        $pasien = $this->makeUser('pasien', 'patient');

        return [$pasien, $jadwal, $dokter];
    }

    private function makeRegistration(User $pasien, JadwalPeriksa $jadwal, int $number = 1, string $date = '2026-10-28'): DaftarPoli
    {
        return DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => $date,
            'keluhan' => 'Pemeriksaan',
            'no_antrian' => $number,
        ]);
    }

    private function makeUser(string $role, string $prefix): User
    {
        return User::create([
            'nama' => ucfirst($prefix).' Uji',
            'role' => $role,
            'email' => $prefix.'-'.uniqid().'@example.test',
            'password' => 'password',
        ]);
    }
}
