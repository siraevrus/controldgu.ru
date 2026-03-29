<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->with('dgu')
            ->latest()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
