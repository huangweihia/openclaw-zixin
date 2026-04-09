<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 前台富文本（投稿等）图片上传，返回 URL 供 TinyMCE 使用。
 */
class UserRichUploadController extends Controller
{
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120', 'mimes:jpeg,jpg,png,gif,webp'],
        ]);

        $path = $request->file('image')->store('uploads/user-content', 'public');
        $url = asset('storage/'.$path);

        return response()->json(['location' => $url]);
    }
}
