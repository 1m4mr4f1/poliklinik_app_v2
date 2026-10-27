<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PoliklinikSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_schema_contains_the_fields_required_by_the_material_and_final_specification(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['nama', 'alamat', 'no_ktp', 'no_hp', 'no_rm', 'role', 'id_poli']));
        $this->assertTrue(Schema::hasColumns('jadwal_periksa', ['id_dokter', 'hari', 'jam_mulai', 'jam_selesai']));
        $this->assertTrue(Schema::hasColumns('daftar_poli', ['id_pasien', 'id_jadwal', 'tanggal_janji', 'keluhan', 'no_antrian']));
        $this->assertTrue(Schema::hasColumns('periksa', ['id_daftar_poli', 'tgl_periksa', 'catatan', 'biaya_periksa']));
        $this->assertTrue(Schema::hasColumns('obat', ['nama_obat', 'kemasan', 'harga', 'stok']));
        $this->assertTrue(Schema::hasColumns('antrian_harian', ['id_jadwal', 'tanggal', 'no_antrian_dipanggil', 'no_antrian_diperiksa']));
        $this->assertTrue(Schema::hasColumns('pembayaran', ['id_periksa', 'status', 'bukti_path', 'diverifikasi_oleh', 'diverifikasi_pada']));
    }

    public function test_core_models_resolve_their_domain_relationships(): void
    {
        $poli = Poli::create(['nama_poli' => 'Umum']);
        $dokter = User::create([
            'nama' => 'Dokter Uji',
            'role' => 'dokter',
            'id_poli' => $poli->id,
            'email' => 'dokter@example.test',
            'password' => 'password',
        ]);
        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Rabu',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);
        $pasien = User::create([
            'nama' => 'Pasien Uji',
            'role' => 'pasien',
            'email' => 'pasien@example.test',
            'password' => 'password',
        ]);
        $pendaftaran = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => '2026-10-28',
            'keluhan' => 'Pemeriksaan rutin',
            'no_antrian' => 1,
        ]);
        $periksa = Periksa::create([
            'id_daftar_poli' => $pendaftaran->id,
            'tgl_periksa' => '2026-10-28 09:00:00',
            'biaya_periksa' => 150000,
        ]);
        $obat = Obat::create([
            'nama_obat' => 'Obat Uji',
            'harga' => 10000,
            'stok' => 5,
        ]);
        $detail = DetailPeriksa::create([
            'id_periksa' => $periksa->id,
            'id_obat' => $obat->id,
        ]);
        $pembayaran = $periksa->pembayaran;

        $this->assertSame($poli->id, $dokter->fresh()->poli->id);
        $this->assertSame($dokter->id, $jadwal->fresh()->dokter->id);
        $this->assertSame($pasien->id, $pendaftaran->fresh()->pasien->id);
        $this->assertSame($jadwal->id, $pendaftaran->fresh()->jadwalPeriksa->id);
        $this->assertSame($pendaftaran->id, $periksa->fresh()->daftarPoli->id);
        $this->assertSame($obat->id, $detail->fresh()->obat->id);
        $this->assertSame($periksa->id, $pembayaran->fresh()->periksa->id);
    }

    public function test_queue_numbers_are_unique_per_schedule_and_appointment_date(): void
    {
        $poli = Poli::create(['nama_poli' => 'Umum']);
        $dokter = User::create([
            'nama' => 'Dokter Uji',
            'role' => 'dokter',
            'id_poli' => $poli->id,
            'email' => 'dokter@example.test',
            'password' => 'password',
        ]);
        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Rabu',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);
        $pasien = User::create([
            'nama' => 'Pasien Uji',
            'role' => 'pasien',
            'email' => 'pasien@example.test',
            'password' => 'password',
        ]);

        DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => '2026-10-28',
            'keluhan' => 'Pemeriksaan rutin',
            'no_antrian' => 1,
        ]);

        $this->expectException(QueryException::class);

        DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => '2026-10-28',
            'keluhan' => 'Pemeriksaan lanjutan',
            'no_antrian' => 1,
        ]);
    }
}
