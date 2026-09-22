<?php

namespace App\Http\Controllers\Customer;

use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tab = $request->string('tab', 'pending')->toString();
        $search = trim($request->string('q')->toString());

        $query = Loan::query()
            ->with(['payments' => fn ($payments) => $payments->latest()])
            ->forCustomer($request->user()->id)
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('reference_code', 'like', '%'.ltrim($search, '#').'%')
                        ->orWhere('id', $search);
                });
            });

        $query = $tab === 'completed'
            ? $query->where('status', LoanStatus::Completed)
            : $query->whereIn('status', [LoanStatus::Pending, LoanStatus::Approved]);

        $loans = $query->latest()->get();

        return view('customer.orders', [
            'loans' => $loans,
            'tab' => $tab === 'completed' ? 'completed' : 'pending',
            'search' => $search,
        ]);
    }
}
