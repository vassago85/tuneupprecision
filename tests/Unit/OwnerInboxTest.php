<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\OwnerInbox;
use Tests\TestCase;

class OwnerInboxTest extends TestCase
{
    public function test_owner_alerts_go_to_dirk(): void
    {
        $this->assertSame('dirkpio01@gmail.com', OwnerInbox::email());
    }

    public function test_a_blank_override_still_falls_back_to_dirk(): void
    {
        config(['tuneup.notifications.email' => '   ']);

        $this->assertSame('dirkpio01@gmail.com', OwnerInbox::email());
    }
}
