@php
    $defaults = $defaults ?? [];
    $control = 'h-11 rounded-full border bg-white px-4 text-sm text-[#1a1a1a] outline-none focus:border-[#f5b400]';
@endphp

<form method="GET" action="{{ $action }}" class="mt-6 flex flex-wrap items-center gap-2">
    @foreach ($fields as $field)
        @php
            $value = $values[$field['name']] ?? '';
            $active = $value !== null && $value !== '' && ($defaults[$field['name']] ?? null) !== $value;
        @endphp
        @if ($field['type'] === 'search')
            <label class="flex h-11 min-w-56 flex-1 items-center gap-2 rounded-full border border-[#e4e0da] bg-white px-4 focus-within:border-[#f5b400]">
                <svg class="h-4 w-4 shrink-0 text-[#8a8680]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-3.5-3.5"/>
                </svg>
                <span class="sr-only">{{ $field['label'] }}</span>
                <input name="{{ $field['name'] }}" type="search" value="{{ $value }}" placeholder="{{ $field['placeholder'] }}" class="h-full w-full bg-transparent text-sm outline-none placeholder:text-[#8a8680]">
            </label>
        @elseif ($field['type'] === 'select')
            <select name="{{ $field['name'] }}" aria-label="{{ $field['label'] }}" @class([$control, 'border-[#f5b400] bg-[#fff8e8]' => $active, 'border-[#e4e0da]' => ! $active])>
                @foreach ($field['options'] as $option => $label)
                    <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $label }}</option>
                @endforeach
            </select>
        @else
            <input name="{{ $field['name'] }}" type="number" min="0" step="1" value="{{ $value }}" placeholder="{{ $field['placeholder'] }}" aria-label="{{ $field['label'] }}" @class([$control, 'w-28', 'border-[#f5b400] bg-[#fff8e8]' => $active, 'border-[#e4e0da]' => ! $active])>
        @endif
    @endforeach

    <button type="submit" class="inline-flex h-11 items-center rounded-full bg-[#111111] px-5 text-sm font-semibold text-white">Apply</button>
    @if ($filtering)
        <a href="{{ $action }}" class="inline-flex h-11 items-center px-2 text-sm font-medium text-[#6f6a64] underline">Clear</a>
    @endif
</form>

<p class="mt-3 text-sm text-[#6f6a64]">Showing {{ $count }} {{ \Illuminate\Support\Str::plural($noun, $count) }}</p>
