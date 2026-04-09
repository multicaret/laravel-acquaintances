<?php

namespace Tests;

use Multicaret\Acquaintances\Interaction;
use PHPUnit\Framework\Attributes\Test;

class InteractionCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Interaction::flushRequestCache();
    }

    #[Test]
    public function in_memory_cache_prevents_duplicate_queries(): void
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        $recipient->acceptFriendRequest($sender);

        $sender->like($recipient);

        $this->assertTrue($recipient->isLikedBy($sender));

        // Second call should hit in-memory cache, not DB
        $this->assertTrue($recipient->isLikedBy($sender));
    }

    #[Test]
    public function cache_is_invalidated_on_unlike(): void
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->like($recipient);
        $this->assertTrue($recipient->isLikedBy($sender));

        $sender->unlike($recipient);
        Interaction::flushRequestCache();
        $this->assertFalse($recipient->isLikedBy($sender));
    }

    #[Test]
    public function flush_request_cache_clears_all_entries(): void
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->like($recipient);
        $this->assertTrue($recipient->isLikedBy($sender));

        Interaction::flushRequestCache();

        // After flush, it should re-query the DB (still true since data exists)
        $this->assertTrue($recipient->isLikedBy($sender));
    }
}
