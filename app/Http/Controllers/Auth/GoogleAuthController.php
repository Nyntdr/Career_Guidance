<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google OAuth.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            Log::warning('Google OAuth state mismatch', [
                'error' => $e->getMessage(),
                'host' => request()->getHttpHost(),
            ]);

            return redirect()->route('login')
                ->with('status', 'Unable to sign in with Google. Your session was lost — make sure you open the site at ' . config('app.url') . ' and try again.');
        } catch (\Exception $e) {
            Log::warning('Google OAuth callback failed', [
                'error' => $e->getMessage(),
                'host' => request()->getHttpHost(),
            ]);

            return redirect()->route('login')
                ->with('status', 'Unable to sign in with Google. Please try again.');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();
        }

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            $user = User::create([
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'role' => UserRole::User,
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(
            route($user->role->dashboardRoute(), absolute: false)
        );
    }
}
