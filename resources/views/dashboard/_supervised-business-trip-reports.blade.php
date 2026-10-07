@if ($supervisedBusinessTripReports->isNotEmpty())
    <section class="mt-7 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
        <div><p class="text-sm font-semibold text-emerald-900">Laporan Perjalanan Dinas Bawahan</p><p class="mt-1 text-xs text-emerald-700">Laporan terbaru yang telah dikirim oleh bawahan langsung Anda.</p></div>
        <div class="mt-4 grid gap-3 lg:grid-cols-2">
            @foreach ($supervisedBusinessTripReports as $businessTrip)
                <a href="{{ route('business-trips.show', $businessTrip) }}" class="rounded-xl border border-emerald-100 bg-white p-4 transition hover:border-emerald-300 hover:shadow-sm"><div class="flex items-start justify-between gap-3"><div><p class="font-semibold text-slate-900">{{ $businessTrip->employee->display_name }}</p><p class="mt-1 text-xs text-slate-500">{{ $businessTrip->employee->department?->name ?? 'Unit belum ditentukan' }}</p></div><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Laporan Selesai</span></div><p class="mt-3 text-sm font-medium text-slate-700">{{ $businessTrip->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $businessTrip->destination }} · dilaporkan {{ $businessTrip->reported_at?->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }} WIB</p><p class="mt-3 text-xs font-semibold text-emerald-700">Baca laporan →</p></a>
            @endforeach
        </div>
    </section>
@endif
