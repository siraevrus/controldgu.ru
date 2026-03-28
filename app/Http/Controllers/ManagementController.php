<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ManagementController extends Controller
{
    public function index(): View
    {
        return view('management.index');
    }
}
