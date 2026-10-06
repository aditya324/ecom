@if ($deals->isNotEmpty())
    <section class="bg-[#FFEADD]" aria-labelledby="todays-deals">
        <div class="mx-auto w-full max-w-[90.5vw] px-10 py-14 sm:px-20 sm:py-16 lg:px-28">
            <div class="flex items-center justify-between gap-4">
                <h2 id="todays-deals" class="text-lg font-medium text-[#1a1a1a]">Today's Deals</h2>
                <a href="{{ route('deals') }}" class="shrink-0 text-sm font-medium text-[#c4a035]">See all deals</a>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($deals as $deal)
                    @include('partials.store-deal-card', ['deal' => $deal])
                @endforeach
            </div>
        </div>
    </section>
@endif
