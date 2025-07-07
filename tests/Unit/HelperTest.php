<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class HelperTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getInstansiLogoUrl dengan logo yang tidak ada
     */
    public function test_get_instansi_logo_url_without_logo()
    {
        // Mock setting tanpa logo
        $this->mock(\App\Models\Setting::class, function ($mock) {
            $mock->shouldReceive('getInstansiSettings')
                ->andReturn((object) ['logo_instansi' => null]);
        });

        $result = getInstansiLogoUrl();
        $this->assertEquals('', $result);
    }

    /**
     * Test getInstansiLogoUrl dengan file yang tidak ada
     */
    public function test_get_instansi_logo_url_with_nonexistent_file()
    {
        // Mock setting dengan logo yang tidak ada
        $this->mock(\App\Models\Setting::class, function ($mock) {
            $mock->shouldReceive('getInstansiSettings')
                ->andReturn((object) ['logo_instansi' => 'nonexistent.png']);
        });

        $result = getInstansiLogoUrl();
        $this->assertEquals('', $result);
    }

    /**
     * Test getInstansiLogoUrl dengan file yang valid
     */
    public function test_get_instansi_logo_url_with_valid_file()
    {
        // Buat file test
        Storage::disk('public')->put('test-logo.png', 'fake-image-data');

        // Mock setting dengan logo yang valid
        $this->mock(\App\Models\Setting::class, function ($mock) {
            $mock->shouldReceive('getInstansiSettings')
                ->andReturn((object) ['logo_instansi' => 'test-logo.png']);
        });

        $result = getInstansiLogoUrl();
        
        // Hapus file test
        Storage::disk('public')->delete('test-logo.png');
        
        // Cek apakah hasilnya adalah base64 data URL
        $this->assertStringStartsWith('data:', $result);
    }
} 