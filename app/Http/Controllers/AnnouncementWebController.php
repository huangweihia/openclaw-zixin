<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementWebController extends Controller
{
    public function show(Request $request, Announcement $announcement): View
    {
        if (! $announcement->is_published) {
            abort(404);
        }
        if ($announcement->expires_at && $announcement->expires_at->isPast()) {
            abort(404);
        }

        return view('announcements.show', [
            'announcement' => $announcement->load('creator:id,name'),
        ]);
    }
}
