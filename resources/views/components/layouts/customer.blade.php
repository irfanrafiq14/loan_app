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
<body class="bg-page font-sans antialiased"
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
          }
      }"
      x-init="@if (session('success')) showToast(@js(session('success')), 'success') @endif @if (session('error')) showToast(@js(session('error')), 'error') @endif">
    <div class="mx-auto min-h-screen w-full max-w-md bg-page pb-24 shadow-sm sm:max-w-lg lg:max-w-xl">
        <div x-show="toast" x-cloak x-transition
             class="fixed inset-x-0 top-4 z-50 mx-auto w-[min(92%,28rem)] rounded-2xl px-4 py-3 text-sm font-semibold shadow-lg"
             :class="toastType === 'error' ? 'bg-red-600 text-white' : 'bg-brand text-white'"
             x-text="toast"></div>

        {{ $slot }}

        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto grid max-w-md grid-cols-3 sm:max-w-lg lg:max-w-xl">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-3 text-xs font-semibold {{ request()->routeIs('home') ? 'text-brand' : 'text-slate-400' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5 12 4l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
                    Home
                </a>
                <a href="{{ route('orders') }}" class="flex flex-col items-center gap-1 py-3 text-xs font-semibold {{ request()->routeIs('orders') || request()->routeIs('loans.pay') ? 'text-brand' : 'text-slate-400' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h10M7 12h10M7 17h6"/><rect x="4" y="4" width="16" height="16" rx="3" stroke-width="1.8"/></svg>
                    Orders
                </a>
                <a href="{{ route('profile') }}" class="flex flex-col items-center gap-1 py-3 text-xs font-semibold {{ request()->routeIs('profile') ? 'text-brand' : 'text-slate-400' }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm8 8a8 8 0 1 0-16 0"/></svg>
                    Profile
                </a>
            </div>
        </nav>
    </div>
</body>
</html>
