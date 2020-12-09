<?php
namespace App\Http\Controllers\Profile\Store;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ProfileController extends Controller
{
    public function __construct(Request $request)
    {
        $store = Store::query()
            ->where('uuid', $request->route('uuid'))
            ->first();

        if ($store === null) {
            abort(404);
        }

        View::share('store', $store);
    }
}
