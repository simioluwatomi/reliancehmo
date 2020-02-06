<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @internal
 *
 * @coversNothing
 */
class UsersCanPayTheirSubscriptionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function guest_users_can_not_visit_the_subscription_payment_page()
    {
        $this->withoutExceptionHandling();
        $this->expectException(AuthenticationException::class);

        $this->get(route('subscription.create', $this->user));
    }

    /** @test */
    public function authenticated_users_can_visit_the_subscription_payment_page()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);

        $this->get(route('subscription.create', $this->user))
            ->assertViewIs('subscription.create')
            ->assertViewHas('user', $this->user);
    }
}
