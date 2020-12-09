<?php
namespace App\Http\Controllers;

use App\Events\UserLogin;
use App\Events\UserLogout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends DatabaseController
{
    public function showLoginForm()
    {
        return view('auth.login_form');
    }

    public function login(Request $request)
    {
        try {
            $this->beginTransaction();

            $user = User::query()
                ->where('email', $request->get('email'))
                ->first();

            if ($user === null OR Hash::check($request->get('password'), $user->password) === false) {
                return back()
                    ->with('messageType', 'danger')
                    ->with('messageContent', 'Email or password is incorrect.');
            } elseif ($user->banned_until !== null) {
                return back()
                    ->with('messageType', 'danger')
                    ->with('messageContent', 'Your account is currently banned until '.$user->banned_until.'.');
            }

            Auth::login($user);
            event(new UserLogin($user));

            $this->commit();

            return redirect()->route('home');
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return back()
                ->with('messageType', 'danger')
                ->with('messageContent', 'Server error.');
        }
    }

    public function logout()
    {
        try {
            $this->beginTransaction();

            event(new UserLogout(Auth::user()));
            Auth::logout();

            $this->commit();

            return redirect()->route('home');
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);
            return back()
                ->with('messageType', 'danger')
                ->with('messageContent', 'Unable to logout.');
        }
    }
}
