<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Videos\Pages\EditVideo;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VideoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_range_video_larger_than_media_library_default_attaches(): void
    {
        Storage::fake('public');

        $video = Video::query()->create([
            'slug' => 'steel-drill',
            'title' => 'Steel drill',
        ]);

        // Minimal MP4 `ftyp` header so the mime check sees video/mp4, padded on
        // disk past 10 MB so the test doesn't hold the file in memory.
        $path = tempnam(sys_get_temp_dir(), 'clip');
        $handle = fopen($path, 'wb');
        fwrite($handle, "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom");
        ftruncate($handle, 11 * 1024 * 1024);
        fclose($handle);

        $video->addMedia($path)
            ->usingFileName('clip.mp4')
            ->toMediaCollection('file', 'public');

        $this->assertTrue($video->fresh()->hasNativeVideo());
    }

    public function test_admin_saves_poster_and_uploaded_video_without_youtube_link(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $video = Video::query()->create([
            'slug' => 'steel-drill',
            'title' => 'Steel drill',
        ]);

        Livewire::actingAs($admin)
            ->test(EditVideo::class, ['record' => $video->getKey()])
            ->fillForm([
                'youtube_id' => '',
                'poster' => UploadedFile::fake()->image('poster.jpg', 1920, 1080),
                'file' => UploadedFile::fake()
                    ->createWithContent('clip.mp4', "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom".str_repeat("\x00", 1024))
                    ->mimeType('video/mp4'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $video->refresh();
        $this->assertTrue($video->hasNativeVideo());
        $this->assertNotNull($video->getFirstMedia('poster'));
        $this->assertNull($video->youtube_id);
    }
}
