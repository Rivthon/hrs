<!DOCTYPE html>
<html lang="id">
<body style="font-family: Arial, sans-serif; color: #1e293b; line-height: 1.6;">
    <p>Yth. {{ $payroll->employee->display_name }},</p>
    <p>Terlampir slip gaji periode {{ $payroll->period->period_date->locale('id')->translatedFormat('F Y') }}.</p>
    <p>Dokumen ini bersifat rahasia. Mohon tidak meneruskannya kepada pihak yang tidak berkepentingan.</p>
    <p>Salam,<br>{{ config('payroll.institution.name') }}</p>
</body>
</html>
