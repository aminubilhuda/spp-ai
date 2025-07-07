# Troubleshooting PDF CSS Issues

Dokumentasi untuk mengatasi masalah CSS tidak terload di PDF DomPDF.

## Masalah Umum

### 1. **CSS Tidak Terload di PDF**

**Gejala:**
- PDF tampil tanpa styling
- Layout berantakan
- Font tidak sesuai

**Penyebab:**
- DomPDF tidak mendukung CSS eksternal
- Font tidak tersedia
- CSS properties tidak didukung

**Solusi:**
- Gunakan CSS inline untuk PDF
- Gunakan font yang tersedia (Arial, Times, Courier)
- Hindari CSS modern yang tidak didukung

### 2. **Font Tidak Tampil**

**Gejala:**
- Font default browser
- Karakter aneh
- Font tidak konsisten

**Solusi:**
```php
// Di controller
$pdf->setOptions([
    'defaultFont' => 'Arial',
    'isFontSubsettingEnabled' => false,
]);
```

### 3. **Layout Berantakan**

**Gejala:**
- Tabel tidak rapi
- Spacing tidak sesuai
- Element overlap

**Solusi:**
- Gunakan `display: table` bukan `display: flex`
- Hindari CSS Grid untuk PDF
- Gunakan unit yang sederhana (px, mm, cm)

## CSS yang Didukung DomPDF

### ✅ **Didukung**
- `display: block`, `inline`, `table`, `table-cell`
- `position: static`, `relative`, `absolute`
- `margin`, `padding`, `border`
- `color`, `background-color`
- `font-family`, `font-size`, `font-weight`
- `text-align`, `text-decoration`
- `width`, `height`
- `float: left`, `right`

### ❌ **Tidak Didukung**
- `display: flex`, `grid`
- `position: fixed`, `sticky`
- CSS animations
- CSS transforms
- CSS gradients (gunakan warna solid)
- CSS custom properties
- CSS media queries (kecuali print)

## Konfigurasi Optimal

### Controller Configuration
```php
private function generatePDF($data)
{
    $pdf = PDF::loadView('template', $data);
    
    $pdf->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'Arial',
        'chroot' => public_path(),
        'default_paper_size' => 'a4',
        'dpi' => 96,
        'font_height_ratio' => 0.9,
        'enable_php' => false,
        'enable_javascript' => false,
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
    ]);

    return $pdf;
}
```

### Template Structure
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    @if(request('html') == 'true')
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @else
        <style>
            /* CSS untuk PDF */
            body { font-family: Arial, sans-serif; }
            .container { width: 100%; }
            .header { background: #333; color: white; }
        </style>
    @endif
</head>
<body>
    <!-- Content -->
</body>
</html>
```

## Best Practices

### 1. **CSS untuk PDF**
```css
/* Gunakan CSS sederhana */
body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    line-height: 1.4;
    color: #333;
}

.container {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 8px;
    border: 1px solid #ddd;
    text-align: left;
}
```

### 2. **Layout untuk PDF**
```css
/* Gunakan table layout */
.info-grid {
    display: table;
    width: 100%;
}

.info-section {
    display: table-cell;
    width: 50%;
    vertical-align: top;
    padding: 10px;
}
```

### 3. **Font untuk PDF**
```css
/* Gunakan font yang tersedia */
body {
    font-family: Arial, Helvetica, sans-serif;
}

h1, h2, h3 {
    font-family: Arial, sans-serif;
    font-weight: bold;
}
```

## Testing

### 1. **Test HTML Version**
```
GET /invoice/{id}/html
```
- Cek apakah CSS eksternal terload
- Verifikasi layout dan styling

### 2. **Test PDF Version**
```
GET /invoice/{id}
```
- Cek apakah CSS inline terload
- Verifikasi font dan layout

### 3. **Test Download**
```
GET /invoice/{id}?download=true
```
- Cek apakah PDF terdownload dengan benar
- Verifikasi file size dan quality

## Debugging

### 1. **Enable Logging**
```php
$pdf->setOptions([
    'log_output_file' => storage_path('logs/dompdf.htm'),
]);
```

### 2. **Check Logs**
```bash
tail -f storage/logs/dompdf.htm
```

### 3. **Common Errors**
- **Font not found**: Gunakan font yang tersedia
- **CSS not loaded**: Gunakan CSS inline
- **Image not found**: Cek path dan permission
- **Memory limit**: Kurangi DPI atau ukuran

## Performance Tips

### 1. **Optimize Images**
- Gunakan format PNG atau JPG
- Kompres gambar sebelum upload
- Batasi ukuran gambar

### 2. **Reduce CSS**
- Hapus CSS yang tidak digunakan
- Gunakan CSS yang sederhana
- Hindari CSS yang kompleks

### 3. **Cache Fonts**
```php
$pdf->setOptions([
    'font_cache' => storage_path('fonts/dompdf'),
]);
```

## Alternative Solutions

### 1. **Use Different PDF Library**
- **mPDF**: Lebih modern, dukungan CSS lebih baik
- **wkhtmltopdf**: Dukungan CSS lengkap
- **Puppeteer**: Render HTML ke PDF

### 2. **Generate HTML First**
```php
// Generate HTML
$html = view('template', $data)->render();

// Convert to PDF
$pdf = PDF::loadHTML($html);
```

### 3. **Use CSS Framework**
- **Bootstrap**: Responsive, banyak komponen
- **Tailwind**: Utility-first CSS
- **Custom CSS**: Sesuai kebutuhan

## Maintenance

### 1. **Regular Updates**
- Update DomPDF ke versi terbaru
- Test CSS compatibility
- Monitor performance

### 2. **Backup Configuration**
- Backup file konfigurasi
- Document custom settings
- Version control

### 3. **Monitor Logs**
- Check error logs regularly
- Monitor performance metrics
- Fix issues promptly 