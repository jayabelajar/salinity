@props(['id', 'label', 'icon', 'value', 'suffix'])

<div id="{{ $id }}" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex items-center justify-between text-xs font-bold text-slate-500">
        <span>{{ $label }}</span>
        <i data-lucide="{{ $icon }}" class="h-4 w-4 text-teal-600"></i>
    </div>
    <div class="mt-3 flex items-baseline gap-1">
        <span class="kpi-value text-2xl font-extrabold text-slate-950">{{ $value }}</span>
        <span class="text-xs font-semibold text-slate-500">{{ $suffix }}</span>
    </div>
</div>
