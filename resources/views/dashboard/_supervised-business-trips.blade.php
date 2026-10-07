@if ($supervisedBusinessTrips->isNotEmpty())
    <section class="mt-7 rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm">
        <div><p class="text-sm font-semibold text-violet-900">Informasi Perjalanan Dinas Bawahan</p><p class="mt-1 text-xs text-violet-700">Pemberitahuan saja, tidak memerlukan persetujuan Anda.</p></div>
        <div class="mt-4 grid gap-3 lg:grid-cols-2">
            @foreach ($supervisedBusinessTrips as $businessTrip)
                <article class="rounded-xl border border-violet-100 bg-white p-4"><div class="flex items-start justify-between gap-3"><div><p class="font-semibold text-slate-900">{{ $businessTrip->employee->display_name }}</p><p class="mt-1 text-xs text-slate-500">{{ $businessTrip->employee->department?->name ?? 'Unit belum ditentukan' }}</p></div><span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">{{ $businessTrip->statusLabel() }}</span></div><p class="mt-3 text-sm font-medium text-slate-700">{{ $businessTrip->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $businessTrip->destination }} · {{ $businessTrip->start_date->format('d/m/Y') }}–{{ $businessTrip->end_date->format('d/m/Y') }}</p></article>
            @endforeach
        </div>
    </section>
@endif
