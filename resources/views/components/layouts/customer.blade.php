@props(['title' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $appName }}</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-customer font-sans antialiased"
      x-data="{
          toast: null,
          toastType: 'success',
          hasOutstandingDues: @js((bool) auth()->user()?->hasOutstandingDues()),
          showToast(message, type = 'success', duration = 4000) {
              this.toast = message;
              this.toastType = type;
              clearTimeout(this._toastTimer);
              this._toastTimer = setTimeout(() => this.toast = null, duration);
          },
          applyLoan() {
              if (this.hasOutstandingDues) {
                  this.showToast('Clear your previous dues before applying.', 'error');
                  return;
              }
              this.showToast('Processing...', 'success', 3000);
          },
          requestIncrease() {
              this.showToast('Contact your administrator to request an increase.', 'success');
          }
      }"
      x-init="persistCustomerAppName(@js($appName ?? '')); @if (session('success')) showToast(@js(session('success')), 'success') @endif @if (session('error')) showToast(@js(session('error')), 'error') @endif">
    <div x-show="toast" x-cloak x-transition
         class="fixed inset-x-0 top-4 z-50 mx-auto w-[min(92%,28rem)] rounded-2xl px-4 py-3 text-sm font-semibold shadow-lg"
         :class="toastType === 'error' ? 'bg-red-600 text-white' : 'bg-brand text-white'"
         x-text="toast"></div>

    <div class="lg:flex lg:min-h-screen">
        <x-customer-nav variant="sidebar" />

        <div class="min-h-screen flex-1 bg-page">
            <div class="mx-auto w-full max-w-md pb-24 lg:max-w-5xl lg:px-8 lg:pb-10 lg:pt-8">
                {{ $slot }}
            </div>
            <x-customer-nav variant="bottom" />
        </div>
    </div>
</body>
</html>
