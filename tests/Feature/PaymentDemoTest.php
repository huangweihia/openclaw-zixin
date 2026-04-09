<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_order_and_simulate_pay(): void
    {
        config(['openclaw.payment_simulate' => true]);

        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post(route('payments.orders.store'), ['plan' => 'vip'])
            ->assertRedirect();

        $order = Order::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->status);

        $this->actingAs($user)->get(route('payments.result', ['order_no' => $order->order_no]))
            ->assertOk()
            ->assertSee('模拟支付成功', false);

        $this->actingAs($user)->post(route('payments.simulate-paid', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('paid', $order->status);

        $user->refresh();
        $this->assertSame('vip', $user->role);
        $this->assertNotNull($user->subscription_ends_at);
    }

    public function test_simulate_pay_disabled_returns_404(): void
    {
        config(['openclaw.payment_simulate' => false]);

        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_no' => 'TEST-ORDER-1',
            'product_type' => 'subscription_plan',
            'product_id' => 1,
            'amount' => 29.00,
            'status' => 'pending',
        ]);

        $this->actingAs($user)->post(route('payments.simulate-paid', $order))
            ->assertNotFound();
    }
}
