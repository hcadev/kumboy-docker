<?php
namespace App\Http\Controllers\Profile\User;

use App\Models\UserActivity;

class ActivityController extends BaseController
{
    public function viewActivities($userUuid, $currentPage, $itemsPerPage, UserActivity $userActivityModel)
    {
        $this->authorize('viewActivities', [new UserActivity(), $userUuid]);

        $this->profile->with('content', 'users.profile.activity.index');

        $userActivity = $userActivityModel->newQuery()
            ->where('user_uuid', $userUuid);

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
            ]
        );
    }
}
