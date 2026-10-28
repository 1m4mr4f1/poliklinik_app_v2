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

        $poliUmum = Poli::firstOrCreate(
            ['nama_poli' => 'Umum'],
            ['keterangan' => 'Poli umum untuk pemeriksaan pasien.'],
        );

        $poliGigi = Poli::firstOrCreate(
            ['nama_poli' => 'Gigi'],
            ['keterangan' => 'Poli gigi untuk pemeriksaan pasien.'],
        );

        User::updateOrCreate(
            ['email' => 'admin@polipoli.test'],
            ['nama' => 'Admin Poliklinik', 'role' => 'admin', 'password' => $passwords['admin']],
        );

        $dokterUmum = User::updateOrCreate(
            ['email' => 'dokter@polipoli.test'],
            [
                'nama' => 'Dr. Budi Santoso',
                'role' => 'dokter',
                'id_poli' => $poliUmum->id,
                'password' => $passwords['dokter'],
            ],
        );

        $dokterGigi = User::updateOrCreate(
            ['email' => 'dokter2@polipoli.test'],
            [
                'nama' => 'Dr. Rina Putri Wijaya',
                'role' => 'dokter',
                'id_poli' => $poliGigi->id,
                'password' => $passwords['dokter'],
            ],
        );

        $hariKerja = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        foreach ([$dokterUmum, $dokterGigi] as $dokter) {
            foreach ($hariKerja as $hari) {
                JadwalPeriksa::updateOrCreate(
                    ['id_dokter' => $dokter->id, 'hari' => $hari, 'jam_mulai' => '09:00'],
                    ['jam_selesai' => '12:00'],
                );
            }
        }

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
