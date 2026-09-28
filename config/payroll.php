<?php

return [
    'institution' => [
        'name' => env('PAYROLL_INSTITUTION_NAME', 'STIKes Bogor Husada'),
        'address_line_1' => env('PAYROLL_ADDRESS_LINE_1', 'Jl. Sholeh Iskandar No.4 RT.02 RW 03'),
        'address_line_2' => env('PAYROLL_ADDRESS_LINE_2', 'Tanah Sareal, Bogor, Jawa Barat 16164'),
        'phone' => env('PAYROLL_PHONE', '(0251) 8576-152'),
        'website' => env('PAYROLL_WEBSITE', 'www.sbh.ac.id'),
    ],
    'signatory' => [
        'city' => env('PAYROLL_SIGNATORY_CITY', 'Bogor'),
        'position' => env('PAYROLL_SIGNATORY_POSITION', 'Biro Adm Umum & Keu'),
        'name' => env('PAYROLL_SIGNATORY_NAME', 'Lisnawati, S.E'),
    ],
];
