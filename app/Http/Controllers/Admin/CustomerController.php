<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $customers = User::query()
            ->customers()
            ->search($request->string('q')->toString())
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = User::query()->create([
            ...$request->safe()->except('country_code'),
            'role' => UserRole::Customer,
            'available_credit' => $request->input('available_credit', 34500),
            'credit_min' => $request->input('credit_min', 2000),
            'credit_max' => $request->input('credit_max', 34500),
            'eligible_offer' => $request->input('eligible_offer', 50000),
        ]);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Customer created. Send them the app link to sign in with their phone number.');
    }

    public function show(User $customer): View
    {
        abort_unless($customer->isCustomer(), 404);

        $this->authorize('view', $customer);

        $customer->load(['loans.payments', 'payments.loan']);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(User $customer): View
    {
        abort_unless($customer->isCustomer(), 404);

        $this->authorize('update', $customer);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $customer->update($request->safe()->except('country_code'));

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function deactivate(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $this->authorize('update', $customer);

        $customer->update(['status' => UserStatus::Inactive]);

        return back()->with('success', 'Customer deactivated.');
    }
}
