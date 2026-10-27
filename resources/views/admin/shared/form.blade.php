<x-layouts.app :title="$title">
    <div class="mx-auto max-w-2xl space-y-4">
        <div>
            <h1 class="text-xl font-bold text-clinic-text">{{ $title }}</h1>
            <p class="mt-1 text-sm text-clinic-secondary">Lengkapi informasi di bawah ini dengan benar.</p>
        </div>

        <x-ui.card>
            <form class="space-y-4" method="POST" action="{{ $action }}">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                @foreach ($fields as $field)
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-clinic-secondary" for="{{ $field['name'] }}">
                            {{ $field['label'] }}
                            @if($field['required'] ?? false)
                                <span class="text-clinic-danger">*</span>
                            @endif
                        </label>

                        @if (($field['type'] ?? 'text') === 'textarea')
                            <textarea class="ui-input @error($field['name']) border-clinic-danger @enderror"
                                      id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="4"
                                      @required($field['required'] ?? false)>{{ old($field['name'], $field['value'] ?? '') }}</textarea>
                        @elseif (($field['type'] ?? 'text') === 'select')
                            <select class="ui-input @error($field['name']) border-clinic-danger @enderror"
                                    id="{{ $field['name'] }}" name="{{ $field['name'] }}"
                                    @required($field['required'] ?? false)>
                                <option value="">Pilih {{ strtolower($field['label']) }}</option>
                                @foreach ($field['options'] as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old($field['name'], $field['value'] ?? '') === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <input class="ui-input @error($field['name']) border-clinic-danger @enderror"
                                   id="{{ $field['name'] }}" name="{{ $field['name'] }}"
                                   type="{{ $field['type'] ?? 'text' }}"
                                   value="{{ ($field['type'] ?? '') === 'password' ? '' : old($field['name'], $field['value'] ?? '') }}"
                                   @if (isset($field['max'])) maxlength="{{ $field['max'] }}" @endif
                                   @if (isset($field['min'])) min="{{ $field['min'] }}" @endif
                                   @required($field['required'] ?? false)
                                   autocomplete="{{ ($field['type'] ?? '') === 'password' ? 'new-password' : 'off' }}">
                        @endif

                        @if (! empty($field['help']))
                            <p class="text-xs text-clinic-muted">{{ $field['help'] }}</p>
                        @endif
                        @error($field['name'])
                            <p class="text-xs text-clinic-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <div class="flex flex-wrap items-center gap-3 border-t border-clinic-border pt-4">
                    <x-ui.button type="submit" variant="primary">
                        <i class="fas fa-floppy-disk mr-1"></i> Simpan
                    </x-ui.button>
                    <x-ui.button href="{{ $backUrl }}" variant="secondary">
                        Batal
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
