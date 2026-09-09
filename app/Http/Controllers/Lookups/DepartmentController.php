<?php

namespace App\Http\Controllers\Lookups;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('master.departments', ['items' => Department::orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'code' => 'required|string|max:30|unique:departments,code']);
        Department::create($data + ['is_active' => true]);
        return back()->with('success', 'Department added.');
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:departments,code,'.$department->id,
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $department->update($data);
        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back()->with('success', 'Department removed.');
    }
}
