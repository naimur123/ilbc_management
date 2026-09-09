<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('user.manage'));

        $users = User::with('roles', 'department')
            ->when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%"))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        Gate::denyIf(! auth()->user()->can('user.manage'));

        return view('admin.users.form', [
            'user' => new User(),
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function show(User $user)
    {
        return redirect()->route('users.edit', $user);
    }

    public function edit(User $user)
    {
        Gate::denyIf(! auth()->user()->can('user.manage'));
        $user->load('roles');

        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user->id);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        Gate::denyIf(! auth()->user()->can('user.manage'));
        $user->update(['is_active' => false]);
        $user->delete();

        return back()->with('success', 'User deactivated.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'employee_code' => 'nullable|string|max:30',
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,'.$ignoreId,
            'phone' => 'nullable|string|max:30',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'password' => ($ignoreId ? 'nullable' : 'required').'|string|min:8|confirmed',
        ];

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
