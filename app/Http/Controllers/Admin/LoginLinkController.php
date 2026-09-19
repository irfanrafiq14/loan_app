<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLoginLinkRequest;
use App\Models\LoginLink;
use App\Models\User;
use App\Services\LoginLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginLinkController extends Controller
{
    public function __construct(private readonly LoginLinkService $loginLinks)
    {
    }

    public function index(Request $request): View
    {
        $links = LoginLink::query()
            ->with('user')
            ->when($request->integer('customer'), fn ($query, $id) => $query->where('user_id', $id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $customers = User::query()->customers()->orderBy('name')->get();

        return view('admin.login-links.index', compact('links', 'customers'));
    }

    public function create(Request $request): View
    {
        $customers = User::query()->customers()->where('status', 'active')->orderBy('name')->get();
        $selected = $request->integer('customer');

        return view('admin.login-links.create', compact('customers', 'selected'));
    }

    public function store(StoreLoginLinkRequest $request): RedirectResponse
    {
        $customer = User::query()->customers()->findOrFail($request->integer('user_id'));
        $generated = $this->loginLinks->create($customer, $request->string('app_name')->toString());

        return redirect()
            ->route('admin.login-links.index')
            ->with('success', 'Login link generated. Copy it now — the raw token is shown only once.')
            ->with('generated_url', $generated['url']);
    }

    public function revoke(LoginLink $loginLink): RedirectResponse
    {
        $this->loginLinks->revoke($loginLink);

        return back()->with('success', 'Login link revoked.');
    }
}
