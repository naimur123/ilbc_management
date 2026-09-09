<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('role.manage'));
        $roles = Role::withCount('users')->orderBy('name')->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        Gate::denyIf(! auth()->user()->can('role.manage'));

        return view('admin.roles.form', [
            'role' => new Role(),
            'permissions' => Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:roles,name']);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function show(\Spatie\Permission\Models\Role $role)
    {
        return redirect()->route('roles.edit', $role);
    }

    public function edit(\Spatie\Permission\Models\Role $role)
    {
        Gate::denyIf(! auth()->user()->can('role.manage'));

        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0]),
        ]);
    }

    public function update(Request $request, \Spatie\Permission\Models\Role $role)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:roles,name,'.$role->id]);
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(\Spatie\Permission\Models\Role $role)
    {
        Gate::denyIf(! auth()->user()->can('role.manage'));

        if (in_array($role->name, ['Super Admin', 'Administrator'], true)) {
            return back()->with('error', 'This built-in role cannot be deleted.');
        }

        $role->delete();

        return back()->with('success', 'Role deleted.');
    }
}
