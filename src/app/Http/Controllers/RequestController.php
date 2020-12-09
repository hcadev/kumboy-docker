<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StoreRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function countPending(Request $request, StoreRequest $storeRequestModel)
    {
        $this->authorize('countPendingRequests', new StoreRequest());

        if ($request->wantsJson()) {
            $count = StoreRequest::query()
                ->where('status', 'pending')
                ->count();

            return response()->json($count);
        }
    }

    public function search(Request $request)
    {
        return redirect()
            ->route('request.view-all', [1, 25, $request->get('keyword')]);
    }

    public function viewAll($currentPage = 1, $itemsPerPage = 15, $keyword = null)
    {
        $this->authorize('viewAllRequests', new StoreRequest());

        $offset = ($currentPage - 1) * $itemsPerPage;

        $storeRequest = StoreRequest::query()
            ->addSelect(['user_name' => User::query()
                ->whereColumn('uuid', 'store_requests.user_uuid')
                ->select('name')
                ->limit(1)
            ])
            ->addSelect(['evaluator_name' => User::query()
                ->whereColumn('uuid', 'store_requests.evaluated_by')
                ->select('name')
                ->limit(1)
            ]);

        if (empty($keyword) === false) {
            $storeRequest->whereRaw('MATCH (code, type, status) AGAINST(? IN BOOLEAN MODE)', [$keyword.'*'])
                ->orWhereHas('user', function ($query) use ($keyword) {
                    $query->where('name', 'LIKE', '%'.$keyword.'%');
                })
                ->orWhereHas('evaluator', function ($query) use ($keyword) {
                    $query->where('name', 'LIKE', '%'.$keyword.'%');
                });
        }

        $totalCount = $storeRequest->count();

        $list = $storeRequest->skip($offset)
            ->take($itemsPerPage)
            ->orderByRaw('status = "pending" DESC, created_at DESC')
            ->get();

        return view('requests.index')
            ->with('storeRequests', $list)
            ->with('itemStart', $offset + 1)
            ->with('itemEnd', $list->count() + $offset)
            ->with('totalCount', $totalCount)
            ->with('currentPage', $currentPage)
            ->with('totalPages', ceil($totalCount / $itemsPerPage))
            ->with('itemsPerPage', $itemsPerPage)
            ->with('keyword', $keyword);
    }
}
