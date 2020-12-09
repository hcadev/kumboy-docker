<?php
namespace App\Http\Controllers\Profile\User;

use App\Models\UserActivity;
use Illuminate\Http\Request;

class ActivityController extends ProfileController
{
    public function searchActivity($userUuid, Request $request)
    {
        return redirect()
            ->route('user.activity-log', [$userUuid, 1, 25, $request->get('keyword')]);
    }

    public function viewActivities($userUuid, $currentPage = 1, $itemsPerPage = 15, $keyword = null)
    {
        $this->authorize('viewActivities', [new UserActivity(), $userUuid]);

        $this->profile->with('content', 'users.profile.activity.index');

        $userActivity = UserActivity::query()
            ->where('user_uuid', $userUuid);

        if (empty($keyword) === false) {
            $userActivity->whereRaw('MATCH (action_taken) AGAINST (? IN BOOLEAN MODE)', [$keyword.'*']);
        }

        $totalCount = $userActivity->count();
        $offset = ($currentPage - 1) * $itemsPerPage;

        $activities = $userActivity->skip($offset)
            ->take($itemsPerPage)
            ->orderByDesc('date_recorded')
            ->get();

        return $this->profile->with('contentData', [
                'user' => $this->user,
                'activities' => $activities,
                'itemStart' => $offset + 1,
                'itemEnd' => $activities->count() + $offset,
                'totalCount' => $totalCount,
                'currentPage' => $currentPage,
                'totalPages' => ceil($totalCount / $itemsPerPage),
                'itemsPerPage' => $itemsPerPage,
                'keyword' => $keyword,
            ]
        );
    }
}
