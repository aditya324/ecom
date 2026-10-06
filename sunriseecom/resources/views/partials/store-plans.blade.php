<section id="packages" class="bg-[#f7f4ef]" aria-label="Packages" data-pricing>
    <div class="mx-auto flex w-full max-w-[90.5vw] flex-col items-center gap-6 px-6 py-16 sm:px-8 lg:flex-row lg:items-end lg:justify-center lg:gap-8">
        @foreach ($plans as $plan)
            @php
                $yearlyMonthly = (int) round(((float) $plan->yearly_price) / 12);
                $monthly = (int) round((float) $plan->monthly_price);
            @endphp
            <article id="plan-{{ $plan->slug }}" class="flex w-full max-w-[300px] flex-col rounded-2xl bg-white px-6 pt-6 pb-5 shadow-[0_10px_40px_rgba(28,28,28,0.06)]">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-lg text-[#1a1a1a]"><a href="{{ route('packages.show', $plan) }}" class="hover:text-[#c4a035]">{{ $plan->name }}</a></h2>
                    <label class="inline-flex cursor-pointer items-center gap-2">
                        <input type="checkbox" data-plan-toggle class="peer sr-only" checked>
                        <span class="relative h-5 w-9 rounded-full bg-[#e4e0db] transition peer-checked:bg-[#f5b400] after:absolute after:top-0.5 after:left-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:shadow-sm after:transition after:content-[''] peer-checked:after:translate-x-4"></span>
                        <span class="text-[11px] font-medium tracking-[0.12em] text-[#8a8680] uppercase">Yearly</span>
                        <span class="sr-only">billing for {{ $plan->name }}</span>
                    </label>
                </div>

                <p class="mt-5 flex items-end gap-3">
                    <span
                        data-plan-price
                        data-monthly="{{ $monthly }}"
                        data-yearly="{{ $yearlyMonthly }}"
                        class="text-[2rem] leading-none font-medium tracking-tight text-[#1a1a1a]"
                    >₹{{ number_format($yearlyMonthly) }}</span>
                    <span class="pb-1 text-[11px] tracking-[0.14em] text-[#8a8680] uppercase">Per month</span>
                </p>

                <ul class="mt-6 flex flex-col gap-3">
                    @foreach ($plan->items as $item)
                        <li class="flex items-start gap-2 text-[13px] tracking-wide text-[#2a2a2a] uppercase">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            {{ $item->service->name }}
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('plans.subscribe', $plan) }}" class="mt-8">
                    @csrf
                    <input type="hidden" name="interval" value="yearly" data-plan-interval>
                    <button type="submit" class="w-full rounded-lg bg-[#f5b400] py-3 text-sm font-medium text-[#1a1a1a]">
                        get a plan
                    </button>
                </form>
            </article>
        @endforeach
    </div>
</section>
