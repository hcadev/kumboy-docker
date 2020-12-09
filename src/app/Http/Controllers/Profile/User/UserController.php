<?php

namespace App\Http\Controllers\Profile\User;

use App\Events\UserChangeName;
use App\Events\UserChangePassword;
use App\Services\MailService;
use App\Services\VerificationService;
use App\Traits\Validation\HasUserValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends ProfileController
{
    use HasUserValidation;

    public function showAccountSettings($uuid)
    {
        $this->authorize('viewAccountSettings', $this->user);

        return $this->profile
            ->with('content', 'users.profile.account_settings.index')
            ->with('contentData', ['user' => $this->user]);
    }

    public function changeName($uuid, Request $request)
    {
        $this->authorize('changeName', $this->user);

        $validatedData = $request->validate($this->getUserRules(['name']));

        try {
            $this->beginTransaction();

            $oldName = $this->user->name;
            $this->user->update(['name' => $validatedData['name']]);

            if ($this->user->wasChanged()) {
                event(new UserChangeName($this->user, $oldName));
            }

            $this->commit();

            return back()
                ->with('messageType', 'success')
                ->with('messageContent', $this->user->wasChanged()
                    ? 'Name has been changed.'
                    : 'No changes were made.'
                );
        } catch (\Exception $e) {
            $this->beginTransaction();
            logger($e);
            return back()
                ->with('messageType', 'danger')
                ->with('messageContent', 'Server error.');
        }
    }

    public function sendPasswordResetCode(
        $uuid,
        Request $request,
        VerificationService $verificationService,
        MailService $mailService
    ) {
        if ($request->wantsJson()) {
            $gate = Gate::inspect('changePassword', $this->user);

            if ($gate->allowed()) {
                try {
                    $this->beginTransaction();

                    $verificationCode = $verificationService->generateVerificationCode($this->user->email);

                    if ($verificationCode === null) {
                        return response()->json('Unable to generate verification code.', 500);
                    }

                    if ($mailService->sendPasswordResetVerificationCode($this->user->email, $verificationCode)) {
                        $this->commit();
                        return response()->json('Verification code has been sent.');
                    } else {
                        return response()->json('Unable to send verification code.', 500);
                    }
                } catch (\Exception $e) {
                    $this->rollback();
                    logger($e);
                    return response()->json('Server error.', 500);
                }
            } else {
                return response()->json('Forbidden', 403);
            }
        }
    }

    public function changePassword($uuid, Request $request, VerificationService $verificationService)
    {
        $this->authorize('changePassword', $this->user);

        // inject user email to request for verification code validation to work
        $request->merge(['email' => $this->user->email]);
        $validatedData = $request->validate($this->getUserRules([
            'password',
            'password_confirmation',
            'verification_code',
        ]));

        try {
            $this->beginTransaction();

            if ($verificationService->consumeVerificationCode($this->user->email, $validatedData['verification_code'])) {
                $this->user->update(['password' => Hash::make($validatedData['password'])]);
                event(new UserChangePassword($this->user));
                $this->commit();

                return back()
                    ->with('messageType', 'success')
                    ->with('messageContent', 'Password has been changed.');
            } else {
                $this->rollback();

                return back()
                    ->with('messageType', 'danger')
                    ->with('messageContent', 'Unable to use verification code.');
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
