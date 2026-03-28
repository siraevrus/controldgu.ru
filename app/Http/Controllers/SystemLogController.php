<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    public function index(): View
    {
        $logs = SystemLog::query()
            ->latest('created_at')
            ->paginate(40);

        return view('settings.logs.index', compact('logs'));
    }
}
