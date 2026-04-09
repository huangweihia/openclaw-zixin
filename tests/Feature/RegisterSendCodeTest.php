<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterSendCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_code_returns_json_success_for_new_email(): void
    {
        $response = $this->postJson('/register/send-code', [
            'email' => 'new-user-' . uniqid('', true) . '@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', '验证码已发送');
    }

    public function test_send_code_rejects_registered_email(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/register/send-code', [
            'email' => $user->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_send_code_rate_limits_within_sixty_seconds(): void
    {
        $email = 'rate-' . uniqid('', true) . '@example.com';

        $this->postJson('/register/send-code', ['email' => $email])->assertOk();

        $again = $this->postJson('/register/send-code', ['email' => $email]);
        $again->assertStatus(422);
        $again->assertJsonValidationErrors(['email']);
    }

    public function test_send_code_validates_email_format(): void
    {
        $this->postJson('/register/send-code', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_send_code_returns_422_when_mailer_throws(): void
    {
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('SMTP connection failed'));

        $this->postJson('/register/send-code', [
            'email' => 'mail-fail-' . uniqid('', true) . '@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
