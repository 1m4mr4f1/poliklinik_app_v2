@props([
    'label',
    'id' => null,
    'error' => null,
])

@php($fieldId = $id ?? $attributes->get('name', 'ui-input'))

<div class="space-y-1">
    <label for="{{ $fieldId }}" class="block text-xs font-medium text-clinic-secondary">
        {{ $label }}
    </label>
    <input id="{{ $fieldId }}"
        {{ $attributes->class(['ui-input'])->merge(['aria-invalid' => $error ? 'true' : 'false']) }}>
    @if($error)
        <p class="text-xs text-clinic-danger" role="alert">{{ $error }}</p>
    @endif
</div>
