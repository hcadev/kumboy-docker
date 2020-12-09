<?php
namespace App\Http\Controllers\Profile\User;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Str;

class StoreController extends ProfileController
{
    public function showStores($userUuid)
    {
        $this->authorize('viewUserStores', [new Store(), $userUuid]);

        $this->profile->with('content', 'users.profile.stores.index');

        $stores = Store::query()
            ->where('user_uuid', $userUuid)
            ->get();

        return $this->profile->with('contentData', ['stores' => $stores]);
    }

    protected function addStore($userUuid, $data)
    {
        Store::query()
            ->create([
                'uuid' => (string) Str::orderedUuid(),
                'user_uuid' => $userUuid,
                'name' => $data->name,
                'contact_number' => $data->contact_number,
                'address' => $data->address,
                'map_coordinates' => $data->map_coordinates,
                'map_address' => $data->map_address,
                'open_until' => $data->open_until,
            ]);
    }

    protected function updateStore($storeUuid, $data)
    {
        $store = Store::query()
            ->where('uuid', $storeUuid)
            ->first();

        if ($store === null) {
            abort(404);
        }

        $store->update([
            'user_uuid' => $data['user_uuid'],
            'name' => $data['name'],
            'contact_number' => $data['contact_number'],
            'address' => $data['address'],
            'map_address' => $data['map_address'],
            'map_coordinates' => $data['map_coordinates'],
            'open_until' => $data['open_until'],
        ]);
    }

    protected function transferStore($ownerUuid, $storeUuid, $targetUuid)
    {
        $store = Store::query()
            ->where('uuid', $storeUuid)
            ->where('user_uuid', $ownerUuid)
            ->first();

        $target = User::query()
            ->where('uuid', $targetUuid)
            ->first();

        if ($store === null OR $target === null) {
            abort(404);
        }

        $store->update([
            'user_uuid' => $targetUuid,
        ]);
    }
}
