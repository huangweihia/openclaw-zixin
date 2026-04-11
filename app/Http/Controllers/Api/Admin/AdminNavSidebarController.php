<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNavMenuBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNavSidebarController extends Controller
{
    public function __invoke(Request $request, AdminNavMenuBuilder $builder): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'menu' => $builder->sidebarForUser($user),
        ]);
    }
}
