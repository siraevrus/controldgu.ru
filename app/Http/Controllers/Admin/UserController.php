<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInvitedUserRequest;
use App\Mail\NewUserCredentialsMail;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
        $plainPassword = $data['password'];
        unset($data['role'], $data['password'], $data['password_confirmation']);

        $data['email_verified_at'] = now();

        try {
            $user = DB::transaction(function () use ($data, $roleName, $plainPassword) {
                $data['password'] = $plainPassword;
                $created = User::query()->create($data);
                $created->syncRoles([$roleName]);

                Mail::to($created->email)->send(new NewUserCredentialsMail($created, $plainPassword));

                return $created;
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('settings.users.create')
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['email' => 'Не удалось создать пользователя или отправить письмо. Попробуйте снова или проверьте настройки почты.']);
        }

        Audit::record('user.invited', User::class, $user->id, [
            'email' => $user->email,
            'role' => $roleName,
        ]);

        return redirect()
            ->route('settings.users.index')
            ->with('status', 'Пользователь создан. Логин и пароль отправлены на email.');
    }
}
