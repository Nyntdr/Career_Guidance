<?php

namespace App\Http\Middleware;

use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $onboarding = false;

        if ($request->user() && ! $request->session()->get('onboarding_skipped')) {
            $user = $request->user()->load(
                'profile.education',
                'profile.workExperience',
                'profile.userSkills',
                'profile.resumes',
            );
            $profile = $user->profile;

            if (! $profile || ($profile->onboarding_skipped_at === null
                && $profile->education->isEmpty()
                && $profile->workExperience->isEmpty()
                && $profile->userSkills->isEmpty()
                && $profile->resumes->isEmpty())) {
                $onboarding = true;
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'settings' => [
                'site_name' => Settings::get('site_name'),
                'currency_symbol' => Settings::get('currency_symbol'),
                'currency_position' => Settings::get('currency_position'),
            ],
            'onboarding' => $onboarding,
        ];
    }
}
