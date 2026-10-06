@if (($count ?? 0) > 0)
    <span @class([
        'inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[11px] font-semibold',
        'ml-auto' => $push ?? false,
        'bg-[#1a1a1a] text-white' => $active,
        'bg-[#f5b400] text-[#1a1a1a]' => ! $active,
    ])>{{ $count }}</span>
@endif
