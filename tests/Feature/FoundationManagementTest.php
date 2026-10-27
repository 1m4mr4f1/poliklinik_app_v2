<?php

namespace Tests\Feature;

use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FoundationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_one_admin_one_doctor_and_four_patients(): void
    {
        $environmentKeys = [
            'POLIKLINIK_SEED_ADMIN_PASSWORD' => 'seed-admin-password',
            'POLIKLINIK_SEED_DOCTOR_PASSWORD' => 'seed-doctor-password',
            'POLIKLINIK_SEED_PATIENT_PASSWORD' => 'seed-patient-password',
        ];
        $previousValues = [];

        foreach ($environmentKeys as $key => $value) {
            $previousValues[$key] = getenv($key);
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        try {
            $this->seed();
        } finally {
            foreach ($previousValues as $key => $value) {
                if ($value === false) {
                    putenv($key);
                    unset($_ENV[$key], $_SERVER[$key]);

                    continue;
                }

                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(1, User::where('role', 'dokter')->count());
        $this->assertSame(4, User::where('role', 'pasien')->count());
        $this->assertSame(1, Poli::where('nama_poli', 'Umum')->count());
        $this->assertSame(1, JadwalPeriksa::where('id_dokter', User::where('role', 'dokter')->value('id'))->count());
        $this->assertTrue(Hash::check('seed-admin-password', User::where('role', 'admin')->firstOrFail()->password));
    }

    public function test_admin_can_create_and_manage_poli_doctor_patient_and_medicine(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->post(route('admin.polis.store'), ['nama_poli' => 'Umum', 'keterangan' => 'Poli umum'])
            ->assertRedirect(route('admin.polis.index'));
        $poli = Poli::firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.dokter.store'), [
                'nama' => 'Dokter Uji',
                'alamat' => 'Jalan Sehat 1',
                'no_ktp' => '1234567890123456',
                'no_hp' => '08123456789',
                'id_poli' => $poli->id,
                'email' => 'dokter-uji@example.test',
                'password' => 'password-dokter',
            ])
            ->assertRedirect(route('admin.dokter.index'));
        $dokter = User::where('email', 'dokter-uji@example.test')->firstOrFail();
        $this->assertSame('dokter', $dokter->role);
        $this->assertSame($poli->id, $dokter->id_poli);

        $this->actingAs($admin)
            ->post(route('admin.pasien.store'), [
                'nama' => 'Pasien Uji',
                'alamat' => 'Jalan Sehat 2',
                'no_ktp' => '1234567890123457',
                'no_hp' => '08123456780',
                'email' => 'pasien-uji@example.test',
                'password' => 'password-pasien',
            ])
            ->assertRedirect(route('admin.pasien.index'));
        $pasien = User::where('email', 'pasien-uji@example.test')->firstOrFail();
        $this->assertSame('pasien', $pasien->role);
        $this->assertNotEmpty($pasien->no_rm);

        $this->actingAs($admin)
            ->post(route('admin.obat.store'), [
                'nama_obat' => 'Paracetamol',
                'kemasan' => 'Tablet',
                'harga' => 5000,
                'stok' => 20,
            ])
            ->assertRedirect(route('admin.obat.index'));
        $this->assertDatabaseHas('obat', ['nama_obat' => 'Paracetamol', 'harga' => 5000, 'stok' => 20]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.polis.index'))
            ->assertSee(route('admin.obat.index'));

        $this->actingAs($pasien)->get(route('admin.polis.index'))->assertForbidden();
    }

    public function test_doctor_can_manage_only_non_overlapping_personal_schedule_slots(): void
    {
        $dokter = $this->user('dokter');
        $otherDoctor = $this->user('dokter');

        $this->actingAs($dokter)
            ->post(route('dokter.jadwal-periksa.store'), [
                'hari' => 'Senin',
                'jam_mulai' => '09:00',
                'jam_selesai' => '12:00',
            ])
            ->assertRedirect(route('dokter.jadwal-periksa.index'));
        $jadwal = JadwalPeriksa::where('id_dokter', $dokter->id)->firstOrFail();

        $this->actingAs($dokter)
            ->post(route('dokter.jadwal-periksa.store'), [
                'hari' => 'Senin',
                'jam_mulai' => '11:00',
                'jam_selesai' => '13:00',
            ])
            ->assertSessionHasErrors('jam_mulai');

        $this->actingAs($dokter)
            ->post(route('dokter.jadwal-periksa.store'), [
                'hari' => 'Senin',
                'jam_mulai' => '13:00',
                'jam_selesai' => '12:00',
            ])
            ->assertSessionHasErrors('jam_selesai');

        $this->actingAs($otherDoctor)
            ->get(route('dokter.jadwal-periksa.edit', $jadwal))
            ->assertNotFound();

        $this->actingAs($dokter)
            ->get(route('dokter.jadwal-periksa.index'))
            ->assertOk()
            ->assertSee('Senin');
    }

    private function user(string $role): User
    {
        return User::create([
            'nama' => ucfirst($role).' Uji',
            'role' => $role,
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password-uji',
        ]);
    }
}
