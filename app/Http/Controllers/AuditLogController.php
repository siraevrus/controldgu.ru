<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->latest('created_at')
            ->paginate(40);

        return view('settings.audit.index', compact('logs'));
    }
}
