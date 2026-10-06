<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'name',
    'slug',
    'short_description',
    'group_name',
    'description',
    'price',
    'price_suffix',
    'compare_price',
    'billing_type',
    'delivery_label',
    'rating',
    'review_count',
    'is_deal',
    'is_best_seller',
    'is_listed',
    'is_active',
    'badge',
    'discount_label',
    'image',
    'video_url',
    'sort_order',
    'deal_ends_at',
    'details',
])]
class Service extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_deal' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_listed' => 'boolean',
            'is_active' => 'boolean',
            'deal_ends_at' => 'datetime',
            'details' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function money(float|string|null $amount): string
    {
        return '₹'.number_format((float) $amount, 0, '.', ',');
    }

    public function billingLabel(): ?string
    {
        return match ($this->billing_type) {
            'monthly' => '/month',
            'one-time' => '/one-time',
            'hourly' => '/hour',
            default => $this->price_suffix,
        };
    }

    public function savingsPercent(): ?int
    {
        if ($this->compare_price === null || (float) $this->compare_price <= (float) $this->price) {
            return null;
        }

        return (int) floor((1 - ((float) $this->price / (float) $this->compare_price)) * 100);
    }

    public function reviewLabel(): string
    {
        if ($this->review_count < 1000) {
            return (string) $this->review_count;
        }

        $scaled = $this->review_count / 1000;
        $formatted = number_format($scaled, $scaled >= 10 ? 0 : 1);
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted.'k';
    }

    public function billingShort(): string
    {
        return match ($this->billing_type) {
            'monthly' => '/mo',
            'hourly' => '/hr',
            default => '',
        };
    }

    public function priceNote(): string
    {
        $note = $this->detail('price_note');

        return is_string($note) && $note !== '' ? $note : 'Inclusive of all taxes.';
    }

    public function stockLabel(): string
    {
        $label = $this->detail('stock');

        return is_string($label) && $label !== '' ? $label : 'In Stock. Ready to deploy.';
    }

    public function coverageLabel(): string
    {
        $label = $this->detail('coverage');

        return is_string($label) && $label !== '' ? $label : 'Delivering virtually globally.';
    }

    /**
     * @return list<array{label: string, summary: string, recommended: bool, months: int, price: float, custom: bool}>
     */
    public function durations(): array
    {
        $items = $this->detailList('durations');

        if ($items !== []) {
            return array_map(fn (array $item) => $this->presentDuration($item), $items);
        }

        if ($this->billing_type !== 'monthly') {
            return [];
        }

        return array_map(fn (array $plan) => $this->presentDuration($plan), $this->standardDurationPlans());
    }

    /**
     * @return array{months: ?int, label: string, price: float}|null
     */
    public function offer(?int $months): ?array
    {
        $durations = $this->durations();

        if ($durations === []) {
            return [
                'months' => null,
                'label' => $this->delivery_label ?: 'One-time',
                'price' => (float) $this->price,
            ];
        }

        foreach ($durations as $duration) {
            if ($months !== null && (int) $duration['months'] === $months) {
                return [
                    'months' => (int) $duration['months'],
                    'label' => $duration['summary'],
                    'price' => (float) $duration['price'],
                ];
            }
        }

        return null;
    }

    public function storedDurationPrice(int $months): mixed
    {
        foreach ($this->detailList('durations') as $item) {
            if ($this->durationMonths($item) !== $months) {
                continue;
            }

            if (! array_key_exists('price', $item) || $item['price'] === null || $item['price'] === '') {
                return null;
            }

            return $item['price'];
        }

        return null;
    }

    /**
     * @return list<array{label: string, summary: string, recommended: bool, months: int}>
     */
    public function standardDurationPlans(): array
    {
        return [
            ['label' => '1 Mo', 'summary' => '1 Month', 'recommended' => false, 'months' => 1],
            ['label' => '3 Mo', 'summary' => '3 Months', 'recommended' => true, 'months' => 3],
            ['label' => '6 Mo', 'summary' => '6 Months', 'recommended' => false, 'months' => 6],
            ['label' => '12 Mo', 'summary' => '12 Months', 'recommended' => false, 'months' => 12],
        ];
    }

    /**
     * The typed plan price is what the customer pays. Compare price stays the crossed-out M.R.P.
     *
     * @param  array<string, mixed>  $item
     * @return array{label: string, summary: string, recommended: bool, months: int, price: float, compare: ?float, custom: bool}
     */
    private function presentDuration(array $item): array
    {
        $months = $this->durationMonths($item);
        $regular = (float) $this->price * $months;
        $custom = array_key_exists('price', $item) && $item['price'] !== null && $item['price'] !== '';
        $selling = $custom ? (float) $item['price'] : $regular;

        return [
            'label' => (string) ($item['label'] ?? ''),
            'summary' => (string) ($item['summary'] ?? $item['label'] ?? ''),
            'recommended' => (bool) ($item['recommended'] ?? false),
            'months' => $months,
            'price' => $selling,
            'compare' => $this->compare_price !== null ? (float) $this->compare_price * $months : null,
            'custom' => $custom,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function durationMonths(array $item): int
    {
        if ((int) ($item['months'] ?? 0) > 0) {
            return (int) $item['months'];
        }

        if (preg_match('/(\d+)/', (string) ($item['label'] ?? ''), $matches) === 1) {
            return max(1, (int) $matches[1]);
        }

        return 1;
    }

    /**
     * @return list<string>
     */
    public function audiences(): array
    {
        $items = $this->detail('audiences');

        if (! is_array($items)) {
            return [];
        }

        $labels = [];

        foreach ($items as $item) {
            if (is_string($item) && $item !== '') {
                $labels[] = $item;
            } elseif (is_array($item) && is_string($item['label'] ?? null) && $item['label'] !== '') {
                $labels[] = $item['label'];
            }
        }

        return $labels;
    }

    /**
     * @return list<string>
     */
    public function deliverables(): array
    {
        $items = $this->detailStrings('deliverables');

        if ($items !== []) {
            return $items;
        }

        return array_values(array_filter([
            $this->short_description,
            $this->delivery_label,
        ]));
    }

    /**
     * @return list<string>
     */
    public function fullDeliverables(): array
    {
        return $this->detailStrings('full_deliverables');
    }

    /**
     * Reasons saved for the admin form. Does not invent a fallback line.
     *
     * @return list<array{title: string, body: string}>
     */
    public function reasonFields(): array
    {
        $items = array_values(array_filter(array_map(fn (array $item) => [
            'title' => trim((string) ($item['title'] ?? '')),
            'body' => trim((string) ($item['body'] ?? '')),
        ], $this->detailList('reasons')), fn (array $item) => $item['title'] !== '' && $item['body'] !== ''));

        if ($items !== []) {
            return $items;
        }

        $body = trim((string) $this->description);

        return $body === '' ? [] : [['title' => 'What is included', 'body' => $body]];
    }

    public static function youtubeIdFrom(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    public function youtubeId(): ?string
    {
        return self::youtubeIdFrom($this->video_url);
    }

    public function reasonsHeading(): string
    {
        $heading = $this->detail('reasons_heading');

        return is_string($heading) && $heading !== ''
            ? $heading
            : 'Here are a few reasons why this service is the right choice for your business:';
    }

    /**
     * @return list<array{title: string, body: string}>
     */
    public function reasons(): array
    {
        $items = array_map(fn (array $item) => [
            'title' => (string) ($item['title'] ?? ''),
            'body' => (string) ($item['body'] ?? ''),
        ], $this->detailList('reasons'));

        $items = array_values(array_filter($items, fn (array $item) => $item['title'] !== '' && $item['body'] !== ''));

        if ($items !== []) {
            return $items;
        }

        return [[
            'title' => 'What is included',
            'body' => $this->description,
        ]];
    }

    public function catalogBadge(): ?string
    {
        if ($this->badge === 'verified') {
            return 'verified';
        }

        if ($this->badge === 'bestseller' || $this->is_best_seller) {
            return 'bestseller';
        }

        return null;
    }

    private function detail(string $key): mixed
    {
        $details = $this->details;

        if (! is_array($details)) {
            return null;
        }

        return $details[$key] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function detailList(string $key): array
    {
        $items = $this->detail($key);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, fn (mixed $item) => is_array($item)));
    }

    /**
     * @return list<string>
     */
    private function detailStrings(string $key): array
    {
        $items = $this->detail($key);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, fn (mixed $item) => is_string($item) && $item !== ''));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<PlanItem, $this>
     */
    public function planItems(): HasMany
    {
        return $this->hasMany(PlanItem::class);
    }

    /**
     * @return HasMany<Quote, $this>
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_hidden', false)->latest();
    }

    public function syncReviewStats(): void
    {
        $count = $this->reviews()->count();

        if ($count === 0) {
            $hadReviews = Review::query()->where('service_id', $this->id)->exists();

            if ($hadReviews && ((int) $this->review_count !== 0 || (float) $this->rating !== 0.0)) {
                $this->update([
                    'rating' => 0,
                    'review_count' => 0,
                ]);
            }

            return;
        }

        $rating = number_format(round((float) $this->reviews()->avg('rating'), 1), 1, '.', '');

        if ((string) $this->rating === $rating && (int) $this->review_count === $count) {
            return;
        }

        $this->update([
            'rating' => $rating,
            'review_count' => $count,
        ]);
    }

    public function dealEndsLabel(): ?string
    {
        if ($this->deal_ends_at === null) {
            return null;
        }

        $seconds = $this->deal_ends_at->getTimestamp() - now()->getTimestamp();

        if ($seconds <= 0) {
            return 'Deal ended';
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remain = $seconds % 60;

        if ($days > 0) {
            return "Ends in {$days}d {$hours}h";
        }

        if ($hours > 0) {
            return "Ends in {$hours}h {$minutes}m";
        }

        return "Ends in {$minutes}m {$remain}s";
    }

    /**
     * @param  Builder<Service>  $query
     * @return Builder<Service>
     */
    #[Scope]
    protected function deals(Builder $query): Builder
    {
        return $query
            ->where('is_deal', true)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * @param  Builder<Service>  $query
     * @return Builder<Service>
     */
    #[Scope]
    protected function bestSellers(Builder $query): Builder
    {
        return $query
            ->where('is_best_seller', true)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
