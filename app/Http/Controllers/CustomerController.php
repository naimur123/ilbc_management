<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Department;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::with('department')
            ->when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%")->orWhere('mobile', 'like', "%{$request->q}%"))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('master.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('master.customers.form', [
            'customer' => new Customer(),
            'departments' => Department::orderBy('name')->get(),
            'paymentTerms' => PaymentTerm::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['customer_code'] = $data['customer_code'] ?: $this->nextCode();
        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }

    public function edit(Customer $customer)
    {
        return view('master.customers.form', [
            'customer' => $customer,
            'departments' => Department::orderBy('name')->get(),
            'paymentTerms' => PaymentTerm::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer->id));

        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'Customer removed.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'customer_code' => 'nullable|string|max:30|unique:customers,customer_code,'.$ignoreId,
            'name' => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'source' => 'nullable|string|max:100',
            'customer_type' => 'nullable|string|max:50',
            'payment_terms_id' => 'nullable|exists:payment_terms,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,BLOCKED',
            'remarks' => 'nullable|string',
        ]);
    }

    private function nextCode(): string
    {
        $last = Customer::orderByDesc('id')->first();
        $next = $last ? ((int) preg_replace('/\D/', '', $last->customer_code) + 1) : 1;

        return 'CUS-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
