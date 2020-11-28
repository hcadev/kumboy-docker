<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MailService;
use App\Services\VerificationService;
use App\Traits\Validation\HasUserValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends DatabaseController
{
    use HasUserValidation;

    public function sendEmailVerificationCode(
        Request $request,
        VerificationService $verificationService,
        MailService $mailService
    ) {
        if ($request->wantsJson()) {
            $validator = Validator::make($request->all(), $this->getUserRules(['email']));

            if ($validator->fails()) {
                return response()->json($validator->errors()->get('email'), 400);
            }

            try {
                $this->beginTransaction();

                $code = $verificationService->generateVerificationCode($validator->validated()['email']);

                if ($code === null) {
                    $this->rollback();
                    return response()->json('Unable to generate verification code.', 500);
                }

                $mailService->sendEmailVerificationCode($validator->validated()['email'], $code);

                $this->commit();

                return response()->json('Verification code has been sent.');
            } catch (\Exception $e) {
                $this->rollback();
                logger($e);
                return response()->json('Server error.', 500);
            }
        }
    }

    public function showRegistrationForm()
    {
        return view('users.registration_form');
    }

    public function register(Request $request, User $userModel, VerificationService $verificationService)
    {
        $validatedData = $request->validate($this->getUserRules());

        try {
            $this->beginTransaction();

            $verificationService->consumeVerificationCode($validatedData['email'], $validatedData['verification_code']);

            $userModel->newQuery()
                ->create([
                    'uuid' => (string) Str::orderedUuid(),
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make($validatedData['password']),
                    'role' => 'user',
                ]);

            $this->commit();

            return back()
                ->with('messageType', 'success')
                ->with('messageContent', 'You have successfully registered. You may now login.');
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return back()
                ->with('messageType', 'success')
                ->with('messageContent', 'Server error.');
        }
    }
}
