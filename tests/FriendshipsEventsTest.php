<?php

namespace Tests;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;

class FriendshipsEventsTest extends TestCase
{
    protected $sender;

    protected $recipient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sender = createUser();
        $this->recipient = createUser();
    }

    #[Test]
    public function friend_request_is_sent(): void
    {
        Event::fake();

        $this->sender->befriend($this->recipient);

        Event::assertDispatched('acq.friendships.sent');
    }

    #[Test]
    public function friend_request_is_accepted(): void
    {
        $this->sender->befriend($this->recipient);

        Event::fake();

        $this->recipient->acceptFriendRequest($this->sender);

        Event::assertDispatched('acq.friendships.accepted');
    }

    #[Test]
    public function friend_request_is_denied(): void
    {
        $this->sender->befriend($this->recipient);

        Event::fake();

        $this->recipient->denyFriendRequest($this->sender);

        Event::assertDispatched('acq.friendships.denied');
    }

    #[Test]
    public function friend_is_blocked(): void
    {
        $this->sender->befriend($this->recipient);
        $this->recipient->acceptFriendRequest($this->sender);

        Event::fake();

        $this->recipient->blockFriend($this->sender);

        Event::assertDispatched('acq.friendships.blocked');
    }

    #[Test]
    public function friend_is_unblocked(): void
    {
        $this->sender->befriend($this->recipient);
        $this->recipient->acceptFriendRequest($this->sender);
        $this->recipient->blockFriend($this->sender);

        Event::fake();

        $this->recipient->unblockFriend($this->sender);

        Event::assertDispatched('acq.friendships.unblocked');
    }

    #[Test]
    public function friendship_is_cancelled(): void
    {
        $this->sender->befriend($this->recipient);
        $this->recipient->acceptFriendRequest($this->sender);

        Event::fake();

        $this->recipient->unfriend($this->sender);

        Event::assertDispatched('acq.friendships.cancelled');
    }
}
