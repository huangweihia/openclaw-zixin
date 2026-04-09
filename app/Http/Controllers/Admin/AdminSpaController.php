<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminSpaController extends Controller
{
    public function index(): View
    {
        $adminDomain = config('admin.domain');
        $frontDomain = config('app.front_domain');
        $splitHosts = is_string($adminDomain) && $adminDomain !== ''
            && is_string($frontDomain) && $frontDomain !== '';

        $prefix = trim((string) config('admin.path_prefix', 'admin'), '/');
        $routerBase = $splitHosts ? '/' : '/'.$prefix.'/';

        return view('admin.spa', ['routerBase' => $routerBase]);
    }
}
