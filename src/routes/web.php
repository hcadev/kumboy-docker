<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Profile\User\ActivityController as UserActivityController;
use App\Http\Controllers\Profile\User\AddressBookController as UserAddressBookController;
use App\Http\Controllers\Profile\User\NotificationController as UserNotificationController;
use App\Http\Controllers\Profile\User\RequestController as UserRequestController;
use App\Http\Controllers\Profile\User\StoreController as UserStoreController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Profile\User\UserController as UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
 * Home Route Group
 */
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
 * Auth Route Group
 */
Route::prefix('auth')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])
        ->name('login');
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:login');
    Route::get('logout', [AuthController::class, 'logout'])
        ->name('logout');
});

/*
 * User Route Group
 */
Route::prefix('users')->group(function () {
    Route::get('register', [UserController::class, 'showRegistrationForm'])
        ->name('user.register');
    Route::post('register', [UserController::class, 'register']);
    Route::post('send-email-verification-code', [UserController::class, 'sendEmailVerificationCode']);

    // profile
    Route::middleware('auth')->prefix('{uuid}')->group(function () {
        // activity log
        Route::get('{currentPage}/{itemsPerPage}', [UserActivityController::class, 'viewActivities'])
            ->name('user.activity-log');

        // account settings
        Route::get('account-settings', [UserProfileController::class, 'showAccountSettings'])
            ->name('user.account-settings');
        Route::post('change-name', [UserProfileController::class, 'changeName'])
            ->name('user.change-name');
        Route::post('send-password-reset-code', [UserProfileController::class, 'sendPasswordResetCode']);
        Route::post('change-password', [UserProfileController::class, 'changePassword'])
            ->name('user.change-password');

        // address book
        Route::get('address-book', [UserAddressBookController::class, 'showAddressBook'])
            ->name('user.address-book');
        Route::get('add-address', [UserAddressBookController::class, 'showAddAddressForm'])
            ->name('user.add-address');
        Route::post('add-address', [UserAddressBookController::class, 'addAddress']);
        Route::get('edit-address/{id}', [UserAddressBookController::class, 'showEditAddressForm'])
            ->name('user.edit-address');
        Route::post('edit-address/{id}', [UserAddressBookController::class, 'editAddress']);
        Route::get('delete-address/{id}', [UserAddressBookController::class, 'showDeleteAddressDialog'])
            ->name('user.delete-address');
        Route::post('delete-address/{id}', [UserAddressBookController::class, 'deleteAddress']);

        // store
        Route::get('stores', [UserStoreController::class, 'showStores'])
            ->name('user.stores');

        // user requests
        Route::get('requests/{currentPage}/{itemsPerPage}', [UserRequestController::class, 'viewRequests'])
            ->name('user.requests');
        Route::get('request-details/{code}', [UserRequestController::class, 'viewRequestDetails'])
            ->name('user.request-details');
        Route::post('cancel-request/{code}', [UserRequestController::class, 'cancelRequest'])
            ->name('user.cancel-request');
        Route::post('approve-request/{code}', [UserRequestController::class, 'approveRequest'])
            ->name('user.approve-request');
        Route::post('reject-request/{code}', [UserRequestController::class, 'rejectRequest'])
            ->name('user.reject-request');
        Route::get('add-store', [UserRequestController::class, 'showAddStoreForm'])
            ->name('user.add-store');
        Route::post('add-store', [UserRequestController::class, 'createStoreApplication']);

        // notifications
        Route::get('notifications/{currentPage}/{itemsPerPage}', [UserNotificationController::class, 'viewAll'])
            ->name('user.notifications');
    });
});

/*
 * Request Route Group
 */
Route::middleware('auth')->prefix('requests')->group(function () {
    Route::get('count-pending', [RequestController::class, 'countPending'])
        ->name('request.count-pending');
    Route::post('search', [RequestController::class, 'search'])
        ->name('request.search');
    Route::get('{currentPage}/{itemsPerPage}/{keyword?}', [RequestController::class, 'viewAll'])
        ->name('request.view-all');
});

/*
 * Notification Route Group
 */
Route::middleware('auth')->prefix('notifications')->group(function () {
    Route::get('count-unread', [NotificationController::class, 'countUnread'])
        ->name('notification.count-unread');
});