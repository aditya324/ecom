import lottie from 'lottie-web';
import searchAnimation from '../lottie/search.json';

document.querySelectorAll('[data-lottie="search"]').forEach((element) => {
    const animation = lottie.loadAnimation({
        container: element,
        renderer: 'svg',
        loop: false,
        autoplay: true,
        animationData: searchAnimation,
    });

    element.closest('button')?.addEventListener('mouseenter', () => {
        animation.goToAndPlay(0, true);
    });
});

document.querySelectorAll('[data-header-search]').forEach((form) => {
    const input = form.querySelector('[data-search-input]');
    const panel = form.querySelector('[data-search-panel]');
    let timer = 0;

    const close = () => {
        panel.hidden = true;
        panel.replaceChildren();
    };

    const show = (services) => {
        panel.replaceChildren();

        if (services.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'px-4 py-3 text-[#6f6a64]';
            empty.textContent = 'No matching services.';
            panel.append(empty);
            panel.hidden = false;

            return;
        }

        services.forEach((service) => {
            const link = document.createElement('a');
            link.href = service.url;
            link.className = 'block px-4 py-3 hover:bg-[#f7f4ef]';

            const name = document.createElement('span');
            name.className = 'block font-medium';
            name.textContent = service.name;

            const category = document.createElement('span');
            category.className = 'block text-xs text-[#8a8680]';
            category.textContent = service.category ?? '';

            link.append(name, category);
            panel.append(link);
        });

        panel.hidden = false;
    };

    input.addEventListener('input', () => {
        window.clearTimeout(timer);

        const term = input.value.trim();

        if (term.length < 2) {
            close();

            return;
        }

        timer = window.setTimeout(async () => {
            const response = await fetch(`${form.action}?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok || input.value.trim() !== term) {
                return;
            }

            const data = await response.json();
            show(data.services ?? []);
        }, 200);
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });

    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) {
            close();
        }
    });
});

document.querySelectorAll('[data-menu]').forEach((menu) => {
    document.addEventListener('click', (event) => {
        if (!menu.open || menu.contains(event.target)) {
            return;
        }

        menu.open = false;
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            menu.open = false;
        }
    });
});

document.querySelectorAll('[data-banner]').forEach((banner) => {
    const track = banner.querySelector('[data-banner-track]');
    const dots = [...banner.querySelectorAll('[data-banner-dot]')];

    const setActive = () => {
        const index = Math.round(track.scrollLeft / track.clientWidth);

        dots.forEach((dot, dotIndex) => {
            dot.toggleAttribute('data-active', dotIndex === index);
        });
    };

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            track.scrollTo({ left: track.clientWidth * index, behavior: 'smooth' });
        });
    });

    track.addEventListener('scroll', setActive, { passive: true });

    let dragStartX = 0;
    let dragStartScroll = 0;
    let dragging = false;

    track.addEventListener('pointerdown', (event) => {
        if (event.target.closest('a, button')) {
            return;
        }

        dragging = true;
        dragStartX = event.clientX;
        dragStartScroll = track.scrollLeft;
        track.setPointerCapture(event.pointerId);
    });

    track.addEventListener('pointermove', (event) => {
        if (!dragging) {
            return;
        }

        track.scrollLeft = dragStartScroll - (event.clientX - dragStartX);
    });

    const endDrag = () => {
        dragging = false;
    };

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let timer = null;
    let paused = false;

    const advance = () => {
        const width = track.clientWidth;

        if (!width || dots.length < 2) {
            return;
        }

        const index = Math.round(track.scrollLeft / width);
        const nextIndex = (index + 1) % dots.length;

        track.scrollTo({ left: width * nextIndex, behavior: 'smooth' });
    };

    const stop = () => {
        window.clearInterval(timer);
        timer = null;
    };

    const start = () => {
        stop();

        if (paused || reduceMotion) {
            return;
        }

        timer = window.setInterval(advance, 5000);
    };

    const pause = () => {
        paused = true;
        stop();
    };

    const resume = () => {
        paused = false;
        start();
    };

    banner.addEventListener('mouseenter', pause);
    banner.addEventListener('mouseleave', resume);
    banner.addEventListener('focusin', pause);
    banner.addEventListener('focusout', (event) => {
        if (!banner.contains(event.relatedTarget)) {
            resume();
        }
    });
    track.addEventListener('pointerdown', pause);
    track.addEventListener('pointerup', (event) => {
        if (event.pointerType === 'mouse' && banner.matches(':hover')) {
            return;
        }

        resume();
    });
    track.addEventListener('pointercancel', resume);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stop();
            return;
        }

        if (!paused) {
            start();
        }
    });

    start();
});

document.querySelectorAll('[data-sellers]').forEach((section) => {
    const track = section.querySelector('[data-sellers-track]');
    const previous = section.querySelector('[data-sellers-prev]');
    const next = section.querySelector('[data-sellers-next]');

    const step = () => {
        const card = track.querySelector('article');

        if (!card) {
            return track.clientWidth;
        }

        const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;

        return card.getBoundingClientRect().width + gap;
    };

    const update = () => {
        const maxScroll = track.scrollWidth - track.clientWidth;

        previous.disabled = track.scrollLeft <= 2;
        next.disabled = track.scrollLeft >= maxScroll - 2;
    };

    previous.addEventListener('click', () => {
        track.scrollBy({ left: -step(), behavior: 'smooth' });
    });

    next.addEventListener('click', () => {
        track.scrollBy({ left: step(), behavior: 'smooth' });
    });

    track.addEventListener('scroll', update, { passive: true });
    update();
});

document.querySelectorAll('[data-pricing]').forEach((section) => {
    const toggles = [...section.querySelectorAll('[data-plan-toggle]')];
    const prices = [...section.querySelectorAll('[data-plan-price]')];
    const format = new Intl.NumberFormat('en-IN');

    const render = (yearly) => {
        toggles.forEach((toggle) => {
            toggle.checked = yearly;
        });

        prices.forEach((price) => {
            const amount = Number(yearly ? price.dataset.yearly : price.dataset.monthly);

            price.textContent = `₹${format.format(amount)}`;
        });

        section.querySelectorAll('[data-plan-interval]').forEach((input) => {
            input.value = yearly ? 'yearly' : 'monthly';
        });
    };

    toggles.forEach((toggle) => {
        toggle.addEventListener('change', () => render(toggle.checked));
    });
});

document.querySelectorAll('[data-catalog-filters]').forEach((form) => {
    const minNumber = form.querySelector('[data-price-min]');
    const maxNumber = form.querySelector('[data-price-max]');
    const minRange = form.querySelector('[data-range-min]');
    const maxRange = form.querySelector('[data-range-max]');
    const fill = form.querySelector('[data-range-fill]');

    const paint = () => {
        if (!minRange || !maxRange || !fill) {
            return;
        }

        const limit = Number(maxRange.max);
        let min = Number(minRange.value);
        let max = Number(maxRange.value);

        if (min > max) {
            [min, max] = [max, min];
        }

        fill.style.left = `${(min / limit) * 100}%`;
        fill.style.width = `${((max - min) / limit) * 100}%`;
    };

    const commitRange = () => {
        if (!minRange || !maxRange) {
            return;
        }

        let min = Number(minRange.value);
        let max = Number(maxRange.value);

        if (min > max) {
            [min, max] = [max, min];
            minRange.value = String(min);
            maxRange.value = String(max);
        }

        if (minNumber) {
            minNumber.value = min === 0 ? '' : String(min);
        }

        if (maxNumber) {
            maxNumber.value = max === Number(maxRange.max) ? '' : String(max);
        }

        form.querySelectorAll('[data-price-min], [data-price-max]').forEach((input) => {
            input.disabled = input.value === '';
        });

        const sort = form.querySelector('[name="sort"]');

        if (sort instanceof HTMLSelectElement && sort.value === 'recommended') {
            sort.disabled = true;
        }

        paint();
        form.requestSubmit();
    };

    minRange?.addEventListener('input', paint);
    maxRange?.addEventListener('input', paint);
    minRange?.addEventListener('change', commitRange);
    maxRange?.addEventListener('change', commitRange);
    paint();
});

document.querySelectorAll('[data-point-list]').forEach((list) => {
    const rows = list.querySelector('[data-point-rows]');
    const add = list.querySelector('[data-point-add]');

    const reindex = () => {
        rows.querySelectorAll('[data-point-row]').forEach((row, index) => {
            const title = row.querySelector('[data-point-title]');
            const body = row.querySelector('[data-point-body]');

            if (title) {
                title.name = `points[${index}][title]`;
            }

            if (body) {
                body.name = `points[${index}][body]`;
            }
        });
    };

    const bind = (row) => {
        row.querySelector('[data-point-remove]')?.addEventListener('click', () => {
            const all = rows.querySelectorAll('[data-point-row]');

            if (all.length === 1) {
                row.querySelectorAll('input, textarea').forEach((field) => {
                    field.value = '';
                });
                row.querySelector('[data-point-title]')?.focus();

                return;
            }

            row.remove();
            reindex();
        });
    };

    rows.querySelectorAll('[data-point-row]').forEach(bind);

    add?.addEventListener('click', () => {
        const row = rows.querySelector('[data-point-row]')?.cloneNode(true);

        if (!(row instanceof HTMLElement)) {
            return;
        }

        row.querySelectorAll('input, textarea').forEach((field) => {
            field.value = '';
        });

        rows.append(row);
        bind(row);
        reindex();
        row.querySelector('[data-point-title]')?.focus();
    });
});

document.querySelectorAll('[data-package-services]').forEach((root) => {
    const rows = root.querySelector('[data-package-rows]');
    const picker = root.querySelector('[data-package-picker]');
    const empty = root.querySelector('[data-package-empty]');

    const paint = () => {
        empty?.classList.toggle('hidden', rows.querySelector('[data-package-row]') !== null);
    };

    const bind = (row) => {
        row.querySelector('[data-package-remove]')?.addEventListener('click', () => {
            const input = row.querySelector('input');
            const option = document.createElement('option');
            option.value = input.value;
            option.textContent = row.dataset.serviceName || 'Service';
            picker.append(option);
            row.remove();
            paint();
        });
    };

    rows.querySelectorAll('[data-package-row]').forEach(bind);

    root.querySelector('[data-package-add]')?.addEventListener('click', () => {
        const option = picker.selectedOptions[0];

        if (!option || option.value === '') {
            return;
        }

        const row = document.createElement('div');
        row.dataset.packageRow = '';
        row.dataset.serviceName = option.textContent;
        row.className = 'flex items-center justify-between gap-3 rounded-xl border border-[#e4e0da] px-3 py-2';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'services[]';
        input.value = option.value;

        const label = document.createElement('span');
        label.className = 'block text-sm text-[#1a1a1a]';
        label.textContent = option.textContent;

        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.packageRemove = '';
        button.className = 'inline-flex h-9 shrink-0 items-center rounded-full px-3 text-sm font-medium text-[#9b2c2c]';
        button.textContent = 'Delete';

        row.append(input, label, button);
        rows.append(row);
        bind(row);
        option.remove();
        picker.value = '';
        paint();
    });
});

document.querySelectorAll('[data-image-input]').forEach((input) => {
    const preview = input.parentElement?.querySelector('[data-image-preview]');

    if (!(input instanceof HTMLInputElement) || !(preview instanceof HTMLImageElement)) {
        return;
    }

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    });
});

const categoryName = document.querySelector('#name');
const categorySlug = document.querySelector('[data-slug-from="name"]');

if (categoryName instanceof HTMLInputElement && categorySlug instanceof HTMLInputElement) {
    const toSlug = (value) => value
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    let followName = categorySlug.value === '' || categorySlug.value === toSlug(categoryName.value);

    const fillSlug = () => {
        if (!followName) {
            return;
        }

        categorySlug.value = toSlug(categoryName.value);
    };

    categoryName.addEventListener('input', fillSlug);
    fillSlug();
}

const adminConfirm = document.querySelector('[data-admin-confirm]');

if (adminConfirm) {
    const title = adminConfirm.querySelector('[data-confirm-title]');
    const body = adminConfirm.querySelector('[data-confirm-body]');
    const accept = adminConfirm.querySelector('[data-confirm-accept]');
    let pendingForm = null;

    const closeConfirm = () => {
        adminConfirm.classList.add('hidden');
        adminConfirm.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        pendingForm = null;
    };

    const openConfirm = (form) => {
        pendingForm = form;
        title.textContent = form.dataset.confirmTitle || 'Are you sure?';
        body.textContent = form.dataset.confirmBody || '';
        accept.textContent = form.dataset.confirmAccept || 'Confirm';
        accept.className = form.dataset.confirmTone === 'danger'
            ? 'inline-flex h-11 items-center rounded-full bg-[#9b2c2c] px-5 text-sm font-semibold text-white'
            : 'inline-flex h-11 items-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]';
        adminConfirm.classList.remove('hidden');
        adminConfirm.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        accept.focus();
    };

    document.querySelectorAll('form[data-confirm-title]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();
            openConfirm(form);
        });
    });

    accept?.addEventListener('click', () => {
        if (!pendingForm) {
            return;
        }

        const form = pendingForm;
        form.dataset.confirmed = '1';
        closeConfirm();
        form.requestSubmit();
    });

    adminConfirm.querySelectorAll('[data-confirm-dismiss]').forEach((button) => {
        button.addEventListener('click', closeConfirm);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !adminConfirm.classList.contains('hidden')) {
            closeConfirm();
        }
    });
}

document.querySelectorAll('[data-service-options]').forEach((root) => {
    const summary = root.querySelector('[data-duration-summary]');
    const recommended = root.querySelector('[data-duration-recommended]');

    const select = (button, group) => {
        root.querySelectorAll(`[${group}]`).forEach((item) => {
            const pressed = item === button;
            item.setAttribute('aria-pressed', pressed ? 'true' : 'false');
            item.classList.toggle('border-[#e07a2f]', pressed);
            item.classList.toggle('text-[#1a1a1a]', pressed);
            item.classList.toggle('border-[#ece7e0]', !pressed);
            item.classList.toggle('text-[#6f6a64]', !pressed);
        });
    };

    const formatMoney = (amount) => `₹${Math.round(Number(amount)).toLocaleString('en-US')}`;

    const applyDuration = (button) => {
        const months = Number(button.dataset.months || 1);
        const total = Number(button.dataset.price);
        const compare = Number(button.dataset.compare || 0);

        document.querySelectorAll('[data-service-price]').forEach((price) => {
            price.textContent = formatMoney(total);
        });

        document.querySelectorAll('[data-service-compare-row]').forEach((row) => {
            row.classList.toggle('hidden', !(compare > 0));
        });

        if (compare > 0) {
            document.querySelectorAll('[data-service-compare]').forEach((price) => {
                price.textContent = formatMoney(compare);
            });
        }

        document.querySelectorAll('[data-billing-suffix]').forEach((suffix) => {
            suffix.classList.toggle('hidden', months !== 1);
        });

        document.querySelectorAll('[data-cart-months]').forEach((input) => {
            input.value = String(months);
        });
    };

    root.querySelectorAll('[data-duration]').forEach((button) => {
        button.addEventListener('click', () => {
            select(button, 'data-duration');
            if (summary) {
                summary.textContent = button.dataset.summary;
            }
            recommended?.classList.toggle('hidden', button.dataset.recommended !== '1');
            applyDuration(button);
        });
    });

    root.querySelectorAll('[data-audience]').forEach((button) => {
        button.addEventListener('click', () => select(button, 'data-audience'));
    });
});

document.querySelectorAll('[data-razorpay-checkout]').forEach((form) => {
    const button = form.querySelector('[data-checkout-button]');
    const error = form.querySelector('[data-checkout-error]');
    const token = form.querySelector('[name="_token"]')?.value ?? '';

    const fail = (message) => {
        if (error) {
            error.textContent = message;
            error.hidden = false;
        }

        if (button) {
            button.disabled = false;
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (button) {
            button.disabled = true;
        }

        if (error) {
            error.hidden = true;
        }

        let payload = {};
        let response;

        try {
            response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: new FormData(form),
            });
            payload = await response.json();
        } catch {
            fail('Payment could not be started. Please try again.');

            return;
        }

        if (!response.ok) {
            const errors = payload.errors ? Object.values(payload.errors).flat() : [];
            fail(errors[0] || payload.message || 'This order was not placed.');

            return;
        }

        if (typeof window.Razorpay !== 'function') {
            fail('Payment could not be opened.');

            return;
        }

        const steps = Array.isArray(payload.steps) ? payload.steps : [];

        const fieldsFor = (step, payment) => (step.type === 'subscription'
            ? {
                razorpay_subscription_id: step.subscription_id,
                razorpay_payment_id: payment.razorpay_payment_id,
                razorpay_signature: payment.razorpay_signature,
            }
            : {
                razorpay_order_id: step.order_id,
                razorpay_payment_id: payment.razorpay_payment_id,
                razorpay_signature: payment.razorpay_signature,
            });

        const submit = (step, payment) => {
            const confirm = document.createElement('form');
            confirm.method = 'POST';
            confirm.action = form.dataset.confirm;

            Object.entries({
                _token: token,
                ...fieldsFor(step, payment),
            }).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                confirm.append(input);
            });

            document.body.append(confirm);
            confirm.submit();
        };

        const openAt = (index) => {
            const step = steps[index];

            if (!step) {
                fail('Payment could not be opened.');

                return;
            }

            const options = {
                key: payload.key,
                name: payload.name,
                description: step.description,
                prefill: payload.prefill,
                theme: { color: '#f5b400' },
                modal: {
                    ondismiss() {
                        if (index > 0 && form.dataset.cart) {
                            window.location = form.dataset.cart;

                            return;
                        }

                        if (button) {
                            button.disabled = false;
                        }
                    },
                },
                handler(payment) {
                    if (index < steps.length - 1) {
                        const body = new FormData();
                        body.append('_token', token);
                        Object.entries(fieldsFor(step, payment)).forEach(([name, value]) => body.append(name, value));

                        fetch(form.dataset.confirm, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body,
                        }).then((confirmed) => {
                            if (!confirmed.ok) {
                                fail('Payment could not be confirmed.');

                                return;
                            }

                            openAt(index + 1);
                        }).catch(() => fail('Payment could not be confirmed.'));

                        return;
                    }

                    submit(step, payment);
                },
            };

            if (step.type === 'subscription') {
                options.subscription_id = step.subscription_id;
            } else {
                options.order_id = step.order_id;
                options.amount = step.amount;
                options.currency = step.currency;
            }

            const checkout = new window.Razorpay(options);

            checkout.on('payment.failed', () => {
                fail('Payment was not completed.');
            });

            checkout.open();
        };

        openAt(0);
    });
});

document.querySelectorAll('[data-rating-picker]').forEach((picker) => {
    const label = picker.querySelector('[data-rating-label]');

    const paint = (value) => {
        picker.querySelectorAll('[data-star]').forEach((star) => {
            const on = Number(star.dataset.star) <= value;
            star.classList.toggle('text-[#f5b400]', on);
            star.classList.toggle('text-[#e4ddd4]', ! on);
        });

        if (label && value > 0) {
            label.textContent = `${value} of 5`;
        }
    };

    const selected = () => Number(picker.querySelector('input:checked')?.value || 0);

    paint(selected());
    picker.addEventListener('change', () => paint(selected()));

    picker.querySelectorAll('[data-star]').forEach((star) => {
        star.addEventListener('mouseenter', () => paint(Number(star.dataset.star)));
    });

    picker.addEventListener('mouseleave', () => paint(selected()));
});

document.querySelectorAll('[data-share]').forEach((button) => {
    button.addEventListener('click', async () => {
        const url = button.dataset.shareUrl;

        if (navigator.share) {
            try {
                await navigator.share({ title: document.title, url });
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    window.prompt('Copy this link', url);
                }
            }

            return;
        }

        window.prompt('Copy this link', url);
    });
});

document.querySelectorAll('[data-deal-ends]').forEach((node) => {
    const ends = Date.parse(node.dataset.dealEnds);

    const label = () => {
        const seconds = Math.floor((ends - Date.now()) / 1000);

        if (Number.isNaN(ends) || seconds <= 0) {
            node.textContent = 'Deal ended';

            return;
        }

        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remain = seconds % 60;

        if (days > 0) {
            node.textContent = `Ends in ${days}d ${hours}h`;
        } else if (hours > 0) {
            node.textContent = `Ends in ${hours}h ${minutes}m`;
        } else {
            node.textContent = `Ends in ${minutes}m ${remain}s`;
        }
    };

    label();
    window.setInterval(label, 1000);
});
