<?php

namespace App\Providers;

use App\Models\Store;
use App\Models\StoreRequest;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('valid_code', function ($attribute, $value, $parameters, $validator) {
            $email = $validator->getData()[$parameters[0]];

            $verificationCode = VerificationCode::query()
                ->where('email', $email)
                ->where('code', $value)
                ->where('created_at', '<=', now())
                ->where('expires_at', '>=', now())
                ->where('status', 'unused')
                ->first();

            return $verificationCode !== null;
        }, 'The verification code is no longer valid.');

        Validator::extend('contact_number', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(09[0-9]{2}-?[0-9]{3}-?[0-9]{4})|([0-9]{1,5}-?[0-9]{3}-?[0-9]{4})$/', $value);
        }, 'Invalid format.');

        Validator::extend('store_application', function ($attribute, $value, $parameters, $validator) {
            $storeUuid = $validator->getData()['uuid'] ?? null;

            if ($storeUuid === null) {
                // check if store name is already taken
                $store = Store::query()
                    ->where('name', $value)
                    ->first();

                if ($store !== null) {
                    return false;
                }
            } else {
                $store = Store::query()
                    ->where('uuid', '!=', $storeUuid)
                    ->where('name', $value)
                    ->first();

                if ($store !== null) {
                    return false;
                }
            }

            $storeRequest = StoreRequest::query()
                ->where('status', 'pending')
                ->whereHas('storeApplication', function ($query) use ($value) {
                    $query->where('name', $value);
                })
                ->first();

            return $storeRequest === null;
        }, 'A pending request already exists for this store or the store name is already taken.');
    }
}
