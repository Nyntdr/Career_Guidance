<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $roleFilter = $request->get('role');

        $users = User::query()
            ->when($search, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('name', 'like', "%{$v}%")
                    ->orWhere('email', 'like', "%{$v}%");
            }))
            ->when($roleFilter, fn ($q, $v) => $q->where('role', $v))
            ->orderBy('created_at', 'desc')
            ->paginate(Settings::get('admin_page_size', 20))
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if ($user->id === auth()->id() && isset($data['role']) && $data['role'] !== $user->role->value) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot change your own role.');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete yourself.');
        }

        if ($user->role->value === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete the last admin account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
