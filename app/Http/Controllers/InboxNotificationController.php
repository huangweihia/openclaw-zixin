<?php

namespace App\Http\Controllers;

use App\Models\InboxNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InboxNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $q = InboxNotification::query()->where('user_id', $request->user()->id);

        if ($request->query('unread') === '1') {
            $q->where('is_read', false);
        }

        if ($request->filled('q')) {
            $kw = '%'.addcslashes($request->string('q')->trim(), '%_\\').'%';
            $q->where(function ($sub) use ($kw) {
                $sub->where('title', 'like', $kw)->orWhere('content', 'like', $kw);
            });
        }

        $items = $q->latest()->paginate(20)->withQueryString();

        return view('notifications.index', ['items' => $items]);
    }

    public function open(Request $request, InboxNotification $inboxNotification): RedirectResponse
    {
        $this->ensureOwner($request, $inboxNotification);
        $inboxNotification->markAsRead();

        if ($inboxNotification->action_url) {
            return redirect()->to($inboxNotification->action_url);
        }

        return redirect()->route('notifications.index');
    }

    public function read(Request $request, InboxNotification $inboxNotification): RedirectResponse
    {
        $this->ensureOwner($request, $inboxNotification);
        $inboxNotification->markAsRead();

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        InboxNotification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back();
    }

    public function destroy(Request $request, InboxNotification $inboxNotification): RedirectResponse
    {
        $this->ensureOwner($request, $inboxNotification);
        $inboxNotification->delete();

        return back();
    }

    private function ensureOwner(Request $request, InboxNotification $inboxNotification): void
    {
        abort_unless((int) $inboxNotification->user_id === (int) $request->user()->id, 403);
    }
}
