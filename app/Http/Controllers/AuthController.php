<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    private $user;

    public function __construct()
    {
        $this->user = new User;
    }

    public function showLogin(){
        return view('auth.login');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();

        $google_id = $googleUser->getId();
        $google_email = $googleUser->getEmail();
        $google_name = $googleUser->getName();

        if (!$google_id || !$google_email || !$google_name) {
            return redirect()->route('login')
                ->withErrors([
                    'google' => 'Google Verification is Failed'
                ]);
        }

        $existUser = DB::table('users')->where('google_id', $google_id)->first();

        if ($existUser) {
            Auth::login(User::findOrFail($existUser->id));
            request()->session()->regenerate();

            return redirect()->route('chat.index');
        }

        $existEmail = DB::table('users')->where('email', $google_email)->first();

        if ($existEmail) {
            if ($existEmail->google_id) {
                abort(409, 'This Email is linked with another google account');
            }

            DB::table('users')->where('id', $existEmail->id)
                ->update([
                    'google_id' => $google_id,
                    'updated_at' => now(),
                ]);

            Auth::login(User::findOrFail($existEmail->id));
            request()->session()->regenerate();

            return redirect()->route('chat.index');
        }

        $base = Str::slug(Str::before($google_email, '@'), '');

        if (!$base) {
            $base = 'user';
        }

        $username = $base;
        $suffix = 1;

        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $base . $suffix++;
        }

        $userId = DB::table('users')->insertGetId([
            'name' => $google_name,
            'email' => $google_email,
            'username' => $username,
            'google_id' => $google_id,
            'password' => Hash::make(Str::random(48)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::findOrFail($userId);

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('chat.index');
    }

    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
