<?php
namespace App\Http\Controllers\Profile\User;

use App\Models\Store;

class StoreController extends BaseController
{
    public function showStores($userUuid, Store $storeModel)
    {
        $this->authorize('viewUserStores', [new Store(), $userUuid]);

        $this->profile->with('content', 'users.profile.store.index');

        $stores = $storeModel->newQuery()
            ->where('user_uuid', $userUuid)
            ->get();

        return $this->profile->with('contentData', ['stores' => $stores]);
    }
}
