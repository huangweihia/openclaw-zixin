<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class EmailSubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('email_subscriptions')) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 25,
                'total' => 0,
                'meta_message' => '请先执行 php artisan migrate（创建 email_subscriptions 表）',
            ]);
        }

        $unsub = $request->query('unsubscribed');
        $q = EmailSubscription::query()->with('user:id,name,email');

        if ($unsub === '1') {
            $q->where('is_unsubscribed', true);
        } elseif ($unsub === '0') {
            $q->where('is_unsubscribed', false);
        }

        if ($search = trim((string) $request->query('q', ''))) {
            $q->where('email', 'like', '%'.$search.'%');
        }

        return response()->json(
            $q->orderByDesc('id')->paginate(25)->withQueryString()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('email_subscriptions', 'email')],
            'user_id' => ['nullable', 'exists:users,id'],
            'subscribed_to' => ['required', 'array', 'min:1'],
            'subscribed_to.*' => ['string', Rule::in(EmailSubscription::TOPICS)],
        ]);

        $sub = EmailSubscription::query()->create([
            'email' => $data['email'],
            'user_id' => $data['user_id'] ?? null,
            'subscribed_to' => $data['subscribed_to'],
            'is_unsubscribed' => false,
            'unsubscribed_at' => null,
        ]);

        return response()->json($sub->load('user:id,name,email'), 201);
    }

    public function update(Request $request, EmailSubscription $emailSubscription): JsonResponse
    {
        $data = $request->validate([
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('email_subscriptions', 'email')->ignore($emailSubscription->id)],
            'user_id' => ['nullable', 'exists:users,id'],
            'subscribed_to' => ['sometimes', 'array', 'min:1'],
            'subscribed_to.*' => ['string', Rule::in(EmailSubscription::TOPICS)],
            'is_unsubscribed' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('is_unsubscribed', $data)) {
            if ($data['is_unsubscribed']) {
                $emailSubscription->is_unsubscribed = true;
                $emailSubscription->unsubscribed_at = $emailSubscription->unsubscribed_at ?? now();
            } else {
                $emailSubscription->is_unsubscribed = false;
                $emailSubscription->unsubscribed_at = null;
            }
            unset($data['is_unsubscribed']);
        }

        $emailSubscription->fill($data);
        $emailSubscription->save();

        return response()->json($emailSubscription->fresh()->load('user:id,name,email'));
    }

    public function destroy(EmailSubscription $emailSubscription): JsonResponse
    {
        $emailSubscription->delete();

        return response()->json(['ok' => true]);
    }

    public function regenerateToken(EmailSubscription $emailSubscription): JsonResponse
    {
        $emailSubscription->unsubscribe_token = \Illuminate\Support\Str::random(48);
        $emailSubscription->save();

        return response()->json($emailSubscription->only(['id', 'unsubscribe_token']));
    }
}
