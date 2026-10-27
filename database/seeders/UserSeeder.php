<?php

namespace Database\Seeders;

use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $passwordEnvironment = [
            'admin' => 'POLIKLINIK_SEED_ADMIN_PASSWORD',
            'dokter' => 'POLIKLINIK_SEED_DOCTOR_PASSWORD',
            'pasien' => 'POLIKLINIK_SEED_PATIENT_PASSWORD',
        ];

        $passwords = [];
        foreach ($passwordEnvironment as $role => $environmentKey) {
            $passwords[$role] = env($environmentKey);
            $password = $passwords[$role];
            if (! is_string($password) || strlen($password) < 12) {
                throw new RuntimeException("Set {$environmentKey} in .env to a value of at least 12 characters.");
            }
        }

        $poli = Poli::firstOrCreate(
            ['nama_poli' => 'Umum'],
            ['keterangan' => 'Poli umum untuk pemeriksaan pasien.'],
        );

        User::updateOrCreate(
            ['email' => 'admin@polipoli.test'],
            ['nama' => 'Admin Poliklinik', 'role' => 'admin', 'password' => $passwords['admin']],
        );

        $dokter = User::updateOrCreate(
            ['email' => 'dokter@polipoli.test'],
            [
                'nama' => 'Dr. Budi Santoso',
                'role' => 'dokter',
                'id_poli' => $poli->id,
                'password' => $passwords['dokter'],
            ],
        );

        $hariIni = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ][today()->dayOfWeek];

        JadwalPeriksa::updateOrCreate(
            ['id_dokter' => $dokter->id, 'hari' => $hariIni, 'jam_mulai' => '09:00'],
            ['jam_selesai' => '12:00'],
        );

        foreach (range(1, 4) as $number) {
            User::updateOrCreate(
                ['email' => "pasien{$number}@polipoli.test"],
                [
                    'nama' => "Pasien Demo {$number}",
                    'role' => 'pasien',
                    'no_rm' => now()->format('Ym').'-P'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                    'password' => $passwords['pasien'],
                ],
            );
        }
    }
}
