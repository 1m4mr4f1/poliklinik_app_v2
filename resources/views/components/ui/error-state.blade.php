@props([
    'message' => 'Data gagal dimuat.',
    'retryUrl' => null,
])

<div {{ $attributes->class(['ui-error']) }} role="alert">
    <p class="text-sm font-medium">{{ $message }}</p>
    @if($retryUrl)
        <a href="{{ $retryUrl }}" class="ui-button ui-button-secondary mt-4">
            Coba lagi
        </a>
    @elseif(isset($action))
        <div class="mt-4">{{ $action }}</div>
    @endif
</div>
