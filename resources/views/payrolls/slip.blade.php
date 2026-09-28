<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji {{ $payroll->period->period_date->format('m/Y') }}</title>
    <style>
        @page { margin: 32px 42px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        .header { border-bottom: 2px solid #222; padding-bottom: 5px; }
        .logo { width: 112px; }
        .institution { font-size: 19px; line-height: 1.25; }
        .institution small { display: block; font-size: 10px; }
        .title { text-align: right; font-size: 13px; font-weight: bold; vertical-align: top; }
        .title span { display: block; margin-top: 12px; font-style: italic; font-weight: normal; }
        .identity { margin-top: 28px; margin-bottom: 20px; }
        .identity td { padding: 3px 4px; }
        .label { width: 16%; }
        .colon { width: 2%; }
        .section-title { border-top: 1px solid #222; border-bottom: 3px double #222; font-size: 11px; font-weight: bold; padding: 3px 2px; }
        .component { vertical-align: top; width: 50%; }
        .component table td { padding: 3px 2px; }
        .amount-label { width: 62%; }
        .currency { width: 8%; }
        .amount { width: 30%; text-align: right; }
        .totals { border-top: 3px double #222; border-bottom: 3px double #222; margin-top: 16px; font-weight: bold; }
        .totals td { padding: 4px 2px; }
        .net { margin-top: 24px; font-size: 12px; font-weight: bold; }
        .signature { text-align: center; width: 31%; line-height: 1.5; }
        .signature img { width: 115px; height: 72px; object-fit: contain; }
        .verified {
            width: 126px;
            margin: 14px auto 12px;
            padding: 13px 5px;
            border: 4px double #b91c1c;
            border-radius: 50%;
            color: #b91c1c;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1.4px;
            line-height: 1;
            text-align: center;
            transform: rotate(-7deg);
        }
        .muted { color: #444; }
    </style>
</head>
<body>
    @php
        $rupiah = fn ($value) => number_format((float) $value, 0, ',', '.');
        $earnings = [
            'base_salary' => 'Gaji Pokok', 'position_allowance' => 'Tunjangan Jabatan',
            'functional_allowance' => 'Tunjangan Fungsional', 'transport_allowance' => 'Tunjangan Transport',
            'teaching_honor' => 'Honor Mengajar', 'proctoring_honor' => 'Honor Mengawas',
            'final_seminar_honor' => 'Seminar Akhir, Hasil & Sidang Skripsi',
            'thesis_defense_honor' => 'Honor Hasil & Sidang Skripsi',
            'thesis_supervisor_honor' => 'Honorarium Dosen Pembimbing Skripsi',
            'pkk_supervision_honor' => 'Supervisi PKK', 'practical_exam_honor' => 'Ujian Praktikum (Uprak)',
            'duty_honor' => 'Piket/Dinas',
        ];
        $deductions = [
            'bpjs_employment_deduction' => 'BPJS Ketenagakerjaan', 'bpjs_health_deduction' => 'BPJS Kesehatan',
            'income_tax_deduction' => 'PPh Pasal 21', 'transport_deduction' => 'Transport',
            'lateness_deduction' => 'Keterlambatan',
        ];
    @endphp
    <table class="header">
        <tr>
            <td style="width: 17%"><img class="logo" src="{{ base_path('12.png') }}"></td>
            <td class="institution" style="width: 58%">{{ $institution['name'] }}<small>{{ $institution['address_line_1'] }}</small><small>{{ $institution['address_line_2'] }}</small><small>Telepon: {{ $institution['phone'] }}</small><small>Website: {{ $institution['website'] }}</small></td>
            <td class="title">SLIP GAJI<span>{{ strtoupper($payroll->period->period_date->locale('id')->translatedFormat('F Y')) }}</span></td>
        </tr>
    </table>
    <table class="identity">
        <tr><td class="label">NIP</td><td class="colon">:</td><td>{{ $payroll->employee->nip }}</td><td class="label">Jabatan</td><td class="colon">:</td><td>{{ $payroll->employee->position?->name ?? '-' }}</td></tr>
        <tr><td>Nama Karyawan</td><td>:</td><td>{{ $payroll->employee->display_name }}</td><td>NPWP</td><td>:</td><td>{{ $payroll->employee->npwp ?: '-' }}</td></tr>
    </table>
    <table><tr><td class="component"><div class="section-title">PENDAPATAN</div><table>@foreach ($earnings as $field => $label)<tr><td class="amount-label">{{ $label }}</td><td class="currency">Rp</td><td class="amount">{{ $rupiah($payroll->{$field}) }}</td></tr>@endforeach</table></td><td class="component"><div class="section-title">POTONGAN</div><table>@foreach ($deductions as $field => $label)<tr><td class="amount-label">{{ $label }}</td><td class="currency">Rp</td><td class="amount">{{ $rupiah($payroll->{$field}) }}</td></tr>@endforeach</table></td></tr></table>
    <table class="totals"><tr><td style="width:31%">JUMLAH PENDAPATAN</td><td style="width:4%">Rp</td><td class="amount" style="width:15%">{{ $rupiah($payroll->gross_income) }}</td><td style="width:31%; padding-left:20px">JUMLAH POTONGAN</td><td style="width:4%">Rp</td><td class="amount" style="width:15%">{{ $rupiah($payroll->total_deductions) }}</td></tr></table>
    <table class="net"><tr><td style="width:18%">Gaji Bersih</td><td style="width:3%">:</td><td>Rp {{ $rupiah($payroll->net_salary) }}</td><td class="signature"><span>{{ $signatory['city'] }}, {{ now()->locale('id')->translatedFormat('d F Y') }}</span><br>{{ $signatory['position'] }}<br>{{ $institution['name'] }}<div class="verified">TERVERIFIKASI</div><strong>{{ $signatory['name'] }}</strong></td></tr></table>
</body>
</html>
