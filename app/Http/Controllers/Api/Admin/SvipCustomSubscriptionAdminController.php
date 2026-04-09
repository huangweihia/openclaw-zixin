<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SvipCustomSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SvipCustomSubscriptionAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(10, min($perPage, 100));
        $query = SvipCustomSubscription::query()
            ->with(['user:id,name,email,role'])
            ->orderByDesc('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('plan_name', 'like', '%'.$q.'%')
                    ->orWhere('description', 'like', '%'.$q.'%')
                    ->orWhereHas('user', function ($userQ) use ($q) {
                        $userQ->where('name', 'like', '%'.$q.'%')
                            ->orWhere('email', 'like', '%'.$q.'%');
                    });
            });
        }

        return response()->json($query->paginate($perPage)->withQueryString());
    }
}
