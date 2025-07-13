<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiKeyCommand extends Command
{
    protected $signature = 'generate:api-key {--show : Tampilkan API key yang dihasilkan}';
    protected $description = 'Generate API key untuk sinkronisasi database';

    public function handle()
    {
        $apiKey = 'spp-ai-sync-' . date('Y') . '-' . Str::random(16);
        
        $this->info('API Key berhasil dibuat!');
        $this->line('');
        $this->line('API Key: ' . $apiKey);
        $this->line('');
        
        if ($this->option('show')) {
            $this->info('Copy API key di atas dan tambahkan ke file .env:');
            $this->line('SYNC_API_KEY=' . $apiKey);
        } else {
            $this->info('Untuk menampilkan API key, jalankan:');
            $this->line('php artisan generate:api-key --show');
        }
        
        $this->line('');
        $this->warn('PENTING:');
        $this->line('1. API key ini harus SAMA di kedua sistem (local & online)');
        $this->line('2. Jangan bagikan API key ke orang lain');
        $this->line('3. Simpan API key dengan aman');
        
        return 0;
    }
} 