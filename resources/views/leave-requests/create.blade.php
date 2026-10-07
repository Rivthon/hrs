@extends('layouts.app')
@section('title', 'Ajukan Cuti atau Izin | HRS Kampus')
@section('content')
    <a href="{{ route('leave-requests.index') }}" class="text-sm font-semibold text-brand-700">&larr; Kembali</a>
    <h1 class="mt-3 text-3xl font-semibold">Ajukan Cuti atau Izin</h1>
    <p class="mt-2 text-sm text-slate-500">Pengajuan akan diteruskan kepada atasan langsung lalu SDM.</p>

    @if ($employee->supervisor === null)<div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">Atasan langsung belum ditentukan. Hubungi SDM sebelum mengajukan cuti.</div>@endif

    <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        @if ($errors->any())<div class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <div class="grid gap-5 md:grid-cols-2">
            <label class="text-sm font-semibold md:col-span-2">Jenis cuti atau izin
                <select id="leave_type" name="leave_type" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3">
                    @foreach (\App\Enums\LeaveType::cases() as $leaveType)<option value="{{ $leaveType->value }}" @selected(old('leave_type', 'annual') === $leaveType->value)>{{ $leaveType->label() }}</option>@endforeach
                </select>
            </label>

            <div id="type-help" class="rounded-xl bg-brand-50 p-4 text-sm leading-6 text-brand-800 md:col-span-2"></div>

            <label class="text-sm font-semibold">Tanggal mulai <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required min="{{ today()->toDateString() }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"></label>
            <label class="text-sm font-semibold">Tanggal selesai <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required min="{{ today()->toDateString() }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100"><span id="annual-limit" class="mt-1 block text-xs font-normal text-slate-400">Cuti tahunan maksimal 3 hari kerja.</span></label>

            <div id="time-fields" class="hidden grid gap-5 md:col-span-2 md:grid-cols-2">
                <label class="text-sm font-semibold">Jam mulai <input type="time" name="start_time" value="{{ old('start_time') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"></label>
                <label class="text-sm font-semibold">Jam selesai <input type="time" name="end_time" value="{{ old('end_time') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"><span class="mt-1 block text-xs font-normal text-slate-400">Setengah hari tepat 4 jam; izin per jam maksimal 4 jam.</span></label>
            </div>

            <div id="doctor-document" class="hidden md:col-span-2">
                <label class="text-sm font-semibold">Surat dokter <span class="text-red-500">*</span><input type="file" name="supporting_document" accept=".pdf,image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:font-semibold file:text-brand-700"><span class="mt-1 block text-xs font-normal text-slate-400">PDF, JPG, PNG, atau WebP. Maksimal 5 MB.</span></label>
            </div>

            <label class="text-sm font-semibold md:col-span-2">Pegawai pengganti <select name="replacement_employee_id" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"><option value="">Pilih pegawai pengganti</option>@foreach ($replacements as $replacement)<option value="{{ $replacement->id }}" @selected((string) old('replacement_employee_id') === (string) $replacement->id)>{{ $replacement->display_name }} &mdash; {{ $replacement->department->name }}</option>@endforeach</select></label>
            <label class="text-sm font-semibold md:col-span-2">Alasan dan keterangan <textarea name="reason" rows="5" required minlength="10" maxlength="2000" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100">{{ old('reason') }}</textarea></label>
        </div>
        <div class="mt-6 flex justify-end gap-3"><a href="{{ route('leave-requests.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold">Batal</a><button @disabled($employee->supervisor === null) class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">Kirim Pengajuan</button></div>
    </form>
@endsection

@push('scripts')
<script>
    const leaveType = document.getElementById('leave_type');
    const timeFields = document.getElementById('time-fields');
    const doctorDocument = document.getElementById('doctor-document');
    const annualLimit = document.getElementById('annual-limit');
    const typeHelp = document.getElementById('type-help');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const descriptions = {
        annual: 'Mengurangi saldo cuti tahunan setelah disetujui SDM. Maksimal 3 hari kerja per pengajuan.',
        sick: 'Tidak mengurangi saldo cuti tahunan. Surat dokter wajib dilampirkan.',
        maternity: 'Khusus pegawai perempuan dan tidak mengurangi saldo cuti tahunan.',
        special_marriage: 'Izin khusus untuk keperluan pernikahan dan tidak mengurangi saldo cuti tahunan.',
        special_bereavement: 'Izin khusus kedukaan dan tidak mengurangi saldo cuti tahunan.',
        half_day: 'Pilih satu tanggal dan rentang waktu tepat 4 jam.',
        hourly: 'Pilih satu tanggal dan rentang waktu maksimal 4 jam.',
    };
    function updateLeaveFields() {
        const type = leaveType.value;
        const partial = ['half_day', 'hourly'].includes(type);
        timeFields.classList.toggle('hidden', !partial);
        doctorDocument.classList.toggle('hidden', type !== 'sick');
        annualLimit.classList.toggle('hidden', type !== 'annual');
        typeHelp.textContent = descriptions[type];
        if (partial && startDate.value) endDate.value = startDate.value;
    }
    leaveType.addEventListener('change', updateLeaveFields);
    startDate.addEventListener('change', () => { if (['half_day', 'hourly'].includes(leaveType.value)) endDate.value = startDate.value; });
    updateLeaveFields();
</script>
@endpush
