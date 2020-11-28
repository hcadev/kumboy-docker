<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function countPending(Request $request, UserRequest $userRequestModel)
    {
        $this->authorize('countPendingRequests', new UserRequest());

        if ($request->wantsJson()) {
            $count = $userRequestModel->newQuery()
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

    public function viewAll(
        UserRequest $userRequestModel,
        User $userModel,
        $currentPage,
        $itemsPerPage,
        $keyword = null)
    {
        $this->authorize('viewAllRequests', new UserRequest());

        $offset = ($currentPage - 1) * $itemsPerPage;

        $userRequest = $userRequestModel->newQuery()
            ->addSelect(['user_name' => $userModel->newQuery()
                ->whereColumn('uuid', 'user_requests.user_uuid')
                ->select('name')
                ->limit(1)
            ])
            ->addSelect(['evaluator_name' => $userModel->newQuery()
                ->whereColumn('uuid', 'user_requests.evaluated_by')
                ->select('name')
                ->limit(1)
            ]);

        if (empty($keyword) === false) {
            $userRequest->whereRaw('MATCH (user_uuid, code) AGAINST(? IN BOOLEAN MODE)', [$keyword.'*']);
        }

        $totalCount = $userRequest->count();

        $list = $userRequest->skip($offset)
            ->take($itemsPerPage)
            ->orderByRaw('status = "pending" DESC, created_at DESC')
            ->get();

        return view('requests.index')
            ->with('userRequests', $list)
            ->with('itemStart', $offset + 1)
            ->with('itemEnd', $list->count() + $offset)
            ->with('totalCount', $totalCount)
            ->with('currentPage', $currentPage)
            ->with('totalPages', ceil($totalCount / $itemsPerPage))
            ->with('itemsPerPage', $itemsPerPage)
            ->with('keyword', $keyword);
    }
}
