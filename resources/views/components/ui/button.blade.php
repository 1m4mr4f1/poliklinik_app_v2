@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'ui-button-primary',
        'secondary' => 'ui-button-secondary',
        'danger' => 'ui-button-danger',
    ];
    $variantClass = $variants[$variant] ?? $variants['primary'];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['ui-button', $variantClass]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['ui-button', $variantClass]) }}>
        {{ $slot }}
    </button>
@endif
