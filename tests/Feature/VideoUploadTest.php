<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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

        // Minimal MP4 `ftyp` header so the mime check sees video/mp4, padded past 10 MB.
        $mp4 = "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom";
        $contents = $mp4.str_repeat("\x00", 15 * 1024 * 1024);

        $video->addMediaFromString($contents)
            ->usingFileName('clip.mp4')
            ->toMediaCollection('file', 'public');

        $this->assertTrue($video->fresh()->hasNativeVideo());
    }
}
