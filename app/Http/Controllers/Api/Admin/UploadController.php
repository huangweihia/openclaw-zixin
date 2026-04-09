<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * 上传单张图片至 public 磁盘，返回可访问 URL（供广告位、素材等填写 URL 字段）。
     */
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120', 'mimes:jpeg,jpg,png,gif,webp'],
        ]);

        $path = $request->file('image')->store('uploads/admin', 'public');
        $url = asset('storage/'.$path);

        return response()->json(['url' => $url]);
    }
}
