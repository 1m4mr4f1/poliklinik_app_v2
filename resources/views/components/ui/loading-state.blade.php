@props(['rows' => 3])

<div {{ $attributes->class(['space-y-3']) }} role="status" aria-label="Memuat data">
    @for($row = 0; $row < $rows; $row++)
        <div class="ui-skeleton w-full" style="height: 42px"></div>
    @endfor
    <span class="sr-only">Memuat data...</span>
</div>
