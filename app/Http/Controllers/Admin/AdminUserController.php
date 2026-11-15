<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => AdminUser::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new AdminUser]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:admin_users,email'],
            'password' => ['required', 'confirmed', Password::min(10)],
        ]);
        AdminUser::create($data);

        return redirect()->route('admin.users.index')->with('status', 'Admin added.');
    }

    public function edit(AdminUser $user)
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, AdminUser $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('admin_users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(10)],
        ]);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'Admin saved.');
    }

    public function destroy(Request $request, AdminUser $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }
        if (AdminUser::count() <= 1) {
            return back()->withErrors(['user' => 'You cannot delete the last admin.']);
        }
        $user->delete();

        return back()->with('status', 'Admin removed.');
    }
}
