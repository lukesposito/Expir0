<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:10',
                'max:64',
                PasswordRule::min(10)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'regex:/^(?:.*[^A-Za-z0-9]){2,}.*$/',
            ],
        ], [
            'password.min' => 'La password deve contenere almeno 10 caratteri.',
            'password.max' => 'La password non può superare 64 caratteri.',
            'password.mixed' => 'La password deve contenere almeno una lettera maiuscola e una minuscola.',
            'password.letters' => 'La password deve contenere almeno una lettera.',
            'password.numbers' => 'La password deve contenere almeno un numero.',
            'password.symbols' => 'La password deve contenere almeno un carattere speciale.',
            'password.regex' => 'La password deve contenere almeno 2 caratteri speciali.',
            'password.uncompromised' => 'Questa password è stata rilevata in violazioni note. Scegline un\'altra più sicura.',
            'password.confirmed' => 'La conferma password non corrisponde.',
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? back()->withInput($request->only('email'))->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
