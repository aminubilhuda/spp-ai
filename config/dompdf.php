<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DomPDF Options
    |--------------------------------------------------------------------------
    |
    | Available options: https://github.com/dompdf/dompdf#options
    |
    */

    'options' => [
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'isFontSubsettingEnabled' => true,
        'defaultFont' => 'Arial',
        'chroot' => public_path(),
        'enable_font_subsetting' => true,
        'default_paper_size' => 'a4',
        'dpi' => 150,
        'font_height_ratio' => 0.9,
        'enable_php' => false,
        'enable_javascript' => false,
        'enable_remote' => true,
        'font_cache' => storage_path('fonts/'),
        'temp_dir' => sys_get_temp_dir(),
        'log_output_file' => storage_path('logs/dompdf.htm'),
        'default_media_type' => 'screen',
        'default_paper_orientation' => 'portrait',
        'default_font' => 'Arial',
        'dpi' => 96,
        'font_height_ratio' => 0.9,
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_font_size' => '12',
        'default_font' => 'Arial',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9,
        'orientation' => 'portrait',
        'page_height' => '297mm',
        'page_width' => '210mm',
    ],

    /*
    |--------------------------------------------------------------------------
    | DomPDF Fonts
    |--------------------------------------------------------------------------
    |
    | Configure the fonts that DomPDF can use
    |
    */

    'fonts' => [
        'Arial' => [
            'normal' => storage_path('fonts/arial.ttf'),
            'bold' => storage_path('fonts/arialbd.ttf'),
            'italic' => storage_path('fonts/ariali.ttf'),
            'bold_italic' => storage_path('fonts/arialbi.ttf'),
        ],
        'Times' => [
            'normal' => storage_path('fonts/times.ttf'),
            'bold' => storage_path('fonts/timesbd.ttf'),
            'italic' => storage_path('fonts/timesi.ttf'),
            'bold_italic' => storage_path('fonts/timesbi.ttf'),
        ],
        'Courier' => [
            'normal' => storage_path('fonts/courier.ttf'),
            'bold' => storage_path('fonts/courierbd.ttf'),
            'italic' => storage_path('fonts/courieri.ttf'),
            'bold_italic' => storage_path('fonts/courierbi.ttf'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DomPDF Temporary Directory
    |--------------------------------------------------------------------------
    |
    | Directory where DomPDF stores temporary files
    |
    */

    'temp_dir' => storage_path('app/temp/dompdf'),

    /*
    |--------------------------------------------------------------------------
    | DomPDF Font Cache Directory
    |--------------------------------------------------------------------------
    |
    | Directory where DomPDF caches fonts
    |
    */

    'font_cache' => storage_path('fonts/dompdf'),

    /*
    |--------------------------------------------------------------------------
    | DomPDF Log File
    |--------------------------------------------------------------------------
    |
    | Log file for DomPDF debugging
    |
    */

    'log_output_file' => storage_path('logs/dompdf.htm'),
]; 