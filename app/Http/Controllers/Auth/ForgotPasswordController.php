<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Validates the email field in the request and sends a password reset link to the provided email address.
     *
     */
    public function sendResetPost()
    {
        $this->validate(request(), [
            'email' => 'required|email',
        ]);
        $template = "xin chao day la link reset password";
        $user = User::where('email', request('email'))->first();
        PasswordReset::where('email', $user->email)->delete();
        $passwordReset = PasswordReset::create([
            'email' => $user->email,
            'token' => Str::random(60),
        ]);
//        Password::sendResetLink(
//            ['email' => request('email')],
//            function(CanResetPassword $user, $token) use ($template) {
                $user->notify(new ResetPasswordNotification($passwordReset->token, $template));
//            }
//        );
        return back()->with('status', trans('passwords.sent'));
    }

    public function sendResetGet(Request $request, $token)
    {
        $token = PasswordReset::where('token', $token)->first();
        if (is_null($token)) {
            return ('token not found33');
        }
//        if (Carbon::parse($token->created_at)->addMinutes(10)->isPast()) {
//            $token->update(['status'=>3]);
//            $token->delete();
//            return ('token expired');
//        }
        return view('auth.reset-two', ['token' => $token->token, 'email' => $token->email]);
    }

    public function sendResetPut(Request $request, $token)
    {
        $token = PasswordReset::where('token', $token)->first();
        if (!($token)) {
            return ('token not found222');
        }
        if (Carbon::parse($token->created_at)->addMinutes(10)->isPast()) {
            $token->update(['status'=>3]);
            $token->delete();
            return ('token expired');
        }
        $user = User::where('email', $token->email)->first();
        $user->update(['password' => bcrypt($request->password)]);
        $token->update(['status'=>2]);
        $token->delete();

        return 'da reset';
    }
}
