<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PointLedgerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('points')) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 40,
                'total' => 0,
            ]);
        }

        $q = Point::query()
            ->with(['user:id,name,email'])
            ->orderByDesc('id');

        $uid = $request->query('user_id');
        if ($uid !== null && $uid !== '' && ctype_digit((string) $uid)) {
            $q->where('user_id', (int) $uid);
        }

        return response()->json($q->paginate(40)->withQueryString());
    }
}
