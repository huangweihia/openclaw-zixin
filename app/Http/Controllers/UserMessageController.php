<?php

namespace App\Http\Controllers;

use App\Models\InboxNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserMessageController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        if ((int) $user->id === (int) $request->user()->id) {
            return response()->json(['error' => '不能给自己留言'], 422);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:20000'],
        ]);

        $from = $request->user();
        InboxNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'user_message',
            'title' => '来自「'.$from->name.'」的留言',
            'content' => $data['body'],
            'action_url' => null,
        ]);

        return response()->json(['ok' => true, 'message' => '留言已发送']);
    }
}
