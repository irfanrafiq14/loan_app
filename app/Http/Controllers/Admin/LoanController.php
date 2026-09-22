<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLoanRequest;
use App\Http\Requests\Admin\UpdateLoanRequest;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Loan::class);

        $loans = Loan::query()
            ->assigned()
            ->with('user')
            ->when($request->integer('customer'), fn ($query, $id) => $query->where('user_id', $id))
            ->status($request->string('status')->toString() ?: null)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $customers = User::query()->customers()->orderBy('name')->get();

        return view('admin.loans.index', compact('loans', 'customers'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Loan::class);

        $customers = User::query()->customers()->where('status', 'active')->orderBy('name')->get();
        $selected = $request->integer('customer');

        return view('admin.loans.create', compact('customers', 'selected'));
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        $loan = Loan::query()->create([
            ...$request->validated(),
            'status' => LoanStatus::Pending,
        ]);

        return redirect()
            ->route('admin.loans.show', $loan)
            ->with('success', 'Loan created and is now visible on the customer Orders page.');
    }

    public function show(Loan $loan): View
    {
        $this->authorize('view', $loan);

        $loan->load(['user', 'payments.paymentMethod']);

        return view('admin.loans.show', compact('loan'));
    }

    public function edit(Loan $loan): View
    {
        $this->authorize('update', $loan);

        $customers = User::query()->customers()->orderBy('name')->get();

        return view('admin.loans.edit', compact('loan', 'customers'));
    }

    public function update(UpdateLoanRequest $request, Loan $loan): RedirectResponse
    {
        $this->authorize('update', $loan);

        $loan->update($request->validated());

        return redirect()
            ->route('admin.loans.show', $loan)
            ->with('success', 'Loan updated successfully.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        $this->authorize('delete', $loan);

        $loan->delete();

        return redirect()
            ->route('admin.loans.index')
            ->with('success', 'Loan deleted.');
    }
}
