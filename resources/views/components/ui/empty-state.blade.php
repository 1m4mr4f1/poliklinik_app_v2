@props([
    'message' => 'Belum ada data.',
])

<div {{ $attributes->class(['ui-empty']) }}>
    <p class="text-sm text-clinic-secondary">{{ $message }}</p>
    @if(isset($action))
        <div class="mt-4">{{ $action }}</div>
    @endif
</div>
