<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteTestimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteTestimonialController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = SiteTestimonial::query()->orderByDesc('sort_order')->orderByDesc('id')->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'avatar_initial' => ['required', 'string', 'max:8'],
            'gradient_from' => ['required', 'string', 'max:64'],
            'gradient_to' => ['required', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $row = SiteTestimonial::query()->create([
            'display_name' => $data['display_name'],
            'caption' => $data['caption'] ?? null,
            'body' => $data['body'],
            'rating' => $data['rating'],
            'avatar_initial' => $data['avatar_initial'],
            'gradient_from' => $data['gradient_from'],
            'gradient_to' => $data['gradient_to'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_published' => (bool) ($data['is_published'] ?? true),
        ]);

        return response()->json($row, 201);
    }

    public function update(Request $request, SiteTestimonial $siteTestimonial): JsonResponse
    {
        $data = $request->validate([
            'display_name' => ['sometimes', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'string', 'max:5000'],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'avatar_initial' => ['sometimes', 'string', 'max:8'],
            'gradient_from' => ['sometimes', 'string', 'max:64'],
            'gradient_to' => ['sometimes', 'string', 'max:64'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:999999'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $siteTestimonial->fill($data);
        $siteTestimonial->save();

        return response()->json($siteTestimonial->fresh());
    }

    public function destroy(SiteTestimonial $siteTestimonial): JsonResponse
    {
        $siteTestimonial->delete();

        return response()->json(['ok' => true]);
    }
}
