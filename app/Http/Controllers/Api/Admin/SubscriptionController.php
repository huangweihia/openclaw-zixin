<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = DB::table('subscriptions as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->select([
                's.id',
                's.user_id',
                's.plan',
                's.amount',
                's.status',
                's.started_at',
                's.expires_at',
                's.payment_method',
                's.created_at',
                'u.name as user_name',
                'u.email as user_email',
            ])
            ->orderByDesc('s.id')
            ->paginate(25);

        return response()->json($rows);
    }
}
