<?php
namespace App\Http\Controllers\Profile\User;

use App\Events\UserRequestCreate;
use App\Events\UserRequestApprove;
use App\Events\UserRequestCancel;
use App\Events\UserRequestReject;
use App\Models\Store;
use App\Models\UserRequest;
use App\Services\MapService;
use App\Traits\Validation\HasStoreApplicationValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequestController extends BaseController
{
    use HasStoreApplicationValidation;

    public function viewRequests($userUuid, $currentPage, $itemsPerPage, UserRequest $userRequestModel)
    {
        $this->authorize('viewUserRequests', [new UserRequest(), $userUuid]);

        $this->profile->with('content', 'users.profile.requests.index');

        $userRequest = $userRequestModel->newQuery()
            ->where('user_uuid', $userUuid);

        $totalCount = $userRequest->count();
        $offset = ($currentPage - 1) * $itemsPerPage;

        $requests = $userRequest->skip($offset)
            ->take($itemsPerPage)
            ->orderByDesc('created_at')
            ->get();

        return $this->profile->with('contentData', [
                'user' => $this->user,
                'requests' => $requests,
                'itemStart' => $offset + 1,
                'itemEnd' => $requests->count() + $offset,
                'totalCount' => $totalCount,
                'currentPage' => $currentPage,
                'totalPages' => ceil($totalCount / $itemsPerPage),
                'itemsPerPage' => $itemsPerPage,
            ]
        );
    }

    public function viewRequestDetails($userUuid, $requestCode, UserRequest $userRequestModel)
    {
        $userRequest = $userRequestModel->newQuery()
            ->where('user_uuid', $userUuid)
            ->where('code', $requestCode)
            ->first();

        switch ($userRequest->type) {
            case 'store application':
                $userRequest->store_application = $userRequest->storeApplication()->first()->toArray();
                break;
        }

        if ($userRequest === null) {
            abort(404);
        }

        $this->authorize('viewRequestDetails', $userRequest);

        return $this->profile
            ->with('content', 'users.profile.requests.details')
            ->with('contentData', ['request' => $userRequest]);
    }

    public function cancelRequest($userUuid, $requestCode, UserRequest $userRequestModel)
    {
        $userRequest = $userRequestModel->newQuery()
            ->where('user_uuid', $userUuid)
            ->where('code', $requestCode)
            ->where('status', 'pending')
            ->first();

        if ($userRequest === null) {
            abort(404);
        }

        $this->authorize('cancelRequest', $userRequest);

        try {
            $this->beginTransaction();

            $userRequest->update(['status' => 'cancelled']);

            event(new UserRequestCancel($userRequest));

            $this->commit();

            return back()
                ->with('messageType', 'success')
                ->with('messageContent', 'Request has been cancelled.');
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return back()
                ->with('messageType', 'danger')
                ->with('messageContent', 'Server error.');
        }
    }

    public function approveRequest(
        $userUuid,
        $requestCode,
        UserRequest $userRequestModel,
        Store $storeModel
    ) {
        $userRequest = $userRequestModel->newQuery()
            ->where('user_uuid', $userUuid)
            ->where('code', $requestCode)
            ->where('status', 'pending')
            ->first();

        if ($userRequest === null) {
            abort(404);
        }

        $this->authorize('approveRequest', $userRequest);

        try {
            $this->beginTransaction();

            switch ($userRequest->type) {
                case 'store application':
                    $storeApplication = $userRequest->storeApplication()->first();

                    $storeModel->newQuery()
                        ->create([
                            'uuid' => (string) Str::orderedUuid(),
                            'user_uuid' => $userUuid,
                            'name' => $storeApplication->name,
                            'contact_number' => $storeApplication->contact_number,
                            'address' => $storeApplication->address,
                            'map_coordinates' => $storeApplication->map_coordinates,
                            'map_address' => $storeApplication->map_address,
                            'open_until' => $storeApplication->open_until,
                        ]);
                    break;
            }

            $userRequest->update([
                'status' => 'approved',
                'evaluated_by' => Auth::user()->uuid,
            ]);

            event(new UserRequestApprove($userRequest));

            $this->commit();

            return back()
                ->with('messageType', 'success')
                ->with('messageContent', 'Request has been approved.');
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return back()
                ->with('messageType', 'danger')
                ->with('messageContent', 'Server error.');
        }
    }

    public function rejectRequest($userUuid, $requestCode, UserRequest $userRequestModel)
    {
        $userRequest = $userRequestModel->newQuery()
            ->where('user_uuid', $userUuid)
            ->where('code', $requestCode)
            ->where('status', 'pending')
            ->first();

        if ($userRequest === null) {
            abort(404);
        }

        $this->authorize('rejectRequest', $userRequest);

        try {
            $this->beginTransaction();

            $userRequest->update([
                'status' => 'rejected',
                'evaluated_by' => Auth::user()->uuid,
            ]);

            event(new UserRequestReject($userRequest));

            $this->commit();

            return back()
                ->with('messageType', 'success')
                ->with('messageContent', 'Request has been rejected.');
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return back()
                ->with('messageType', 'danger')
                ->with('messageContent', 'Server error.');
        }
    }

    public function showAddStoreForm($userUuid)
    {
        $this->authorize('addStoreApplication', [new UserRequest(), $userUuid]);

        return $this->profile
            ->with('content', 'users.profile.store.form')
            ->with('contentData', [
                'formTitle' => 'Add Store',
            ]);
    }

    public function createStoreApplication(
        $userUuid,
        Request $request,
        UserRequest $userRequestModel,
        MapService $mapService
    ) {
        $this->authorize('addStoreApplication', [new UserRequest(), $userUuid]);

        $validatedData = $request->validate($this->getStoreApplicationRules());

        try {
            $this->beginTransaction();

            if ($mapService->isValidAddress($validatedData['map_coordinates'], $validatedData['map_address'])) {
                // reference number date + user_uuid + last 4 digit unix timestamp
                $code = date('Ymd').$this->user->id.substr(strtotime('now'), -4);

                $userRequest = $userRequestModel->newQuery()
                    ->create([
                        'user_uuid' => $userUuid,
                        'code' => $code,
                        'type' => 'store application',
                        'status' => 'pending',
                    ]);

                $userRequest->storeApplication()
                    ->create([
                        'user_request_code' => $code,
                        'uuid' => null,
                        'name' => $validatedData['name'],
                        'contact_number' => $validatedData['contact_number'],
                        'address' => $validatedData['address'],
                        'map_coordinates' => $validatedData['map_coordinates'],
                        'map_address' => $validatedData['map_address'],
                        'open_until' => $validatedData['open_until'],
                        'attachment' => $code.'.pdf',
                    ]);

                event(new UserRequestCreate($userRequest));

                $request->file('attachment')->storeAs('attachments', $code.'.pdf');

                $this->commit();

                return back()
                    ->with('messageType', 'success')
                    ->with('messageContent', 'Application has been submitted. Please wait for approval. Ref#:'.$code);
            } else {
                $this->rollback();

                return back()
                    ->with('messageType', 'danger')
                    ->with('messageContent', 'Invalid map address or location is out of service area.');
            }
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return back()
                ->with('messageType', 'danger')
                ->with('messageContent', 'Server error.');
        }
    }
}
