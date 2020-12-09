<?php
namespace App\Http\Controllers\Profile\User;

use App\Events\StoreRequestCreate;
use App\Events\StoreRequestApprove;
use App\Events\StoreRequestCancel;
use App\Events\StoreRequestReject;
use App\Models\Store;
use App\Models\User;
use App\Models\StoreRequest;
use App\Services\MapService;
use App\Traits\Validation\HasStoreApplicationValidation;
use App\Traits\Validation\HasStoreTransferValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreRequestController extends StoreController
{
    use HasStoreApplicationValidation, HasStoreTransferValidation;

    public function searchRequest($userUuid, Request $request)
    {
        return redirect()
            ->route('user.store-requests', [$userUuid, 1, 25, $request->get('keyword')]);
    }

    public function viewRequests($userUuid, $currentPage = 1, $itemsPerPage = 15, $keyword = null)
    {
        $this->authorize('viewStoreRequests', [new StoreRequest(), $userUuid]);

        $this->profile->with('content', 'users.profile.stores.requests.index');

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
            ])
            ->where('user_uuid', $userUuid);

        if (empty($keyword) === false) {
            $storeRequest->where(function ($query) use ($keyword) {
                $query->whereRaw('MATCH (code, type, status) AGAINST (? IN BOOLEAN MODE)', [$keyword.'*'])
                    ->orWhereHas('evaluator', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', '%'.$keyword.'%');
                    });
            });
        }

        $totalCount = $storeRequest->count();
        $offset = ($currentPage - 1) * $itemsPerPage;

        $requests = $storeRequest->skip($offset)
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
                'keyword' => $keyword,
            ]
        );
    }

    public function viewRequestDetails($userUuid, $requestCode)
    {
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
            ])
            ->where('user_uuid', $userUuid)
            ->where('code', $requestCode)
            ->first();

        if ($storeRequest === null) {
            abort(404);
        }

        if (in_array($storeRequest->type, ['store creation', 'store update'])) {
            $storeRequest->store_application = $storeRequest->storeApplication()->first()->toArray();
        } elseif (strtolower($storeRequest->type) === 'store transfer') {
            $storeRequest->store_transfer = $storeRequest->storeTransfer()
                ->addSelect(['target_name' => User::query()
                    ->whereColumn('uuid', 'store_transfer_requests.target_uuid')
                    ->select('name')
                    ->limit(1)
                ])
                ->first()
                ->toArray();
            $storeRequest->store = Store::query()
                ->where('uuid', $storeRequest->store_transfer['uuid'])
                ->first();
        }

        $this->authorize('viewRequestDetails', $storeRequest);

        return $this->profile
            ->with('content', 'users.profile.stores.requests.details')
            ->with('contentData', ['request' => $storeRequest]);
    }

    public function cancelRequest($userUuid, $requestCode)
    {
        $storeRequest = StoreRequest::query()
            ->where('user_uuid', $userUuid)
            ->where('code', $requestCode)
            ->where('status', 'pending')
            ->first();

        if ($storeRequest === null) {
            abort(404);
        }

        $this->authorize('cancelRequest', $storeRequest);

        try {
            $this->beginTransaction();

            $storeRequest->update(['status' => 'cancelled']);

            event(new StoreRequestCancel($storeRequest));

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

    public function approveRequest($userUuid, $requestCode)
    {
        $storeRequest = StoreRequest::query()
            ->where('user_uuid', $userUuid)
            ->where('code', $requestCode)
            ->where('status', 'pending')
            ->first();

        if ($storeRequest === null) {
            abort(404);
        }

        $this->authorize('approveRequest', $storeRequest);

        try {
            $this->beginTransaction();

            switch ($storeRequest->type) {
                case 'store creation':
                    $storeApplication = $storeRequest->storeApplication()->first();
                    $this->addStore($userUuid, $storeApplication);
                    break;
                case 'store update':
                    $storeApplication = $storeRequest->storeApplication()->first();
                    $storeApplication->user_uuid = $storeRequest->user_uuid;
                    $this->updateStore($storeApplication->uuid, $storeApplication);
                    break;
                case 'store transfer':
                    $storeTransfer = $storeRequest->storeTransfer()->first();
                    $this->transferStore($userUuid, $storeTransfer->uuid, $storeTransfer->target_uuid);
                    break;
            }

            $storeRequest->update([
                'status' => 'approved',
                'evaluated_by' => Auth::user()->uuid,
            ]);

            event(new StoreRequestApprove($storeRequest));

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

    public function rejectRequest($userUuid, $requestCode)
    {
        $storeRequest = StoreRequest::query()
            ->where('user_uuid', $userUuid)
            ->where('code', $requestCode)
            ->where('status', 'pending')
            ->first();

        if ($storeRequest === null) {
            abort(404);
        }

        $this->authorize('rejectRequest', $storeRequest);

        try {
            $this->beginTransaction();

            $storeRequest->update([
                'status' => 'rejected',
                'evaluated_by' => Auth::user()->uuid,
            ]);

            event(new StoreRequestReject($storeRequest));

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
        $this->authorize('addStore', [new Store(), $userUuid]);

        return $this->profile
            ->with('content', 'users.profile.stores.requests.application.form')
            ->with('contentData', [
                'formTitle' => 'Add Store',
            ]);
    }

    public function showEditStoreForm($userUuid, $storeUuid)
    {
        $store = Store::query()
            ->where('uuid', $storeUuid)
            ->where('user_uuid', $userUuid)
            ->first();

        if ($store === null) {
            abort(404);
        }

        $this->authorize('editStore', $store);

        return $this->profile
            ->with('content', 'users.profile.users.profile.stores.requests.application.form')
            ->with('contentData', [
                'formTitle' => 'Edit Store',
                'formData' => $store,
            ]);
    }

    public function createStoreApplication(Request $request, MapService $mapService, $userUuid, $storeUuid = null)
    {
        $this->authorize('addStoreApplication', [new StoreRequest(), $userUuid]);

        // insert store uuid for name validation to work
        $request->merge(['uuid' => $storeUuid]);
        $validatedData = $request->validate($this->getStoreApplicationRules());

        try {
            $this->beginTransaction();

            if ($mapService->isValidAddress($validatedData['map_coordinates'], $validatedData['map_address'])) {
                // check if there are any changes in case of store update before proceeding
                if ($storeUuid !== null) {
                    $store = Store::query()
                        ->where('uuid', $storeUuid)
                        ->first();

                    if ($store === null) {
                        abort(404);
                    }

                    $store->fill($validatedData);

                    if ($store->isDirty() === false) {
                        return back()
                            ->with('messageType', 'success')
                            ->with('messageContent', 'No changes were made.');
                    }
                }

                // reference number date + user_uuid + last 4 digit unix timestamp
                $code = date('Ymd').$this->user->id.substr(strtotime('now'), -4);

                $storeRequest = StoreRequest::query()
                    ->create([
                        'user_uuid' => $userUuid,
                        'code' => $code,
                        'type' => $storeUuid === null ? 'store creation' : 'store update',
                        'status' => preg_match('/admin/i', Auth::user()->role) ? 'approved' : 'pending',
                        'evaluated_by' => preg_match('/admin/i', Auth::user()->role) ? Auth::user()->uuid : null,
                    ]);

                $storeApplication = $storeRequest->storeApplication()
                    ->create([
                        'request_code' => $code,
                        'uuid' => $storeUuid,
                        'name' => $validatedData['name'],
                        'contact_number' => $validatedData['contact_number'],
                        'address' => $validatedData['address'],
                        'map_coordinates' => $validatedData['map_coordinates'],
                        'map_address' => $validatedData['map_address'],
                        'open_until' => $validatedData['open_until'],
                        'attachment' => $code.'.pdf',
                    ]);

                if ($storeRequest->status === 'approved') {
                    if ($storeRequest->type === 'store creation') {
                        $this->addStore($userUuid, $storeApplication);
                    } elseif ($storeRequest->type === 'store update') {
                        $storeApplication->user_uuid = $storeRequest->user_uuid;
                        $this->updateStore($storeApplication->uuid, $storeApplication);
                    }
                }

                event(new StoreRequestCreate($storeRequest));

                $request->file('attachment')->storeAs('attachments', $code.'.pdf');

                $this->commit();

                if ($storeRequest->status === 'approved') {
                    $redirect = redirect()
                        ->route('user.stores', $userUuid)
                        ->with('messageType', 'success');

                    if ($storeRequest->type === 'store creation') {
                        $redirect->with('messageContent', 'Store has been created.');
                    } elseif ($storeRequest->type === 'store update') {
                        $redirect->with('messageContent', 'Store has been updated.');
                    }

                    return $redirect;
                } else {
                    return redirect()
                        ->route('user.stores', $userUuid)
                        ->with('messageType', 'success')
                        ->with('messageContent', 'Application has been submitted. Please wait for approval. Ref#:'.$code);
                }
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

    public function showTransferStoreForm($userUuid, $storeUuid)
    {
        $store = Store::query()
            ->where('user_uuid', $userUuid)
            ->where('uuid', $storeUuid)
            ->first();

        if ($store === null) {
            abort(404);
        }

        $this->authorize('transferStore', $store);

        return $this->profile
            ->with('content', 'users.profile.stores.requests.transfer.form')
            ->with('contentData', ['store' => $store]);
    }

    public function createStoreTransfer(Request $request, $userUuid, $storeUuid)
    {
        $store = Store::query()
            ->where('user_uuid', $userUuid)
            ->where('uuid', $storeUuid)
            ->first();

        $this->authorize('transferStore', $store);

        $validatedData = $request->validate($this->getStoreTransferRules());

        $target = User::query()
            ->where('email', $validatedData['email'])
            ->first();

        try {
            $this->beginTransaction();

            // check for duplicate request
            $storeRequest = StoreRequest::query()
                ->where('status', 'pending')
                ->whereHas('storeTransfer', function ($query) use ($storeUuid) {
                    $query->where('uuid', $storeUuid);
                })
                ->first();

            if ($storeRequest !== null) {
                return back()
                    ->with('messageType', 'danger')
                    ->with('messageContent', 'A pending store transfer request for this store already exists.');
            }

            // reference number date + user_uuid + last 4 digit unix timestamp
            $code = date('Ymd').$this->user->id.substr(strtotime('now'), -4);

            $storeRequest = StoreRequest::query()
                ->create([
                    'user_uuid' => $userUuid,
                    'code' => $code,
                    'type' => 'store transfer',
                    'status' => preg_match('/admin/i', Auth::user()->role) ? 'approved' : 'pending',
                    'evaluated_by' => preg_match('/admin/i', Auth::user()->role) ? Auth::user()->uuid : null,
                ]);

            $storeTransfer = $storeRequest->storeTransfer()
                ->create([
                    'request_code' => $code,
                    'uuid' => $store->uuid,
                    'target_uuid' => $target->uuid,
                    'attachment' => $code.'.pdf',
                ]);

            if ($storeRequest->status === 'approved') {
                $this->transferStore($userUuid, $store->uuid, $target->uuid);
            }

            event(new StoreRequestCreate($storeRequest));

            $request->file('attachment')->storeAs('attachments', $code.'.pdf');

            $this->commit();

            if ($storeRequest->status === 'approved') {
                return redirect()
                    ->route('user.stores', $userUuid)
                    ->with('messageType', 'success')
                    ->with('messageContent', 'Store has been transferred.');
            } else {
                return redirect()
                    ->route('user.stores', $userUuid)
                    ->with('messageType', 'success')
                    ->with('messageContent', 'Request has been submitted. Please wait for approval. Ref#:'.$code);
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
