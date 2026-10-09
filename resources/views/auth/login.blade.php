<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SalinityGuard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } } };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="min-h-full font-sans text-slate-800 bg-slate-50">
    <main class="min-h-screen grid lg:grid-cols-[1.05fr_.95fr]">
        <section class="hidden lg:flex relative overflow-hidden bg-slate-950 text-white">
            <img class="absolute inset-0 h-full w-full object-cover opacity-45" src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1600&q=80" alt="Irigasi pertanian">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-950/80 to-teal-900/60"></div>
            <div class="relative z-10 flex min-h-full flex-col justify-between p-12">
                <div class="flex items-center gap-3">
                    <div class="grid h-11 w-11 place-items-center rounded-lg bg-teal-500 shadow-lg shadow-teal-500/30">
                        <i data-lucide="droplets" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <div class="text-lg font-extrabold">SalinityGuard</div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-teal-200">Fuzzy IoT Early Warning</div>
                    </div>
                </div>
                <div class="max-w-xl">
                    <p class="mb-4 inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-teal-100 ring-1 ring-white/15">Wokwi + Telegram ready</p>
                    <h1 class="text-5xl font-extrabold leading-tight">Monitoring salinitas irigasi dengan data real-time.</h1>
                    <p class="mt-5 text-base leading-7 text-slate-200">Dashboard, ingest endpoint, alert fuzzy Mamdani, sinkronisasi offline-first, dan command bot Telegram dalam satu backend Laravel.</p>
                </div>
                <div class="grid grid-cols-3 gap-3 text-sm">
                    <div class="rounded-lg bg-white/10 p-4 ring-1 ring-white/10">
                        <div class="text-2xl font-bold">0-100</div>
                        <div class="text-slate-300">Fuzzy score</div>
                    </div>
                    <div class="rounded-lg bg-white/10 p-4 ring-1 ring-white/10">
                        <div class="text-2xl font-bold">MQTT</div>
                        <div class="text-slate-300">Payload format</div>
                    </div>
                    <div class="rounded-lg bg-white/10 p-4 ring-1 ring-white/10">
                        <div class="text-2xl font-bold">Bot</div>
                        <div class="text-slate-300">Webhook API</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center px-5 py-10">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <div class="grid h-10 w-10 place-items-center rounded-lg bg-teal-600 text-white">
                        <i data-lucide="droplets" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <div class="font-extrabold text-slate-900">SalinityGuard</div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Fuzzy IoT</div>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-6">
                        <h2 class="text-2xl font-extrabold text-slate-950">Masuk Dashboard</h2>
                        <p class="mt-1 text-sm text-slate-500">Gunakan akun demo: admin@salinityguard.local / password</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                        @csrf
                        <label class="block">
                            <span class="mb-1 block text-sm font-semibold text-slate-700">Email</span>
                            <input name="email" type="email" value="{{ old('email', 'admin@salinityguard.local') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-sm font-semibold text-slate-700">Password</span>
                            <input name="password" type="password" value="password" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input name="remember" type="checkbox" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            Ingat sesi
                        </label>
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-teal-700">
                            <i data-lucide="log-in" class="h-4 w-4"></i>
                            Login
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
