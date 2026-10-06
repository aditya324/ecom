<footer class="mt-auto border-t-4 border-[#f5b400] bg-[#161616] text-white">
    <div class="mx-auto w-full container px-6 py-12 sm:px-8">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-start justify-center lg:gap-16 xl:gap-24">
            <div class="max-w-xs shrink-0">
                <img src="{{ asset('assets/logo/logo.png') }}" alt="Sunrise Digital" class="h-14 w-auto rounded-md bg-white p-1.5">
                <p class="mt-5 text-sm leading-relaxed text-white/75">
                    Sunrise Digital is a premier agency-grade marketplace providing high-conversion digital services, from branding to industrial-scale development.
                </p>
                <div class="mt-5 flex items-center gap-4 text-white/80">
                    <a href="https://x.com/SdmBangalore" class="hover:text-[#f5b400]" aria-label="X" target="_blank" rel="noopener">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M14.7 10.3 22.4 2h-1.8l-6.7 7.2L8.4 2H2l8.1 10.9L2 22h1.8l7.1-7.6L15.6 22H22l-7.3-11.7Zm-2.5 2.7-.8-1.1L4.4 3.3h2.8l5.3 7 .8 1.1 6.9 9.2h-2.8l-5.2-7.6Z"/>
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/sunrisedsn" class="hover:text-[#f5b400]" aria-label="Facebook" target="_blank" rel="noopener">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M14.5 22v-8.2h2.8l.4-3.2h-3.2V8.6c0-.9.3-1.6 1.6-1.6H18V4.1C17.7 4.1 16.6 4 15.4 4c-2.6 0-4.4 1.6-4.4 4.5v2.1H8.2v3.2h2.8V22h3.5Z"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/sunrisedigitalofficial" class="hover:text-[#f5b400]" aria-label="Instagram" target="_blank" rel="noopener">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="18" height="18" x="3" y="3" rx="4"/>
                            <circle cx="12" cy="12" r="3.5"/>
                            <circle cx="17.5" cy="6.5" r="0.8" fill="currentColor" stroke="none"/>
                        </svg>
                    </a>
                    <a href="https://www.youtube.com/c/SunriseDigitalMedia" class="hover:text-[#f5b400]" aria-label="YouTube" target="_blank" rel="noopener">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M23 12.2s0-3.2-.4-4.6c-.2-.9-.9-1.6-1.8-1.8C19.2 5.4 12 5.4 12 5.4s-7.2 0-8.8.4c-.9.2-1.6.9-1.8 1.8C1 9 1 12.2 1 12.2s0 3.2.4 4.6c.2.9.9 1.6 1.8 1.8 1.6.4 8.8.4 8.8.4s7.2 0 8.8-.4c.9-.2 1.6-.9 1.8-1.8.4-1.4.4-4.6.4-4.6ZM9.8 15.5v-6.6l6.2 3.3-6.2 3.3Z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-x-10 gap-y-8 sm:grid-cols-3 sm:gap-x-16 xl:gap-x-24">
            <div>
                <h2 class="text-sm font-semibold tracking-[0.14em] text-[#f5b400] uppercase">Quick Links</h2>
                <ul class="mt-4 flex flex-col gap-3 text-sm text-white/80">
                    <li><a href="https://sunrisedigital.co.in/about.php" class="hover:text-white" target="_blank" rel="noopener">About Us</a></li>
                    <li><a href="https://sunrisedigital.co.in/career.php" class="hover:text-white" target="_blank" rel="noopener">Careers</a></li>
                    <li><a href="{{ route('categories.index') }}" class="hover:text-white">Services</a></li>
                    <li><a href="https://sunrisedigital.co.in/contact-us.php" class="hover:text-white" target="_blank" rel="noopener">Contact</a></li>
                    <li><a href="{{ route('support') }}" class="hover:text-white">Support</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold tracking-[0.14em] text-[#f5b400] uppercase">Services</h2>
                <ul class="mt-4 flex flex-col gap-3 text-sm text-white/80">
                    <li><a href="{{ route('categories.show', 'development') }}" class="hover:text-white">Development</a></li>
                    <li><a href="{{ route('categories.show', 'branding') }}" class="hover:text-white">UI/UX Design</a></li>
                    <li><a href="{{ route('categories.show', 'seo') }}" class="hover:text-white">SEO & Content</a></li>
                    <li><a href="{{ route('categories.show', 'digital-marketing') }}" class="hover:text-white">Marketing</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-sm font-semibold tracking-[0.14em] text-[#f5b400] uppercase">Legal</h2>
                <ul class="mt-4 flex flex-col gap-3 text-sm text-white/80">
                    <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white">Terms of Service</a></li>
                    <li><a href="{{ route('refund') }}" class="hover:text-white">Refund Policy</a></li>
                </ul>
            </div>
            </div>
        </div>

        <p class="mt-8 border-t border-white/10 pt-5 text-center text-xs text-white/55">
            © 2024 Sunrise Digital Agency. All rights reserved. Precise Engineering for the Digital Era.
        </p>
    </div>
</footer>
