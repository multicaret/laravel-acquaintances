<?php

namespace Tests;

class FriendshipsTest extends TestCase
{
    // use DatabaseTransactions;

    /** @test */
    public function user_can_send_a_friend_request()
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);

        $this->assertCount(1, $recipient->getFriendRequests());
    }

    /** @test */
    public function user_can_not_send_a_friend_request_if_frienship_is_pending()
    {
        $sender = createUser();
        $recipient = createUser();
        $sender->befriend($recipient);
        $sender->befriend($recipient);
        $sender->befriend($recipient);

        $this->assertCount(1, $recipient->getFriendRequests());
    }


    /** @test */
    public function user_can_send_a_friend_request_if_frienship_is_denied()
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        $recipient->denyFriendRequest($sender);

        $sender->befriend($recipient);

        $this->assertCount(1, $recipient->getFriendRequests());
    }

    /** @test */
    public function user_can_remove_a_friend_request()
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->befriend($recipient);
        $this->assertCount(1, $recipient->getFriendRequests());

        $sender->unfriend($recipient);
        $this->assertCount(0, $recipient->getFriendRequests());

        // Can resend friend request after deleted
        $sender->befriend($recipient);
        $this->assertCount(1, $recipient->getFriendRequests());

        $recipient->acceptFriendRequest($sender);
        $this->assertEquals(true, $recipient->isFriendWith($sender));
        // Can remove friend request after accepted
        $sender->unfriend($recipient);
        $this->assertEquals(false, $recipient->isFriendWith($sender));
    }

    /** @test */
    public function user_is_friend_with_another_user_if_accepts_a_friend_request()
    {
        $sender = createUser();
        $recipient = createUser();
        //send fr
        $sender->befriend($recipient);
        //accept fr
        $recipient->acceptFriendRequest($sender);

        $this->assertTrue($recipient->isFriendWith($sender));
        $this->assertTrue($sender->isFriendWith($recipient));
        //fr has been delete
        $this->assertCount(0, $recipient->getFriendRequests());
    }

    /** @test */
    public function user_is_not_friend_with_another_user_until_he_accepts_a_friend_request()
    {
        $sender = createUser();
        $recipient = createUser();
        //send fr
        $sender->befriend($recipient);

        $this->assertFalse($recipient->isFriendWith($sender));
        $this->assertFalse($sender->isFriendWith($recipient));
    }

    /** @test */
    public function user_has_friend_request_from_another_user_if_he_received_a_friend_request()
    {
        $sender = createUser();
        $recipient = createUser();
        //send fr
        $sender->befriend($recipient);

        $this->assertTrue($recipient->hasFriendRequestFrom($sender));
        $this->assertFalse($sender->hasFriendRequestFrom($recipient));
    }

    /** @test */
    public function user_has_sent_friend_request_to_this_user_if_he_already_sent_request()
    {
        $sender = createUser();
        $recipient = createUser();
        //send fr
        $sender->befriend($recipient);

        $this->assertFalse($recipient->hasSentFriendRequestTo($sender));
        $this->assertTrue($sender->hasSentFriendRequestTo($recipient));
    }

    /** @test */
    public function user_has_not_friend_request_from_another_user_if_he_accepted_the_friend_request()
    {
        $sender = createUser();
        $recipient = createUser();
        //send fr
        $sender->befriend($recipient);
        //accept fr
        $recipient->acceptFriendRequest($sender);

        $this->assertFalse($recipient->hasFriendRequestFrom($sender));
        $this->assertFalse($sender->hasFriendRequestFrom($recipient));
    }

    /** @test */
    public function user_cannot_accept_his_own_friend_request()
    {
        $sender = createUser();
        $recipient = createUser();

        //send fr
        $sender->befriend($recipient);

        $sender->acceptFriendRequest($recipient);
        $this->assertFalse($recipient->isFriendWith($sender));
    }

    /** @test */
    public function user_can_deny_a_friend_request()
    {
        $sender = createUser();
        $recipient = createUser();
        $sender->befriend($recipient);

        $recipient->denyFriendRequest($sender);

        $this->assertFalse($recipient->isFriendWith($sender));

        //fr has been delete
        $this->assertCount(0, $recipient->getFriendRequests());
        $this->assertCount(1, $sender->getDeniedFriendships());
    }

    /** @test */
    public function user_can_block_another_user()
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->blockFriend($recipient);

        $this->assertTrue($recipient->isBlockedBy($sender));
        $this->assertTrue($sender->hasBlocked($recipient));
        //sender is not blocked by receipient
        $this->assertFalse($sender->isBlockedBy($recipient));
        $this->assertFalse($recipient->hasBlocked($sender));
    }

    /** @test */
    public function user_can_unblock_a_blocked_user()
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->blockFriend($recipient);
        $sender->unblockFriend($recipient);

        $this->assertFalse($recipient->isBlockedBy($sender));
        $this->assertFalse($sender->hasBlocked($recipient));
    }

    /** @test */
    public function user_block_is_permanent_unless_blocker_decides_to_unblock()
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->blockFriend($recipient);
        $this->assertTrue($recipient->isBlockedBy($sender));

        // now recipient blocks sender too
        $recipient->blockFriend($sender);

        // expect that both users have blocked each other
        $this->assertTrue($sender->isBlockedBy($recipient));
        $this->assertTrue($recipient->isBlockedBy($sender));

        $sender->unblockFriend($recipient);

        $this->assertTrue($sender->isBlockedBy($recipient));
        $this->assertFalse($recipient->isBlockedBy($sender));

        $recipient->unblockFriend($sender);
        $this->assertFalse($sender->isBlockedBy($recipient));
        $this->assertFalse($recipient->isBlockedBy($sender));
    }

    /** @test */
    public function user_can_send_friend_request_to_user_who_is_blocked()
    {
        $sender = createUser();
        $recipient = createUser();

        $sender->blockFriend($recipient);
        $sender->befriend($recipient);
        $sender->befriend($recipient);

        $this->assertCount(1, $recipient->getFriendRequests());
    }

    /** @test */
    public function it_returns_all_user_friendships()
    {
        $sender = createUser();
        $recipients = createUser([], 3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertCount(3, $sender->getAllFriendships());
    }

    /** @test */
    public function it_returns_accepted_user_friendships_number()
    {
        $sender = createUser();
        $recipients = createUser([], 3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertEquals(2, $sender->getFriendsCount());
    }

    /** @test */
    public function it_returns_accepted_user_friendships()
    {
        $sender = createUser();
        $recipients = createUser([], 3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertCount(2, $sender->getAcceptedFriendships());
    }

    /** @test */
    public function it_returns_only_accepted_user_friendships()
    {
        $sender = createUser();
        $recipients = createUser([], 4);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertCount(2, $sender->getAcceptedFriendships());

        $this->assertCount(1, $recipients[0]->getAcceptedFriendships());
        $this->assertCount(1, $recipients[1]->getAcceptedFriendships());
        $this->assertCount(0, $recipients[2]->getAcceptedFriendships());
        $this->assertCount(0, $recipients[3]->getAcceptedFriendships());
    }

    /** @test */
    public function it_returns_pending_user_friendships()
    {
        $sender = createUser();
        $recipients = createUser([], 3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $this->assertCount(2, $sender->getPendingFriendships());
    }

    /** @test */
    public function it_returns_denied_user_friendships()
    {
        $sender = createUser();
        $recipients = createUser([], 3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertCount(1, $sender->getDeniedFriendships());
    }

    /** @test */
    public function it_returns_blocked_user_friendships()
    {
        $sender = createUser();
        $recipients = createUser([], 3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->blockFriend($sender);
        $this->assertCount(1, $sender->getBlockedFriendships());
    }

    /** @test */
    public function it_returns_user_friends()
    {
        $sender = createUser();
        $recipients = createUser([], 4);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);

        $this->assertCount(2, $sender->getFriends());
        $this->assertCount(1, $recipients[1]->getFriends());
        $this->assertCount(0, $recipients[2]->getFriends());
        $this->assertCount(0, $recipients[3]->getFriends());

        $this->containsOnlyInstancesOf(\App\User::class, $sender->getFriends());
    }

    /** @test */
    public function it_returns_user_friends_per_page()
    {
        $sender = createUser();
        $recipients = createUser([], 6);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $recipients[3]->acceptFriendRequest($sender);
        $recipients[4]->acceptFriendRequest($sender);


        $this->assertCount(2, $sender->getFriends(2));
        $this->assertCount(4, $sender->getFriends(0));
        $this->assertCount(4, $sender->getFriends(10));
        $this->assertCount(1, $recipients[1]->getFriends());
        $this->assertCount(0, $recipients[2]->getFriends());
        $this->assertCount(0, $recipients[5]->getFriends(2));

        $this->containsOnlyInstancesOf(\App\User::class, $sender->getFriends());
    }

    /** @test */
    public function it_returns_user_friends_of_friends()
    {
        $sender = createUser();
        $recipients = createUser([], 2);
        $fofs = createUser([], 5)->chunk(3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
            $recipient->acceptFriendRequest($sender);

            //add some friends to each recipient too
            foreach ($fofs->shift() as $fof) {
                $recipient->befriend($fof);
                $fof->acceptFriendRequest($recipient);
            }
        }

        $this->assertCount(2, $sender->getFriends());
        $this->assertCount(4, $recipients[0]->getFriends());
        $this->assertCount(3, $recipients[1]->getFriends());

        $this->assertCount(5, $sender->getFriendsOfFriends());

        $this->containsOnlyInstancesOf(\App\User::class, $sender->getFriendsOfFriends());
    }

    /** @test */
    public function it_returns_user_mutual_friends()
    {
        $sender = createUser();
        $recipients = createUser([], 2);
        $fofs = createUser([], 5)->chunk(3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
            $recipient->acceptFriendRequest($sender);

            //add some friends to each recipient too
            foreach ($fofs->shift() as $fof) {
                $recipient->befriend($fof);
                $fof->acceptFriendRequest($recipient);
                $fof->befriend($sender);
                $sender->acceptFriendRequest($fof);
            }
        }

        $this->assertCount(3, $sender->getMutualFriends($recipients[0]));
        $this->assertCount(3, $recipients[0]->getMutualFriends($sender));

        $this->assertCount(2, $sender->getMutualFriends($recipients[1]));
        $this->assertCount(2, $recipients[1]->getMutualFriends($sender));

        $this->containsOnlyInstancesOf(\App\User::class, $sender->getMutualFriends($recipients[0]));
    }

    /** @test */
    public function it_returns_user_mutual_friends_per_page()
    {
        $sender = createUser();
        $recipients = createUser([], 2);
        $fofs = createUser([], 8)->chunk(5);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
            $recipient->acceptFriendRequest($sender);

            //add some friends to each recipient too
            foreach ($fofs->shift() as $fof) {
                $recipient->befriend($fof);
                $fof->acceptFriendRequest($recipient);
                $fof->befriend($sender);
                $sender->acceptFriendRequest($fof);
            }
        }

        $this->assertCount(2, $sender->getMutualFriends($recipients[0], 2));
        $this->assertCount(5, $sender->getMutualFriends($recipients[0], 0));
        $this->assertCount(5, $sender->getMutualFriends($recipients[0], 10));
        $this->assertCount(2, $recipients[0]->getMutualFriends($sender, 2));
        $this->assertCount(5, $recipients[0]->getMutualFriends($sender, 0));
        $this->assertCount(5, $recipients[0]->getMutualFriends($sender, 10));

        $this->assertCount(1, $recipients[1]->getMutualFriends($recipients[0], 10));

        $this->containsOnlyInstancesOf(\App\User::class, $sender->getMutualFriends($recipients[0], 2));
    }

    /** @test */
    public function it_returns_user_mutual_friends_number()
    {
        $sender = createUser();
        $recipients = createUser([], 2);
        $fofs = createUser([], 5)->chunk(3);

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
            $recipient->acceptFriendRequest($sender);

            //add some friends to each recipient too
            foreach ($fofs->shift() as $fof) {
                $recipient->befriend($fof);
                $fof->acceptFriendRequest($recipient);
                $fof->befriend($sender);
                $sender->acceptFriendRequest($fof);
            }
        }

        $this->assertEquals(3, $sender->getMutualFriendsCount($recipients[0]));
        $this->assertEquals(3, $recipients[0]->getMutualFriendsCount($sender));

        $this->assertEquals(2, $sender->getMutualFriendsCount($recipients[1]));
        $this->assertEquals(2, $recipients[1]->getMutualFriendsCount($sender));
    }
}
