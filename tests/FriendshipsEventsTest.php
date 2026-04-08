<?php

namespace Tests;

use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class FriendshipsEventsTest extends TestCase
{
    // use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->sender = createUser();
        $this->recipient = createUser();
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    #[Test]
    public function friend_request_is_sent()
    {
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.sent', Mockery::any()]);

        $this->sender->befriend($this->recipient);
    }

    #[Test]
    public function friend_request_is_accepted()
    {
        $this->sender->befriend($this->recipient);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.accepted', Mockery::any()]);

        $this->recipient->acceptFriendRequest($this->sender);
    }

    #[Test]
    public function friend_request_is_denied()
    {
        $this->sender->befriend($this->recipient);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.denied', Mockery::any()]);

        $this->recipient->denyFriendRequest($this->sender);
    }

    #[Test]
    public function friend_is_blocked()
    {
        $this->sender->befriend($this->recipient);
        $this->recipient->acceptFriendRequest($this->sender);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.blocked', Mockery::any()]);

        $this->recipient->blockFriend($this->sender);
    }

    #[Test]
    public function friend_is_unblocked()
    {
        $this->sender->befriend($this->recipient);
        $this->recipient->acceptFriendRequest($this->sender);
        $this->recipient->blockFriend($this->sender);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.unblocked', Mockery::any()]);

        $this->recipient->unblockFriend($this->sender);
    }

    #[Test]
    public function friendship_is_cancelled()
    {
        $this->sender->befriend($this->recipient);
        $this->recipient->acceptFriendRequest($this->sender);
        Event::shouldReceive('dispatch')->once()->withArgs(['friendships.cancelled', Mockery::any()]);

        $this->recipient->unfriend($this->sender);
    }
}
