<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\UserAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectEngagementController extends Controller
{
    public function toggleFavorite(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();

        return DB::transaction(function () use ($user, $project) {
            $row = UserAction::query()
                ->where('user_id', $user->id)
                ->where('actionable_type', $project->getMorphClass())
                ->where('actionable_id', $project->id)
                ->where('type', 'favorite')
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->delete();
                $message = '已取消收藏';
            } else {
                UserAction::query()->create([
                    'user_id' => $user->id,
                    'actionable_type' => $project->getMorphClass(),
                    'actionable_id' => $project->id,
                    'type' => 'favorite',
                ]);
                $message = '已加入收藏';
            }

            return back()->with('success', $message);
        });
    }
}
