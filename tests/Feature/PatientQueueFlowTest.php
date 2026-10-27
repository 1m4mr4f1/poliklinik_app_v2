<?php

namespace Tests\Feature;

use App\Events\AntrianDiperbarui;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PatientQueueFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_registration_creates_a_patient_account_and_medical_record_number(): void
    {
        $response = $this->post(route('register'), [
            'nama' => 'Pasien Baru',
            'alamat' => 'Jalan Sehat 1',
            'no_ktp' => '1234567890123456',
            'no_hp' => '081234567890',
            'email' => 'pasien-baru@example.test',
            'password' => 'password-aman',
            'password_confirmation' => 'password-aman',
        ]);

        $response->assertRedirect(route('login'));
        $pasien = User::where('email', 'pasien-baru@example.test')->firstOrFail();
        $this->assertSame('pasien', $pasien->role);
        $this->assertMatchesRegularExpression('/^\d{6}-\d{3,}$/', $pasien->no_rm);
        $this->assertTrue(Hash::check('password-aman', $pasien->password));
    }

    public function test_patient_can_register_once_for_a_matching_practice_date(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(8, 0));
        [$pasien, $jadwal] = $this->makePatientAndSchedule(today()->dayOfWeek);

        $response = $this->actingAs($pasien)->post(route('pasien.pendaftaran.store'), [
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => today()->toDateString(),
            'keluhan' => 'Demam sejak kemarin',
        ]);

        $response->assertRedirect(route('pasien.dashboard'));
        $this->assertDatabaseHas('daftar_poli', [
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => today()->toDateString(),
            'no_antrian' => 1,
        ]);
    }

    public function test_patient_cannot_register_for_a_date_that_does_not_match_the_schedule_day(): void
    {
        [$pasien, $jadwal] = $this->makePatientAndSchedule(today()->dayOfWeek);
        $wrongDate = today()->addDay();

        $response = $this->actingAs($pasien)->post(route('pasien.pendaftaran.store'), [
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => $wrongDate->toDateString(),
            'keluhan' => 'Pemeriksaan',
        ]);

        $response->assertSessionHasErrors('tanggal_janji');
        $this->assertDatabaseCount('daftar_poli', 0);
    }

    public function test_patient_cannot_have_a_second_active_registration_until_exam_is_recorded(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(8, 0));
        [$pasien, $jadwal] = $this->makePatientAndSchedule(today()->dayOfWeek);

        $payload = [
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => today()->toDateString(),
            'keluhan' => 'Pemeriksaan',
        ];

        $this->actingAs($pasien)->post(route('pasien.pendaftaran.store'), $payload)
            ->assertRedirect(route('pasien.dashboard'));

        $this->actingAs($pasien)->post(route('pasien.pendaftaran.store'), $payload)
            ->assertSessionHasErrors('id_jadwal');

        $this->assertDatabaseCount('daftar_poli', 1);

        $pendaftaran = DaftarPoli::firstOrFail();
        Periksa::create([
            'id_daftar_poli' => $pendaftaran->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
        ]);

        $this->actingAs($pasien)->post(route('pasien.pendaftaran.store'), $payload)
            ->assertRedirect(route('pasien.dashboard'));

        $this->assertDatabaseCount('daftar_poli', 2);
    }

    public function test_doctor_can_call_next_number_and_broadcast_only_queue_data(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(8, 0));
        [$pasien, $jadwal, $dokter] = $this->makePatientAndSchedule(today()->dayOfWeek);
        DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => today(),
            'keluhan' => 'Pemeriksaan',
            'no_antrian' => 1,
        ]);
        Event::fake([AntrianDiperbarui::class]);

        $response = $this->actingAs($dokter)->post(route('dokter.antrian.panggil-berikutnya', $jadwal));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('antrian_harian', [
            'id_jadwal' => $jadwal->id,
            'tanggal' => today()->toDateString(),
            'no_antrian_dipanggil' => 1,
        ]);
        Event::assertDispatched(
            AntrianDiperbarui::class,
            fn (AntrianDiperbarui $event): bool => $event->idJadwal === $jadwal->id
                && $event->tanggal === today()->toDateString()
                && $event->nomorDipanggil === 1
                && $event->broadcastWith() === [
                    'id_jadwal' => $jadwal->id,
                    'tanggal' => today()->toDateString(),
                    'nomor_dipanggil' => 1,
                    'nomor_diperiksa' => null,
                    'jumlah_belum_diperiksa' => 1,
                ]
                && $event->broadcastOn()[0]->name === "antrian.{$jadwal->id}.".today()->toDateString(),
        );
    }

    public function test_doctor_must_finish_current_patient_before_calling_another_number(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(8, 0));
        [$pasien, $jadwal, $dokter] = $this->makePatientAndSchedule(today()->dayOfWeek);
        $this->makeRegistration($pasien, $jadwal, 1);
        $this->makeRegistration($pasien, $jadwal, 2);

        $this->actingAs($dokter)->post(route('dokter.antrian.panggil-berikutnya', $jadwal))
            ->assertSessionHas('success');

        $this->actingAs($dokter)->post(route('dokter.antrian.panggil-berikutnya', $jadwal))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('antrian_harian', [
            'id_jadwal' => $jadwal->id,
            'no_antrian_dipanggil' => 1,
        ]);
    }

    public function test_doctor_can_advance_to_the_next_patient_after_examination_is_saved(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(8, 0));
        [$pasien, $jadwal, $dokter] = $this->makePatientAndSchedule(today()->dayOfWeek);
        $pertama = $this->makeRegistration($pasien, $jadwal, 1);
        $this->makeRegistration($pasien, $jadwal, 2);

        $this->actingAs($dokter)->post(route('dokter.antrian.panggil-berikutnya', $jadwal));
        Periksa::create([
            'id_daftar_poli' => $pertama->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
        ]);

        $this->actingAs($dokter)->post(route('dokter.antrian.panggil-berikutnya', $jadwal))
            ->assertSessionHas('success', 'Nomor antrian 2 berhasil dipanggil.');

        $this->assertDatabaseHas('antrian_harian', [
            'id_jadwal' => $jadwal->id,
            'no_antrian_dipanggil' => 2,
        ]);
    }

    public function test_patient_dashboard_shows_active_queue_and_schedule(): void
    {
        [$pasien, $jadwal] = $this->makePatientAndSchedule(today()->dayOfWeek);
        $this->makeRegistration($pasien, $jadwal, 1);

        $this->actingAs($pasien)
            ->get(route('pasien.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Pasien')
            ->assertSee('Antrian aktif Anda')
            ->assertSee('Umum')
            ->assertSee('Dokter Uji')
            ->assertSee('Nomor antrian Anda');
    }

    public function test_patient_cannot_call_a_queue_or_access_other_role_dashboards(): void
    {
        [$pasien, $jadwal] = $this->makePatientAndSchedule(today()->dayOfWeek);

        $this->actingAs($pasien)
            ->post(route('dokter.antrian.panggil-berikutnya', $jadwal))
            ->assertForbidden();

        $this->actingAs($pasien)
            ->get(route('dokter.dashboard'))
            ->assertForbidden();
    }

    private function makePatientAndSchedule(int $weekday): array
    {
        $poli = Poli::create(['nama_poli' => 'Umum']);
        $dokter = User::create([
            'nama' => 'Dokter Uji',
            'role' => 'dokter',
            'id_poli' => $poli->id,
            'email' => 'dokter-'.uniqid().'@example.test',
            'password' => Hash::make('password'),
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
            ][$weekday],
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);
        $pasien = User::create([
            'nama' => 'Pasien Uji',
            'role' => 'pasien',
            'email' => 'pasien-'.uniqid().'@example.test',
            'password' => Hash::make('password'),
        ]);

        return [$pasien, $jadwal, $dokter];
    }

    private function makeRegistration(User $pasien, JadwalPeriksa $jadwal, int $number): DaftarPoli
    {
        return DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'tanggal_janji' => Carbon::today(),
            'keluhan' => 'Pemeriksaan',
            'no_antrian' => $number,
        ]);
    }
}
