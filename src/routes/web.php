<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Profile\Store\ProductController as StoreProductController;
use App\Http\Controllers\Profile\User\ActivityController as UserActivityController;
use App\Http\Controllers\Profile\User\AddressBookController as UserAddressBookController;
use App\Http\Controllers\Profile\User\NotificationController as UserNotificationController;
use App\Http\Controllers\Profile\User\StoreRequestController as UserStoreRequestController;
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
    Route::post('search', [UserController::class, 'search'])
        ->name('user.search');
    Route::get('view-all/{currentPage?}/{itemsPerPage?}/{keyword?}', [UserController::class, 'viewAll'])
        ->name('user.view-all');
    Route::post('find-email', [UserController::class, 'findByEmail'])
        ->name('user.find-email');

    // profile
    Route::middleware('auth')->prefix('{uuid}')->group(function () {
        // activity log
        Route::post('activities/search', [UserActivityController::class, 'searchActivity'])
            ->name('user.search-activity');
        Route::get('activities/{currentPage?}/{itemsPerPage?}/{keyword?}', [UserActivityController::class, 'viewActivities'])
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
        Route::get('stores/add', [UserStoreRequestController::class, 'showAddStoreForm'])
            ->name('user.add-store');
        Route::post('stores/add', [UserStoreRequestController::class, 'createStoreApplication']);
        Route::get('stores/{store}/edit', [UserStoreRequestController::class, 'showEditStoreForm'])
            ->name('user.edit-store');
        Route::post('stores/{store}/edit', [UserStoreRequestController::class, 'createStoreApplication']);
        Route::get('stores/{store}/transfer', [UserStoreRequestController::class, 'showTransferStoreForm'])
            ->name('user.transfer-store');
        Route::post('stores/{store}/transfer', [UserStoreRequestController::class, 'createStoreTransfer']);

        Route::prefix('stores/requests')->group(function () {
            Route::post('search', [UserStoreRequestController::class, 'searchRequest'])
                ->name('user.search-store-request');
            Route::get('{currentPage?}/{itemsPerPage?}/{keyword?}', [UserStoreRequestController::class, 'viewRequests'])
                ->name('user.store-requests');
            Route::get('{code}/view', [UserStoreRequestController::class, 'viewRequestDetails'])
                ->name('user.store-request-details');
            Route::post('{code}/cancel', [UserStoreRequestController::class, 'cancelRequest'])
                ->name('user.cancel-store-request');
            Route::post('{code}/approve', [UserStoreRequestController::class, 'approveRequest'])
                ->name('user.approve-store-request');
            Route::post('reject-request/{code}', [UserStoreRequestController::class, 'rejectRequest'])
                ->name('user.reject-store-request');
        });

        // notifications
        Route::post('notifications/search', [UserNotificationController::class, 'searchNotification'])
            ->name('user.search-notification');
        Route::get('notifications/{currentPage?}/{itemsPerPage?}/{keyword?}', [UserNotificationController::class, 'viewAll'])
            ->name('user.notifications');
        Route::get('notifications/{notification}/read', [UserNotificationController::class, 'readNotification'])
            ->name('user.read-notification');
        Route::get('notifications/{notification}/view', [UserNotificationController::class, 'viewNotification'])
            ->name('user.view-notification');
    });
});

/*
 * Store Route Group
 */
Route::prefix('stores/{uuid}')->group(function () {
    Route::get('products/{currentPage?}/{itemPerPage?}/{keyword?}', [StoreProductController::class, 'viewStoreProducts'])
        ->name('store.products');
});

/*
 * Request Route Group
 */
Route::middleware('auth')->prefix('requests')->group(function () {
    Route::get('count-pending', [RequestController::class, 'countPending'])
        ->name('request.count-pending');
    Route::post('search', [RequestController::class, 'search'])
        ->name('request.search');
    Route::get('{currentPage?}/{itemsPerPage?}/{keyword?}', [RequestController::class, 'viewAll'])
        ->name('request.view-all');
});

/*
 * Notification Route Group
 */
Route::middleware('auth')->prefix('notifications')->group(function () {
    Route::get('count-unread', [NotificationController::class, 'countUnread'])
        ->name('notification.count-unread');
});