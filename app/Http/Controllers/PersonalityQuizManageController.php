<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalityQuizManageController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('personality-quiz.manage', [
            'adminToken' => (string) $request->query('token', ''),
        ]);
    }
}
