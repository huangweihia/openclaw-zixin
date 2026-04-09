<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ViewHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ViewHistoryAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ViewHistory::query()
            ->with(['user:id,name,email'])
            ->orderByDesc('viewed_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        return response()->json($query->paginate(40));
    }
}
