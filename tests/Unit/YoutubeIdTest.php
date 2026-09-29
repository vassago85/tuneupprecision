<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\YoutubeId;
use PHPUnit\Framework\TestCase;

class YoutubeIdTest extends TestCase
{
    public function test_accepts_a_bare_id(): void
    {
        $this->assertSame('dQw4w9WgXcQ', YoutubeId::extract('dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', YoutubeId::extract('  dQw4w9WgXcQ  '));
    }

    public function test_extracts_ids_from_common_youtube_links(): void
    {
        $links = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=12s',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ',
            'https://www.youtube.com/live/dQw4w9WgXcQ',
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://m.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        foreach ($links as $link) {
            $this->assertSame('dQw4w9WgXcQ', YoutubeId::extract($link), $link);
        }
    }

    public function test_rejects_empty_and_unrecognised_values(): void
    {
        $this->assertNull(YoutubeId::extract(null));
        $this->assertNull(YoutubeId::extract(''));
        $this->assertNull(YoutubeId::extract('https://vimeo.com/123456789'));
        $this->assertNull(YoutubeId::extract('not a video'));
    }
}
