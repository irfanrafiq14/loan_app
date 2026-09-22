<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $customer = $request->user();
        $progress = $customer->loanProgressBar();

        $featuredLoans = Loan::query()
            ->featuredOffers()
            ->latest()
            ->get()
            ->unique(fn (Loan $loan) => $loan->title.'|'.$loan->amount)
            ->take(8)
            ->values();

        return view('customer.home', [
            'customer' => $customer,
            'featuredLoans' => $featuredLoans,
            'featuredTotal' => (float) $featuredLoans->sum(fn (Loan $loan) => (float) $loan->amount),
            'barAmount' => $progress['amount'],
            'barMin' => $progress['min'],
            'barMax' => $progress['max'],
            'barPercent' => $progress['percent'],
            'barLabel' => $progress['label'],
            'barCount' => $progress['count'],
            'hasOutstandingDues' => $customer->hasOutstandingDues(),
        ]);
    }
}
