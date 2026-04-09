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
        $query = SvipCustomSubscription::query()
            ->with(['user:id,name,email,role'])
            ->orderByDesc('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json($query->paginate(30));
    }
}
