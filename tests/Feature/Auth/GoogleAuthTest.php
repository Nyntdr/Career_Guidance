<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_google_user_is_created_verified(): void
    {
        $this->mockGoogleUser('google-id-123', 'google@example.com', 'Google User');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'google@example.com')->firstOrFail();

        $this->assertSame('google-id-123', $user->google_id);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_unverified_user_is_verified_by_google_login(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'google@example.com']);

        $this->mockGoogleUser('google-id-123', 'google@example.com', 'Google User');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = $user->fresh();

        $this->assertSame('google-id-123', $user->google_id);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
    }

    private function mockGoogleUser(string $id, string $email, string $name): void
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn($id);
        $googleUser->shouldReceive('getEmail')->andReturn($email);
        $googleUser->shouldReceive('getName')->andReturn($name);
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(
                Mockery::mock()
                    ->shouldReceive('user')
                    ->andReturn($googleUser)
                    ->getMock()
            );
    }
}