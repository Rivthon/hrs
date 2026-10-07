@if ($employeesOnLeaveToday->isNotEmpty())
    <div id="leave-today-popup" role="dialog" aria-modal="true" aria-labelledby="leave-today-title" class="fixed inset-0 z-50 grid place-items-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <section class="max-h-[85vh] w-full max-w-xl overflow-hidden rounded-3xl bg-white shadow-2xl">
            <header class="flex items-start justify-between gap-4 border-b border-slate-100 bg-amber-50 px-6 py-5">
                <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Informasi hari ini</p><h2 id="leave-today-title" class="mt-1 text-xl font-semibold text-slate-900">Tendik atau Dosen yang sedang Cuti Hari Ini</h2><p class="mt-1 text-sm text-slate-600">{{ today()->locale('id')->translatedFormat('l, d F Y') }}</p></div>
                <button type="button" onclick="document.getElementById('leave-today-popup').remove()" aria-label="Tutup informasi" class="grid size-10 shrink-0 place-items-center rounded-full bg-white text-xl text-slate-500 shadow-sm transition hover:bg-slate-100 hover:text-slate-900">&times;</button>
            </header>
            <div class="max-h-[55vh] overflow-y-auto p-4 sm:p-6">
                <div class="flex flex-col gap-3">
                    @foreach ($employeesOnLeaveToday as $leaveRequest)
                        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 p-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-sm font-bold text-brand-700">{{ strtoupper(substr($leaveRequest->employee->full_name, 0, 2)) }}</span>
                            <div class="min-w-0 flex-1"><p class="font-semibold text-slate-900">{{ $leaveRequest->employee->display_name }}</p><p class="mt-1 text-sm text-slate-500">{{ $leaveRequest->employee->user?->role === 'dosen' ? 'Dosen' : 'Tendik' }} · {{ $leaveRequest->employee->department?->name ?? 'Unit belum ditentukan' }}</p><p class="mt-1 text-xs font-medium text-amber-700">{{ $leaveRequest->leave_type->label() }} · sampai {{ $leaveRequest->end_date->locale('id')->translatedFormat('d F Y') }}@if ($leaveRequest->leave_type->isPartialDay()) pukul {{ substr($leaveRequest->end_time, 0, 5) }}@endif</p></div>
                        </article>
                    @endforeach
                </div>
            </div>
            <footer class="border-t border-slate-100 px-6 py-4"><button type="button" onclick="document.getElementById('leave-today-popup').remove()" class="w-full rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Saya Mengerti</button></footer>
        </section>
    </div>
@endif
