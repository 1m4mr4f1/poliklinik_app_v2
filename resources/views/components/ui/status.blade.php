@props(['status'])

@php
    $statusClasses = [
        'menunggu' => 'ui-status-waiting',
        'menunggu pemeriksaan' => 'ui-status-waiting',
        'dipanggil' => 'ui-status-called',
        'selesai' => 'ui-status-done',
        'sudah diperiksa' => 'ui-status-done',
        'batal' => 'ui-status-danger',
        'belum_bayar' => 'ui-status-waiting',
        'belum_dibayar' => 'ui-status-waiting',
        'menunggu_verifikasi' => 'ui-status-waiting',
        'lunas' => 'ui-status-done',
    ];
    $statusLabels = [
        'menunggu_verifikasi' => 'Menunggu verifikasi',
        'belum_bayar' => 'Belum bayar',
        'belum_dibayar' => 'Belum bayar',
        'batal' => 'Batal',
        'dipanggil' => 'Sedang dipanggil',
        'selesai' => 'Selesai',
        'sudah diperiksa' => 'Sudah diperiksa',
        'menunggu pemeriksaan' => 'Menunggu pemeriksaan',
        'menunggu' => 'Menunggu',
        'lunas' => 'Lunas',
    ];
    $normalizedStatus = strtolower(str_replace('-', ' ', (string) $status));
    $statusClass = $statusClasses[$normalizedStatus] ?? 'ui-status-called';
    $statusLabel = $statusLabels[$normalizedStatus] ?? ucfirst($normalizedStatus);
@endphp

<span {{ $attributes->class(['ui-status', $statusClass]) }}>
    {{ $slot->isEmpty() ? $statusLabel : $slot }}
</span>
