<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInvitedUserRequest;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->with('roles')->orderBy('name')->paginate(20);

        return view('settings.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('settings.users.create');
    }

    public function store(StoreInvitedUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $roleName = $data['role'];
        unset($data['role']);

        $data['password'] = Hash::make(Str::random(64));
        $data['email_verified_at'] = null;

        $user = User::query()->create($data);
        $user->syncRoles([$roleName]);

        Audit::record('user.invited', User::class, $user->id, [
            'email' => $user->email,
            'role' => $roleName,
        ]);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()
                ->route('settings.users.index')
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('settings.users.index')
            ->with('status', 'Пользователь создан. На email отправлена ссылка для установки пароля.');
    }
}
