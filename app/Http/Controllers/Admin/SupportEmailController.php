<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSupportEmailRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportEmailController extends Controller
{
    public function edit(): View
    {
        return view('admin.support-email', [
            'supportEmail' => Setting::getValue('support_email'),
        ]);
    }

    public function update(UpdateSupportEmailRequest $request): RedirectResponse
    {
        Setting::putValue('support_email', $request->string('support_email')->toString());

        return redirect()
            ->route('admin.support-email.edit')
            ->with('success', 'Support email updated. The customer Support button opens Gmail with this address.');
    }
}
