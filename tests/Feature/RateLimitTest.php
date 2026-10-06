<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * يختبر الـ limiter المسمّى "auth" المعرّف في AppServiceProvider:
 *   Limit::perMinute(5)->by(strtolower(email) . '|' . ip)
 * والمطبّق على POST /api/login و POST /api/register.
 *
 * ملاحظة: CACHE_STORE=array في phpunit.xml، فالعدّاد يبدأ من الصفر في كل تيست.
 */
class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    private const MAX_ATTEMPTS = 5;

    private const EMAIL = 'nesrine@example.com';

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'email'    => self::EMAIL,
            'password' => bcrypt('correct-password'),
        ]);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function login(string $email = self::EMAIL, string $password = 'wrong-password', string $ip = '127.0.0.1')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/api/login', [
                'email'    => $email,
                'password' => $password,
            ]);
    }

    private function exhaustLimit(string $email = self::EMAIL, string $ip = '127.0.0.1'): void
    {
        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $this->login($email, 'wrong-password', $ip);
        }
    }

    // ---------------------------------------------------------------
    // Happy path
    // ---------------------------------------------------------------

    public function test_requests_within_the_limit_are_not_throttled(): void
    {
        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $this->login()->assertStatus(401); // بيانات خاطئة لكن مو 429
        }
    }

    public function test_the_fifth_attempt_is_still_allowed_and_the_sixth_is_blocked(): void
    {
        // الحالة الحدّية: الطلب رقم 5 بالظبط لازم يمرّ
        for ($i = 1; $i <= self::MAX_ATTEMPTS; $i++) {
            $this->login()->assertStatus(401);
        }

        $this->login()->assertStatus(429);
    }

    public function test_blocked_response_has_retry_after_and_rate_limit_headers(): void
    {
        $this->exhaustLimit();

        $response = $this->login();

        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
        $response->assertHeader('X-RateLimit-Limit', (string) self::MAX_ATTEMPTS);
        $response->assertHeader('X-RateLimit-Remaining', '0');

        $retryAfter = (int) $response->headers->get('Retry-After');
        $this->assertGreaterThan(0, $retryAfter);
        $this->assertLessThanOrEqual(60, $retryAfter);
    }

    public function test_remaining_header_counts_down_on_allowed_requests(): void
    {
        $first  = $this->login();
        $second = $this->login();

        $this->assertSame(
            (int) $first->headers->get('X-RateLimit-Remaining') - 1,
            (int) $second->headers->get('X-RateLimit-Remaining')
        );
    }

    // ---------------------------------------------------------------
    // Window reset
    // ---------------------------------------------------------------

    public function test_still_blocked_just_before_the_window_expires(): void
    {
        $this->exhaustLimit();
        $this->login()->assertStatus(429);

        $this->travel(50)->seconds();

        $this->login()->assertStatus(429);
    }

    public function test_limit_resets_after_one_minute(): void
    {
        $this->exhaustLimit();
        $this->login()->assertStatus(429);

        $this->travel(61)->seconds();

        $this->login()->assertStatus(401); // مرّ من الـ limiter
    }

    // ---------------------------------------------------------------
    // Isolation between buckets (email|ip)
    // ---------------------------------------------------------------

    public function test_a_different_email_has_its_own_bucket(): void
    {
        $this->exhaustLimit(self::EMAIL);

        $this->login('someone.else@example.com')->assertStatus(401);
    }

    public function test_a_different_ip_has_its_own_bucket(): void
    {
        $this->exhaustLimit(self::EMAIL, '10.0.0.1');

        $this->login(self::EMAIL, 'wrong-password', '10.0.0.1')->assertStatus(429);
        $this->login(self::EMAIL, 'wrong-password', '10.0.0.2')->assertStatus(401);
    }

    public function test_email_is_case_insensitive_for_the_bucket_key(): void
    {
        $this->exhaustLimit('nesrine@example.com');

        // نفس الإيميل بأحرف كبيرة لازم يضرب نفس العدّاد
        $this->login('NESRINE@Example.com')->assertStatus(429);
    }

    // ---------------------------------------------------------------
    // What counts as an attempt
    // ---------------------------------------------------------------

    public function test_successful_logins_also_count_towards_the_limit(): void
    {
        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $this->login(self::EMAIL, 'correct-password')->assertStatus(200);
        }

        $this->login(self::EMAIL, 'correct-password')->assertStatus(429);
    }

    public function test_correct_password_is_still_blocked_once_the_limit_is_hit(): void
    {
        $this->exhaustLimit();

        // حتى لو كلمة السر صحيحة، الـ throttle بيشتغل قبل الـ controller
        $this->login(self::EMAIL, 'correct-password')->assertStatus(429);
    }

    public function test_validation_failures_count_towards_the_limit(): void
    {
        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $this->postJson('/api/login', ['email' => self::EMAIL])->assertStatus(422);
        }

        $this->postJson('/api/login', ['email' => self::EMAIL])->assertStatus(429);
    }

    public function test_register_and_login_share_the_same_bucket_for_the_same_email_and_ip(): void
    {
        $this->exhaustLimit(self::EMAIL);

        // register بيستخدم نفس الـ limiter ونفس الـ key (email|ip)
        $this->postJson('/api/register', [
            'first_name'            => 'Nesrine',
            'last_name'             => 'Test',
            'email'                 => self::EMAIL,
            'password'              => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            'role'                  => 'freelancer',
        ])->assertStatus(429);
    }

    public function test_register_is_throttled_per_email_and_ip(): void
    {
        $payload = [
            'first_name'            => 'New',
            'last_name'             => 'User',
            'email'                 => 'new.user@example.com',
            'password'              => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            'role'                  => 'client',
        ];

        // أول طلب بينجح (201/200)، والباقي بيفشلوا بالـ validation لأن الإيميل صار مأخوذ
        $this->postJson('/api/register', $payload)->assertSuccessful();

        for ($i = 1; $i < self::MAX_ATTEMPTS; $i++) {
            $this->postJson('/api/register', $payload)->assertStatus(422);
        }

        $this->postJson('/api/register', $payload)->assertStatus(429);
    }

    // ---------------------------------------------------------------
    // Scope
    // ---------------------------------------------------------------

    public function test_public_project_listing_is_not_affected_by_the_auth_limiter(): void
    {
        $this->exhaustLimit();

        $this->getJson('/api/projects')->assertStatus(200);
    }

    public function test_clearing_the_limiter_unblocks_the_user(): void
    {
        $this->exhaustLimit();
        $this->login()->assertStatus(429);

        // نفس صيغة المفتاح اللي بيبنيها الـ ThrottleRequests للـ named limiters
        $key = md5('auth' . strtolower(self::EMAIL) . '|127.0.0.1');
        app(RateLimiter::class)->clear($key);

        $this->login()->assertStatus(401);
    }
}
