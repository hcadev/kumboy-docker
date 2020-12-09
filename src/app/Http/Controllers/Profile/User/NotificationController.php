<?php
namespace App\Http\Controllers\Profile\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends ProfileController
{
    public function searchNotification($userUuid, Request $request)
    {
        return redirect()
            ->route('user.notifications', [$userUuid, 1, 15, $request->get('keyword')]);
    }

    public function viewAll($userUuid, $currentPage = 1, $itemsPerPage = 15, $keyword = null)
    {
        if (Auth::user()->uuid !== $this->user->uuid) {
            abort(403);
        }

        $userNotifications = Auth::user()->notifications();

        if (empty($keyword) === false) {
            $userNotifications->whereRaw('MATCH (data) AGAINST (? IN BOOLEAN MODE)', [$keyword.'*']);
        }

        $offset = ($currentPage - 1) * $itemsPerPage;
        $totalCount = $userNotifications->count();

        $notifications = $userNotifications->skip($offset)
            ->take($itemsPerPage)
            ->get();

        return $this->profile
            ->with('content', 'users.profile.notifications.index')
            ->with('contentData', [
                'user' => $this->user,
                'notifications' => $notifications,
                'itemStart' => $offset + 1,
                'itemEnd' => $notifications->count() + $offset,
                'totalCount' => $totalCount,
                'currentPage' => $currentPage,
                'totalPages' => ceil($totalCount / $itemsPerPage),
                'itemsPerPage' => $itemsPerPage,
                'keyword' => $keyword,
            ]);
    }

    public function readNotification($userUuid, $notifId)
    {
        if (Auth::user()->uuid !== $this->user->uuid) {
            abort(403);
        }

        $notification = Auth::user()->unreadNotifications()->find($notifId);

        if ($notification === null) {
            abort(404);
        }

        try {
            $this->beginTransaction();

            $notification->markAsRead();

            switch ($notification->data['type']) {
                case 'store_request':
                    $redirect = redirect()
                        ->route('user.store-request-details', [
                            $notification->data['user_uuid'],
                            $notification->data['code']
                        ]);
                    break;
                case 'store_received':
                    $redirect = redirect()
                        ->route('store.products', $notification->data['store_uuid']);
                    break;
            }

            $this->commit();

            return $redirect;
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return back()
                ->with('messageType', 'danger')
                ->with('messageContent', 'Server error.');
        }
    }

    public function viewNotification($userUuid, $notifId)
    {
        if (Auth::user()->uuid !== $this->user->uuid) {
            abort(403);
        }

        $notification = Auth::user()->notifications()->find($notifId);

        if ($notification === null) {
            abort(404);
        }

        switch ($notification->data['type']) {
            case 'store_request':
                $redirect = redirect()
                    ->route('user.store-request-details', [
                        $notification->data['user_uuid'],
                        $notification->data['code']
                    ]);
                break;
        }

        return $redirect;
    }
}
