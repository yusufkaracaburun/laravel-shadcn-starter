<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class WelcomeController extends Controller
{
    public function home(): View
    {
        return view('app');
    }
}
