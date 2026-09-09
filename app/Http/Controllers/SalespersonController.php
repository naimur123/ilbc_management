<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Salesperson;
use App\Models\User;
use Illuminate\Http\Request;

class SalespersonController extends Controller
{
    public function index()
    {
        return view('master.salespersons.index', ['items' => Salesperson::with('department')->orderBy('name')->paginate(20)]);
    }

    public function create()
    {
        return view('master.salespersons.form', [
            'salesperson' => new Salesperson(),
            'departments' => Department::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Salesperson::create($this->validated($request));

        return redirect()->route('salespersons.index')->with('success', 'Salesperson added.');
    }

    public function edit(Salesperson $salesperson)
    {
        return view('master.salespersons.form', [
            'salesperson' => $salesperson,
            'departments' => Department::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Salesperson $salesperson)
    {
        $salesperson->update($this->validated($request, $salesperson->id));

        return redirect()->route('salespersons.index')->with('success', 'Salesperson updated.');
    }

    public function destroy(Salesperson $salesperson)
    {
        $salesperson->delete();

        return back()->with('success', 'Salesperson removed.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:salespersons,code,'.$ignoreId,
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
