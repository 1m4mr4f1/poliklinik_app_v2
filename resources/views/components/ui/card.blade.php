@props([
    'href' => null,
])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['ui-card block transition duration-150 hover:border-clinic-primary hover:shadow-sm cursor-pointer']) }}>
        {{ $slot }}
    </a>
@else
    <section {{ $attributes->class(['ui-card']) }}>
        {{ $slot }}
    </section>
@endif
