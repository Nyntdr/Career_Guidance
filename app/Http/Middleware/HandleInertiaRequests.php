<?php

namespace App\Http\Middleware;

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

        if ($request->user() && !$request->session()->get('onboarding_skipped')) {
            $user = $request->user()->load('profile.education', 'profile.workExperience', 'profile.userSkills');
            $profile = $user->profile;

            if (! $profile || ($profile->education->isEmpty() && $profile->workExperience->isEmpty() && $profile->userSkills->isEmpty())) {
                $onboarding = true;
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'onboarding' => $onboarding,
        ];
    }
}
