@extends('layouts.app')
@section('title', 'Input Payroll | HRS Kampus')
@section('content')
    <a href="{{ route('payroll-periods.show', $payroll->period) }}" class="text-sm font-semibold text-brand-700">← Payroll {{ $payroll->period->period_date->format('m/Y') }}</a><h1 class="mt-3 text-3xl font-semibold">Input Komponen Payroll</h1><p class="mt-2 text-sm text-slate-500">{{ $payroll->employee->display_name }} · {{ strtoupper($payroll->employee->user?->role ?? 'staff') }}</p>
    <form method="POST" action="{{ route('payrolls.update', $payroll) }}" class="mt-6 flex flex-col gap-6">@csrf @method('PUT')
        @php
            $money = 'mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100';
            $isLecturer = $payroll->employee->user?->role === 'dosen';
            $earnings = [
                'position_allowance' => 'Tunjangan Jabatan', 'functional_allowance' => 'Tunjangan Fungsional',
                'pkk_supervision_honor' => 'Supervisi PKK',
                'practical_exam_honor' => 'Ujian Praktik / Uprak', 'duty_honor' => 'Piket / Dinas',
            ];
            $roleEarnings = $isLecturer
                ? [
                    'teaching_honor' => 'Honor Mengajar',
                    'final_seminar_honor' => 'Honor Seminar Akhir',
                    'thesis_defense_honor' => 'Honor Hasil & Sidang Skripsi',
                    'thesis_supervisor_honor' => 'Honor Pembimbing Skripsi',
                ]
                : ['proctoring_honor' => 'Honor Mengawas'];
            $earnings = array_merge($earnings, $roleEarnings);
            $deductions = [
                'bpjs_employment_deduction' => 'BPJS Ketenagakerjaan', 'bpjs_health_deduction' => 'BPJS Kesehatan',
                'income_tax_deduction' => 'PPh Pasal 21', 'transport_deduction' => 'Potongan Transport',
                'lateness_deduction' => 'Potongan Keterlambatan',
            ];
        @endphp
        <section class="rounded-2xl border border-brand-100 bg-brand-50 p-6"><h2 class="font-semibold text-brand-800">Komponen Otomatis</h2><div class="mt-4 grid gap-5 md:grid-cols-2"><label class="text-sm font-semibold">Gaji Pokok<input value="Rp {{ number_format((float) $payroll->base_salary, 0, ',', '.') }}" readonly class="{{ $money }} bg-white/70"></label><label class="text-sm font-semibold">Tunjangan Transport<input value="Rp {{ number_format((float) $payroll->transport_allowance, 0, ',', '.') }}" readonly class="{{ $money }} bg-white/70"></label></div></section>
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-semibold">Pendapatan Manual</h2><div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">@foreach ($earnings as $field => $label)<label class="text-sm font-semibold">{{ $label }}<input type="text" inputmode="numeric" data-rupiah name="{{ $field }}" value="{{ old($field, number_format((float) $payroll->{$field}, 0, ',', '.')) }}" class="{{ $money }}">@error($field)<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>@endforeach</div></section>
        <section class="rounded-2xl border border-red-100 bg-white p-6 shadow-sm"><h2 class="font-semibold">Potongan</h2><div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">@foreach ($deductions as $field => $label)<label class="text-sm font-semibold">{{ $label }}<input type="text" inputmode="numeric" data-rupiah name="{{ $field }}" value="{{ old($field, number_format((float) $payroll->{$field}, 0, ',', '.')) }}" class="{{ $money }}">@error($field)<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>@endforeach</div><label class="mt-5 block text-sm font-semibold">Catatan<textarea name="notes" rows="3" class="{{ $money }}">{{ old('notes', $payroll->notes) }}</textarea></label></section>
        <div class="flex justify-end gap-3"><a href="{{ route('payrolls.show', $payroll) }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold">Batal</a><button class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white">Simpan & Hitung</button></div>
    </form>
@endsection
@push('scripts')
    <script>
        document.querySelectorAll('[data-rupiah]').forEach((input) => {
            const formatRupiah = () => {
                const digits = input.value.replace(/\D/g, '');
                input.value = `Rp ${new Intl.NumberFormat('id-ID').format(Number(digits || 0))}`;
            };

            input.addEventListener('input', formatRupiah);
            formatRupiah();
        });
    </script>
@endpush
