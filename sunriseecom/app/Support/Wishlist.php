<?php

namespace App\Support;

use App\Models\Service;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class Wishlist
{
    public function __construct(private Request $request) {}

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        if (! $this->request->session()->has('wishlist') && ($user = $this->request->user()) instanceof User) {
            $this->request->session()->put('wishlist', $this->storedIds($user));
        }

        return array_values(array_map('intval', $this->request->session()->get('wishlist', [])));
    }

    public function count(): int
    {
        return count($this->services());
    }

    public function has(Service $service): bool
    {
        return in_array($service->id, $this->ids(), true);
    }

    /**
     * @return list<Service>
     */
    public function services(): array
    {
        $ids = $this->ids();

        if ($ids === []) {
            return [];
        }

        $services = Service::query()
            ->with('category')
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $saved = [];

        foreach (array_reverse($ids) as $id) {
            if ($services->has($id)) {
                $saved[] = $services->get($id);
            }
        }

        return $saved;
    }

    public function add(Service $service): void
    {
        $ids = $this->ids();

        if (! in_array($service->id, $ids, true)) {
            $ids[] = $service->id;
        }

        $this->request->session()->put('wishlist', $ids);
        $this->persist();
    }

    public function remove(Service $service): void
    {
        $ids = array_values(array_filter(
            $this->ids(),
            fn (int $id) => $id !== $service->id,
        ));

        $this->request->session()->put('wishlist', $ids);
        $this->persist();
    }

    public function toggle(Service $service): bool
    {
        if ($this->has($service)) {
            $this->remove($service);

            return false;
        }

        $this->add($service);

        return true;
    }

    /**
     * @return list<int>
     */
    public static function hold(Request $request): array
    {
        return array_values(array_map('intval', $request->session()->get('wishlist', [])));
    }

    /**
     * @param  list<int>  $held
     */
    public function resume(array $held): void
    {
        $user = $this->request->user();

        if ($user === null) {
            return;
        }

        $ids = array_values(array_unique(array_merge(
            array_map('intval', $held),
            $this->storedIds($user),
        )));

        $this->request->session()->put('wishlist', $ids);
        $this->persist();
    }

    /**
     * @return list<int>
     */
    private function storedIds(User $user): array
    {
        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->pluck('service_id')
            ->map(fn (mixed $id) => (int) $id)
            ->all();
    }

    private function persist(): void
    {
        $user = $this->request->user();

        if ($user === null) {
            return;
        }

        WishlistItem::query()->where('user_id', $user->id)->delete();

        foreach ($this->ids() as $id) {
            WishlistItem::query()->create([
                'user_id' => $user->id,
                'service_id' => $id,
            ]);
        }
    }
}
