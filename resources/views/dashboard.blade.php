<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SalinityGuard Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } } };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: Inter, sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white md:flex md:flex-col">
            <div class="flex h-16 items-center gap-3 border-b border-slate-100 px-6">
                <div class="grid h-9 w-9 place-items-center rounded-lg bg-teal-600 text-white shadow-md shadow-teal-600/20">
                    <i data-lucide="droplets" class="h-5 w-5"></i>
                </div>
                <div>
                    <h1 class="font-extrabold leading-none text-slate-900">SalinityGuard</h1>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Fuzzy & Offline IoT</p>
                </div>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 custom-scrollbar">
                @foreach (['dashboard' => 'layout-dashboard', 'monitoring' => 'activity', 'devices' => 'cpu', 'readings' => 'table-2', 'alerts' => 'bell', 'synchronization' => 'wifi-off', 'analytics' => 'bar-chart-3', 'reports' => 'file-text', 'settings' => 'settings'] as $page => $icon)
                    <a href="#{{ $page }}" data-tab="{{ $page }}" class="nav-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-950">
                        <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
                        {{ ucfirst($page) }}
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-slate-100 bg-slate-50/60 p-4 text-xs">
                <div class="rounded-lg border border-slate-200 bg-white p-3">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-500">Backend</span>
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700">Laravel API</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="font-semibold text-slate-500">Bot</span>
                        <span class="font-bold text-slate-700">Telegram webhook</span>
                    </div>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
                <div>
                    <h2 id="page-title" class="text-lg font-extrabold text-slate-900">Dashboard</h2>
                    <p class="text-xs text-slate-500">Data tersinkron dari database Laravel</p>
                </div>
                <div class="flex items-center gap-2">
                    <button id="simulate-normal" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Sim NORMAL</button>
                    <button id="simulate-danger" class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-white hover:bg-amber-600">Sim Spike</button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50" title="Logout">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            </header>

            <main class="space-y-6 p-4 sm:p-6">
                <section id="dashboard" class="tab-content space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                        <x-kpi id="kpi-ec" label="EC Terkini" icon="zap" value="-" suffix="mS/cm" />
                        <x-kpi id="kpi-temp" label="Suhu Air" icon="thermometer" value="-" suffix="C" />
                        <x-kpi id="kpi-risk" label="Fuzzy Score" icon="shield-alert" value="-" suffix="" />
                        <x-kpi id="kpi-devices" label="Perangkat Online" icon="cpu" value="-" suffix="" />
                        <x-kpi id="kpi-alerts" label="Alert Aktif" icon="bell" value="-" suffix="" />
                    </div>
                    <div class="grid gap-6 xl:grid-cols-[1.6fr_.9fr]">
                        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <h3 class="font-extrabold text-slate-900">Real-time Telemetry Trends</h3>
                                    <p class="text-xs text-slate-500">EC, suhu air, dan skor fuzzy dari database</p>
                                </div>
                                <span id="latest-risk" class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">-</span>
                            </div>
                            <div class="h-80"><canvas id="trend-chart"></canvas></div>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="font-extrabold text-slate-900">Live Data Stream</h3>
                            <div id="stream-list" class="mt-4 max-h-80 space-y-2 overflow-y-auto pr-1 custom-scrollbar"></div>
                        </div>
                    </div>
                </section>

                <section id="devices" class="tab-content hidden">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-4 font-extrabold text-slate-900">Device Management</h3>
                        <div id="device-grid" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"></div>
                    </div>
                </section>

                <section id="monitoring" class="tab-content hidden">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-4 font-extrabold text-slate-900">Monitoring & Fuzzy Mamdani</h3>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-lg bg-slate-50 p-4"><p class="text-xs font-bold text-slate-500">EC Corrected</p><p id="monitor-ec" class="mt-2 text-3xl font-extrabold">-</p></div>
                            <div class="rounded-lg bg-slate-50 p-4"><p class="text-xs font-bold text-slate-500">EC Rate</p><p id="monitor-rate" class="mt-2 text-3xl font-extrabold">-</p></div>
                            <div class="rounded-lg bg-slate-50 p-4"><p class="text-xs font-bold text-slate-500">Risk Category</p><p id="monitor-risk" class="mt-2 text-3xl font-extrabold">-</p></div>
                        </div>
                    </div>
                </section>

                <section id="readings" class="tab-content hidden">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-4 font-extrabold text-slate-900">Sensor History</h3>
                        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-2">Time</th><th>Device</th><th>EC</th><th>Temp</th><th>Risk</th></tr></thead><tbody id="reading-table"></tbody></table></div>
                    </div>
                </section>

                <section id="alerts" class="tab-content hidden">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-4 font-extrabold text-slate-900">Early Warning</h3>
                        <div id="alert-list" class="space-y-3"></div>
                    </div>
                </section>

                <section id="synchronization" class="tab-content hidden">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-4 font-extrabold text-slate-900">Offline-First Synchronization</h3>
                        <p class="text-sm text-slate-500">Endpoint ingest idempoten: <code class="rounded bg-slate-100 px-2 py-1">POST /api/iot/readings</code>. Duplikasi <code class="rounded bg-slate-100 px-2 py-1">device_id + event_id</code> tidak membuat reading baru.</p>
                    </div>
                </section>

                <section id="analytics" class="tab-content hidden"><div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-extrabold">Analytics</h3><p class="mt-2 text-sm text-slate-500">Grafik utama tersedia di dashboard dan endpoint /api/analytics.</p></div></section>
                <section id="reports" class="tab-content hidden"><div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-extrabold">Reports</h3><p class="mt-2 text-sm text-slate-500">Export CSV/PDF dapat disambungkan dari /api/reports/export.</p></div></section>
                <section id="settings" class="tab-content hidden"><div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><h3 class="font-extrabold">Telegram Settings</h3><p class="mt-2 text-sm text-slate-500">Webhook: POST /api/telegram/webhook. Pairing code: POST /api/telegram/pairing.</p></div></section>
            </main>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let chart;

        const riskClass = (risk) => ({
            NORMAL: 'bg-emerald-50 text-emerald-700',
            WASPADA: 'bg-amber-50 text-amber-700',
            BAHAYA: 'bg-rose-50 text-rose-700'
        }[risk] || 'bg-slate-100 text-slate-600');

        async function api(path, options = {}) {
            const response = await fetch(path, {
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, ...(options.headers || {}) },
                ...options
            });
            if (!response.ok) throw new Error(await response.text());
            return response.json();
        }

        async function refresh() {
            const data = await api('/api/dashboard/summary');
            const latest = data.latest || {};
            document.querySelector('#kpi-ec .kpi-value').textContent = latest.ec_corrected ?? '-';
            document.querySelector('#kpi-temp .kpi-value').textContent = latest.temperature ?? '-';
            document.querySelector('#kpi-risk .kpi-value').textContent = latest.fuzzy_score ?? '-';
            document.querySelector('#kpi-devices .kpi-value').textContent = `${data.stats.online_devices}/${data.stats.total_devices}`;
            document.querySelector('#kpi-alerts .kpi-value').textContent = data.stats.active_alerts;
            document.getElementById('latest-risk').textContent = latest.risk_level || '-';
            document.getElementById('latest-risk').className = `rounded-full px-2 py-1 text-xs font-bold ${riskClass(latest.risk_level)}`;
            document.getElementById('monitor-ec').textContent = latest.ec_corrected ? `${latest.ec_corrected} mS/cm` : '-';
            document.getElementById('monitor-rate').textContent = latest.ec_rate ? `${latest.ec_rate} mS/cm/min` : '-';
            document.getElementById('monitor-risk').textContent = latest.risk_level || '-';
            renderChart(data.trend || []);
            renderStream(data.trend || []);
            renderDevices(data.devices || []);
            renderAlerts(data.alerts || []);
            renderReadings(data.trend || []);
        }

        function renderChart(trend) {
            const labels = trend.map(row => new Date(row.measured_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
            const datasets = [
                { label: 'EC', data: trend.map(row => row.ec_corrected), borderColor: '#0d9488', backgroundColor: 'rgba(13,148,136,.08)', fill: true, tension: .35 },
                { label: 'Temperature', data: trend.map(row => row.temperature), borderColor: '#2563eb', tension: .35 },
                { label: 'Fuzzy', data: trend.map(row => row.fuzzy_score), borderColor: '#f59e0b', tension: .35 }
            ];
            if (!chart) {
                chart = new Chart(document.getElementById('trend-chart'), { type: 'line', data: { labels, datasets }, options: { responsive: true, maintainAspectRatio: false } });
            } else {
                chart.data = { labels, datasets };
                chart.update('none');
            }
        }

        function renderStream(trend) {
            document.getElementById('stream-list').innerHTML = trend.slice(-8).reverse().map(row => `
                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 p-3 text-xs">
                    <div><div class="font-bold text-slate-900">EC ${row.ec_corrected} mS/cm | ${row.temperature} C</div><div class="text-slate-400">${new Date(row.measured_at).toLocaleString()}</div></div>
                    <span class="rounded-full px-2 py-1 font-bold ${riskClass(row.risk_level)}">${row.risk_level}</span>
                </div>`).join('');
        }

        function renderDevices(devices) {
            document.getElementById('device-grid').innerHTML = devices.map(device => `
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="flex items-center justify-between"><h4 class="font-extrabold">${device.name}</h4><span class="rounded-full px-2 py-1 text-xs font-bold ${device.status === 'online' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}">${device.status}</span></div>
                    <p class="mt-2 text-sm text-slate-500">${device.device_uid} - ${device.location || '-'}</p>
                    <p class="mt-3 text-xs text-slate-400">Readings: ${device.readings_count} | Alerts: ${device.alerts_count}</p>
                </div>`).join('');
        }

        function renderAlerts(alerts) {
            document.getElementById('alert-list').innerHTML = alerts.length ? alerts.map(alert => `
                <div class="flex flex-col justify-between gap-3 rounded-lg border border-slate-200 p-4 md:flex-row md:items-center">
                    <div><span class="rounded-full px-2 py-1 text-xs font-bold ${riskClass(alert.risk_level)}">${alert.risk_level}</span><h4 class="mt-2 font-extrabold">${alert.device?.name || '-'}</h4><p class="text-sm text-slate-500">${new Date(alert.occurred_at).toLocaleString()} - ${alert.status}</p></div>
                    <button onclick="ack(${alert.id})" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold hover:bg-slate-50">Acknowledge</button>
                </div>`).join('') : '<p class="text-sm text-slate-500">Tidak ada alert.</p>';
        }

        function renderReadings(trend) {
            document.getElementById('reading-table').innerHTML = trend.slice().reverse().map(row => `
                <tr class="border-t border-slate-100"><td class="py-3">${new Date(row.measured_at).toLocaleString()}</td><td>Latest stream</td><td>${row.ec_corrected}</td><td>${row.temperature}</td><td><span class="rounded-full px-2 py-1 text-xs font-bold ${riskClass(row.risk_level)}">${row.risk_level}</span></td></tr>`).join('');
        }

        async function ack(id) {
            await api(`/api/alerts/${id}/acknowledge`, { method: 'PATCH', body: '{}' });
            refresh();
        }

        async function simulate(risk) {
            const ec = risk === 'BAHAYA' ? 3.8 : 1.7;
            await api('/api/iot/readings', {
                method: 'POST',
                body: JSON.stringify({ event_id: `web-${Date.now()}`, device_id: 'esp32-001', measured_at: new Date().toISOString(), ec_raw: ec, ec_corrected: ec, temperature: 28.2, ec_rate: .08, fuzzy_score: risk === 'BAHAYA' ? 88 : 24, risk_level: risk, firmware_version: 'web-sim' })
            });
            refresh();
        }

        document.querySelectorAll('.nav-link').forEach(link => link.addEventListener('click', (event) => {
            event.preventDefault();
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('bg-slate-100', 'text-slate-950'));
            document.getElementById(link.dataset.tab).classList.remove('hidden');
            link.classList.add('bg-slate-100', 'text-slate-950');
            document.getElementById('page-title').textContent = link.dataset.tab.charAt(0).toUpperCase() + link.dataset.tab.slice(1);
        }));

        document.querySelector('[data-tab="dashboard"]').classList.add('bg-slate-100', 'text-slate-950');
        document.getElementById('simulate-normal').addEventListener('click', () => simulate('NORMAL'));
        document.getElementById('simulate-danger').addEventListener('click', () => simulate('BAHAYA'));
        lucide.createIcons();
        refresh();
        setInterval(refresh, 10000);
    </script>
</body>
</html>
