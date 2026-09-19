<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFeaturedLoanRequest;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeaturedLoanController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Loan::class);

        $loans = Loan::query()
            ->where('is_featured', true)
            ->latest()
            ->get();

        return view('admin.featured-loans.index', compact('loans'));
    }

    public function create(): View
    {
        $this->authorize('create', Loan::class);

        return view('admin.featured-loans.create');
    }

    public function store(StoreFeaturedLoanRequest $request): RedirectResponse
    {
        $this->authorize('create', Loan::class);

        $data = $request->safe()->except('featured_image');

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('featured-loans', 'public');
        }

        Loan::query()->create([
            ...$data,
            'user_id' => null,
            'status' => LoanStatus::Approved,
            'is_featured' => true,
        ]);

        return redirect()
            ->route('admin.featured-loans.index')
            ->with('success', 'Featured loan added. It is now visible to every customer.');
    }

    public function edit(Loan $loan): View
    {
        abort_unless($loan->is_featured, 404);
        $this->authorize('update', $loan);

        return view('admin.featured-loans.edit', compact('loan'));
    }

    public function update(StoreFeaturedLoanRequest $request, Loan $loan): RedirectResponse
    {
        abort_unless($loan->is_featured, 404);
        $this->authorize('update', $loan);

        $data = $request->safe()->except('featured_image');

        if ($request->hasFile('featured_image')) {
            if ($loan->featured_image) {
                Storage::disk('public')->delete($loan->featured_image);
            }

            $data['featured_image'] = $request->file('featured_image')->store('featured-loans', 'public');
        }

        $loan->update($data);

        return redirect()
            ->route('admin.featured-loans.index')
            ->with('success', 'Featured loan updated.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        abort_unless($loan->is_featured, 404);
        $this->authorize('delete', $loan);

        if ($loan->featured_image) {
            Storage::disk('public')->delete($loan->featured_image);
        }

        $loan->delete();

        return redirect()
            ->route('admin.featured-loans.index')
            ->with('success', 'Featured loan deleted.');
    }

    public function image(Loan $loan): StreamedResponse
    {
        abort_unless($loan->is_featured && $loan->hasFeaturedImage(), 404);
        abort_unless(Storage::disk('public')->exists($loan->featured_image), 404);

        return Storage::disk('public')->response($loan->featured_image);
    }
}
