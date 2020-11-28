<?php
namespace App\Http\Controllers\Profile\User;

use App\Events\UserAddAddress;
use App\Events\UserDeleteAddress;
use App\Events\UserEditAddress;
use App\Models\UserAddressBook;
use App\Services\MapService;
use App\Traits\Validation\HasUserAddressValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressBookController extends BaseController
{
    use HasUserAddressValidation;

    public function showAddressBook($userUuid, UserAddressBook $userAddressBookModel)
    {
        $this->authorize('viewAddressBook', [new UserAddressBook(), $userUuid]);

        $this->profile->with('content', 'users.profile.address_book.index');

        $userAddressBook = $userAddressBookModel->newQuery()
            ->where('user_uuid', $userUuid)
            ->get();

        return $this->profile->with('contentData', ['addressBook' => $userAddressBook]
        );
    }

    public function showAddAddressForm($userUuid)
    {
        // pass an instance of UserAddressBook to indicate that UserAddressBookPolicy will be used
        $this->authorize('addAddress', [new UserAddressBook(), $userUuid]);

        return $this->profile
            ->with('content', 'users.profile.address_book.form')
            ->with('contentData', [
                'formTitle' => 'Add Address',
            ]);
    }

    public function addAddress($userUuid, Request $request, UserAddressBook $userAddressBookModel, MapService $mapService)
    {
        $this->authorize('addAddress', [new UserAddressBook(), $userUuid]);

        $validatedData = $request->validate($this->getUserAddressRules());
        $validatedData['user_uuid'] = $userUuid;

        try {
            $this->beginTransaction();

            if ($mapService->isValidAddress($validatedData['map_coordinates'], $validatedData['map_address'])) {
                $userAddress = $userAddressBookModel->newQuery()
                    ->create($validatedData);

                event(new UserAddAddress($userAddress));

                $this->commit();

                return redirect()->route('user.address-book', $userUuid);
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

    public function showEditAddressForm($userUuid, $addressID, UserAddressBook $userAddressBookModel)
    {
        $userAddress = $userAddressBookModel->newQuery()
            ->find($addressID);

        if ($userAddress === null) {
            abort(404);
        }

        $this->authorize('edit-address', $userAddress);

        return $this->profile->with('content', 'users.profile.address_book.form')
            ->with('contentData', [
                'formTitle' => 'Edit Address',
                'formData' => $userAddress,
            ]);
    }

    public function editAddress(
        $userUuid,
        $addressID,
        Request $request,
        UserAddressBook $userAddressBookModel,
        MapService $mapService
    ) {
        $userAddress = $userAddressBookModel->newQuery()
            ->find($addressID);

        if ($userAddress === null) {
            abort(404);
        }

        $this->authorize('editAddress', $userAddress);

        $validatedData = $request->validate($this->getUserAddressRules());
        $validatedData['user_uuid'] = $userUuid;

        try {
            $this->beginTransaction();

            if ($mapService->isValidAddress($validatedData['map_coordinates'], $validatedData['map_address'])) {
                $userAddress->fill($validatedData);
                $oldAddress = $userAddress->getOriginal();
                $userAddress->save();

                event(new UserEditAddress($userAddress, $oldAddress));

                $this->commit();

                return back()
                    ->with('messageType', 'success')
                    ->with('messageContent', $userAddress->wasChanged()
                        ? 'Address has been changed.'
                        : 'No changes made.'
                    );
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

    public function showDeleteAddressDialog($userUuid, $addressID, UserAddressBook $userAddressBookModel)
    {
        $address = $userAddressBookModel->newQuery()
            ->find($addressID);

        if ($address === null) {
            abort(404);
        }

        $this->authorize('deleteAddress', $address);

        return $this->profile
            ->with('content', 'users.profile.address_book.delete_dialog')
            ->with('contentData', [
                'address' => $address,
            ]);
    }

    public function deleteAddress($userUuid, $addressID, UserAddressBook $userAddressBookModel)
    {
        $userAddress = $userAddressBookModel->newQuery()
            ->find($addressID);

        if ($userAddress === null) {
            abort(404);
        }

        $this->authorize('deleteAddress', $userAddress);

        try {
            $this->beginTransaction();

            $userAddress->delete();

            event(new UserDeleteAddress($userAddress));

           $this->commit();

            return redirect()
                ->route('user.address-book', $userUuid)
                ->with('messageType', 'success')
                ->with('messageContent', 'Address has been deleted.');
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return redirect()
                ->route('user.address-book', $userUuid)
                ->with('messageType', 'danger')
                ->with('messageContent', 'Server error.');
        }
    }
}
