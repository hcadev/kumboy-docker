<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MailService;
use App\Services\VerificationService;
use App\Traits\Validation\HasUserValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function register(Request $request, VerificationService $verificationService)
    {
        $validatedData = $request->validate($this->getUserRules());

        try {
            $this->beginTransaction();

            $verificationService->consumeVerificationCode($validatedData['email'], $validatedData['verification_code']);

            User::query()
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

    public function search(Request $request)
    {
        return redirect()
            ->route('user.view-all', [1, 25, $request->get('keyword')]);
    }

    public function viewAll($currentPage = 1, $itemsPerPage = 15, $keyword = null)
    {
        $this->authorize('viewAll', new User());

        $users = User::query();

        if (empty($keyword) === false) {
            $users->whereRaw('MATCH (uuid, name, email) AGAINST(? IN BOOLEAN MODE)', [$keyword.'*']);
        }

        $offset = ($currentPage - 1) * $itemsPerPage;
        $totalCount = $users->count();

        $list = $users->skip($offset)
            ->take($itemsPerPage)
            ->orderBy('name')
            ->get();

        return view('users.index')
            ->with('users', $list)
            ->with('itemStart', $offset + 1)
            ->with('itemEnd', $list->count() + $offset)
            ->with('totalCount', $totalCount)
            ->with('currentPage', $currentPage)
            ->with('totalPages', ceil($totalCount / $itemsPerPage))
            ->with('itemsPerPage', $itemsPerPage)
            ->with('keyword', $keyword);
    }

    public function findByEmail(Request $request)
    {
        if ($request->wantsJson()) {
            $user = User::query()
                ->where('email', $request->get('email'))
                ->whereNull('banned_until')
                ->first();

            if ($user === null) {
                return response()->json('User not found.', 404);
            } elseif ($user->uuid === Auth::user()->uuid) {
                return response()->json('Pick a user other than yourself.', 400);
            } else {
                return response()->json($user);
            }
        }
    }
}
