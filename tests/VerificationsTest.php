<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_send_a_verification_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->verify($recipient, 'This user is verified for their expertise');

        $this->assertCount(1, $recipient->getVerificationRequests());
    }

    /** @test */
    public function user_can_not_send_a_verification_request_if_verification_is_pending()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $sender->verify($recipient, 'Test verification message');
        $sender->verify($recipient, 'Second verification message');
        $sender->verify($recipient, 'Third verification message');

        $this->assertCount(1, $recipient->getVerificationRequests());
    }

    /** @test */
    public function user_can_send_a_verification_request_if_verification_is_denied()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->verify($recipient, 'Initial verification message');
        $recipient->denyVerificationRequest($sender);

        $sender->verify($recipient, 'Second verification attempt');

        $this->assertCount(1, $recipient->getVerificationRequests());
    }

    /** @test */
    public function user_can_remove_a_verification_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->verify($recipient, 'Test verification message');
        $this->assertCount(1, $recipient->getVerificationRequests());

        $sender->unverify($recipient);
        $this->assertCount(0, $recipient->getVerificationRequests());

        // Can resend verification request after deleted
        $sender->verify($recipient, 'Second verification message');
        $this->assertCount(1, $recipient->getVerificationRequests());

        $recipient->acceptVerificationRequest($sender);
        $this->assertEquals(true, $recipient->isVerifiedWith($sender));
        // Can remove verification after accepted
        $sender->unverify($recipient);
        $this->assertEquals(false, $recipient->isVerifiedWith($sender));
    }

    /** @test */
    public function user_is_verified_with_another_user_if_accepts_a_verification_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        //send verification request
        $sender->verify($recipient, 'Test verification message');
        //accept verification request
        $recipient->acceptVerificationRequest($sender);

        $this->assertTrue($recipient->isVerifiedWith($sender));
        $this->assertTrue($sender->isVerifiedWith($recipient));
        //verification request has been deleted
        $this->assertCount(0, $recipient->getVerificationRequests());
    }

    /** @test */
    public function user_is_not_verified_with_another_user_until_he_accepts_a_verification_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        //send verification request
        $sender->verify($recipient, 'Test verification message');

        $this->assertFalse($recipient->isVerifiedWith($sender));
        $this->assertFalse($sender->isVerifiedWith($recipient));
    }

    /** @test */
    public function user_has_verification_request_from_another_user_if_he_received_a_verification_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        //send verification request
        $sender->verify($recipient, 'Test verification message');

        $this->assertTrue($recipient->hasVerificationRequestFrom($sender));
        $this->assertFalse($sender->hasVerificationRequestFrom($recipient));
    }

    /** @test */
    public function user_has_sent_verification_request_to_this_user_if_he_already_sent_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        //send verification request
        $sender->verify($recipient, 'Test verification message');

        $this->assertFalse($recipient->hasSentVerificationRequestTo($sender));
        $this->assertTrue($sender->hasSentVerificationRequestTo($recipient));
    }

    /** @test */
    public function user_has_not_verification_request_from_another_user_if_he_accepted_the_verification_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        //send verification request
        $sender->verify($recipient, 'Test verification message');
        //accept verification request
        $recipient->acceptVerificationRequest($sender);

        $this->assertFalse($recipient->hasVerificationRequestFrom($sender));
        $this->assertFalse($sender->hasVerificationRequestFrom($recipient));
    }

    /** @test */
    public function user_cannot_accept_his_own_verification_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        //send verification request
        $sender->verify($recipient, 'Test verification message');

        $sender->acceptVerificationRequest($recipient);
        $this->assertFalse($recipient->isVerifiedWith($sender));
    }

    /** @test */
    public function user_can_deny_a_verification_request()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $sender->verify($recipient, 'Test verification message');

        $recipient->denyVerificationRequest($sender);

        $this->assertFalse($recipient->isVerifiedWith($sender));

        //verification request has been updated to denied status
        $this->assertCount(0, $recipient->getVerificationRequests());
        $this->assertCount(1, $sender->getDeniedVerifications());
    }

    /** @test */
    public function user_can_block_another_user()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->blockVerification($recipient);

        // Verification blocking creates a verification record with BLOCKED status
        // But blocking checks are still done via friendship methods
        $verification = $sender->getVerification($recipient);
        $this->assertEquals(\Multicaret\Acquaintances\Status::BLOCKED, $verification->status);

        // The actual blocking status is checked via friendship methods
        // since verification blocking depends on friendship blocking
        $this->assertTrue($sender->getBlockedVerifications()->contains($verification));
    }

    /** @test */
    public function user_can_unblock_a_blocked_user()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->blockVerification($recipient);
        $verification = $sender->getVerification($recipient);
        $this->assertEquals(\Multicaret\Acquaintances\Status::BLOCKED, $verification->status);

        $sender->unblockVerification($recipient);

        // Verification should be deleted after unblocking
        $this->assertNull($sender->getVerification($recipient));
        $this->assertCount(0, $sender->getBlockedVerifications());
    }

    /** @test */
    public function user_block_is_permanent_unless_blocker_decides_to_unblock()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->blockVerification($recipient);
        $senderVerification = $sender->getVerification($recipient);
        $this->assertEquals(\Multicaret\Acquaintances\Status::BLOCKED, $senderVerification->status);

        // Check that there's one blocked verification between the users
        $this->assertCount(1, $sender->getBlockedVerifications());

        // now recipient blocks sender too
        // This should replace the previous verification since there can only be one between two users
        $recipient->blockVerification($sender);
        $verificationFromRecipient = $recipient->getVerification($sender);
        $this->assertEquals(\Multicaret\Acquaintances\Status::BLOCKED, $verificationFromRecipient->status);

        // Now the recipient is the sender of the blocked verification
        // Sender should have 1 blocked verification (received from recipient)
        // Recipient should have 1 blocked verification (sent to sender)
        $this->assertCount(1, $sender->getBlockedVerifications());
        $this->assertCount(1, $recipient->getBlockedVerifications());

        // Since recipient is now the sender of the verification, they can unblock it
        $recipient->unblockVerification($sender);

        // After recipient unblocks, the verification should be deleted
        $this->assertCount(0, $sender->getBlockedVerifications());
        $this->assertCount(0, $recipient->getBlockedVerifications());
    }

    /** @test */
    public function user_cannot_send_verification_request_after_verification_block()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        // First send a verification request and have it accepted
        $sender->verify($recipient, 'Initial verification message');
        $recipient->acceptVerificationRequest($sender);
        $this->assertTrue($sender->isVerifiedWith($recipient));

        // Now block the verification
        $sender->blockVerification($recipient);
        $verification = $sender->getVerification($recipient);
        $this->assertEquals(\Multicaret\Acquaintances\Status::BLOCKED, $verification->status);

        // User should NOT be able to send new verification requests after blocking
        // The blocked verification prevents new ones until unblocked
        $result = $sender->verify($recipient, 'Second verification message after block');

        // verify() should return false when blocked
        $this->assertFalse($result);
        // No new verification requests should be created
        $this->assertCount(0, $recipient->getVerificationRequests());
    }

    /** @test */
    public function it_returns_all_user_verifications()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(3)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->denyVerificationRequest($sender);
        $this->assertCount(3, $sender->getAllVerifications());
    }

    /** @test */
    public function it_returns_accepted_user_verifications_number()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(3)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->denyVerificationRequest($sender);
        $this->assertEquals(2, $sender->getVerifiersCount());
    }

    /** @test */
    public function it_returns_accepted_user_verifications()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(3)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->denyVerificationRequest($sender);
        $this->assertCount(2, $sender->getAcceptedVerifications());
    }

    /** @test */
    public function it_returns_only_accepted_user_verifications()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(4)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->denyVerificationRequest($sender);
        $this->assertCount(2, $sender->getAcceptedVerifications());

        $this->assertCount(1, $recipients[0]->getAcceptedVerifications());
        $this->assertCount(1, $recipients[1]->getAcceptedVerifications());
        $this->assertCount(0, $recipients[2]->getAcceptedVerifications());
        $this->assertCount(0, $recipients[3]->getAcceptedVerifications());
    }

    /** @test */
    public function it_returns_pending_user_verifications()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(3)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $this->assertCount(2, $sender->getPendingVerifications());
    }

    /** @test */
    public function it_returns_denied_user_verifications()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(3)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->denyVerificationRequest($sender);
        $this->assertCount(1, $sender->getDeniedVerifications());
    }

    /** @test */
    public function it_returns_blocked_user_verifications()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(3)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->blockVerification($sender);
        $this->assertCount(1, $sender->getBlockedVerifications());
    }

    /** @test */
    public function it_returns_user_verifiers()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(4)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->denyVerificationRequest($sender);

        $this->assertCount(2, $sender->getVerifiers());
        $this->assertCount(1, $recipients[1]->getVerifiers());
        $this->assertCount(0, $recipients[2]->getVerifiers());
        $this->assertCount(0, $recipients[3]->getVerifiers());

        $this->assertContainsOnlyInstancesOf(User::class, $sender->getVerifiers());
    }

    /** @test */
    public function it_returns_user_verifiers_per_page()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(6)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->denyVerificationRequest($sender);
        $recipients[3]->acceptVerificationRequest($sender);
        $recipients[4]->acceptVerificationRequest($sender);


        $this->assertCount(2, $sender->getVerifiers(2));
        $this->assertCount(4, $sender->getVerifiers(0));
        $this->assertCount(4, $sender->getVerifiers(10));
        $this->assertCount(1, $recipients[1]->getVerifiers());
        $this->assertCount(0, $recipients[2]->getVerifiers());
        $this->assertCount(0, $recipients[5]->getVerifiers(2));

        $this->assertContainsOnlyInstancesOf(User::class, $sender->getVerifiers());
    }

    /** @test */
    public function it_returns_user_verifiers_of_verifiers()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(2)->create();
        $vovs = User::factory()->count(5)->create()->chunk(3);

        foreach ($recipients as $index => $recipient) {
            $sender->verify($recipient, 'Test verification message');
            $recipient->acceptVerificationRequest($sender);

            //add some verifiers to each recipient too
            foreach ($vovs[$index] as $vov) {
                $recipient->verify($vov, 'Test verification message');
                $vov->acceptVerificationRequest($recipient);
            }
        }

        $this->assertCount(2, $sender->getVerifiers());
        $this->assertCount(4, $recipients[0]->getVerifiers());
        $this->assertCount(3, $recipients[1]->getVerifiers());

        $this->assertCount(5, $sender->getVerifiersOfVerifiers());

        $this->assertContainsOnlyInstancesOf(User::class, $sender->getVerifiersOfVerifiers());
    }

    /** @test */
    public function it_returns_user_mutual_verifiers()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(2)->create();
        $vovs = User::factory()->count(5)->create()->chunk(3);

        foreach ($recipients as $index => $recipient) {
            $sender->verify($recipient, 'Test verification message');
            $recipient->acceptVerificationRequest($sender);

            //add some verifiers to each recipient too
            foreach ($vovs[$index] as $vov) {
                $recipient->verify($vov, 'Test verification message');
                $vov->acceptVerificationRequest($recipient);
                $vov->verify($sender, 'Test verification message');
                $sender->acceptVerificationRequest($vov);
            }
        }

        $this->assertCount(3, $sender->getMutualVerifiers($recipients[0]));
        $this->assertCount(3, $recipients[0]->getMutualVerifiers($sender));

        $this->assertCount(2, $sender->getMutualVerifiers($recipients[1]));
        $this->assertCount(2, $recipients[1]->getMutualVerifiers($sender));

        $this->assertContainsOnlyInstancesOf(User::class, $sender->getMutualVerifiers($recipients[0]));
    }

    /** @test */
    public function it_returns_user_mutual_verifiers_per_page()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(2)->create();
        $vovs = User::factory()->count(8)->create()->chunk(5);

        foreach ($recipients as $index => $recipient) {
            $sender->verify($recipient, 'Test verification message');
            $recipient->acceptVerificationRequest($sender);

            //add some verifiers to each recipient too
            foreach ($vovs[$index] as $vov) {
                $recipient->verify($vov, 'Test verification message');
                $vov->acceptVerificationRequest($recipient);
                $vov->verify($sender, 'Test verification message');
                $sender->acceptVerificationRequest($vov);
            }
        }

        $this->assertCount(2, $sender->getMutualVerifiers($recipients[0], 2));
        $this->assertCount(5, $sender->getMutualVerifiers($recipients[0], 0));
        $this->assertCount(5, $sender->getMutualVerifiers($recipients[0], 10));
        $this->assertCount(2, $recipients[0]->getMutualVerifiers($sender, 2));
        $this->assertCount(5, $recipients[0]->getMutualVerifiers($sender, 0));
        $this->assertCount(5, $recipients[0]->getMutualVerifiers($sender, 10));

        $this->assertCount(1, $recipients[1]->getMutualVerifiers($recipients[0], 10));

        $this->assertContainsOnlyInstancesOf(User::class, $sender->getMutualVerifiers($recipients[0], 2));
    }

    /** @test */
    public function it_returns_user_mutual_verifiers_number()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(2)->create();
        $vovs = User::factory()->count(5)->create()->chunk(3);

        foreach ($recipients as $index => $recipient) {
            $sender->verify($recipient, 'Test verification message');
            $recipient->acceptVerificationRequest($sender);

            //add some verifiers to each recipient too
            foreach ($vovs[$index] as $vov) {
                $recipient->verify($vov, 'Test verification message');
                $vov->acceptVerificationRequest($recipient);
                $vov->verify($sender, 'Test verification message');
                $sender->acceptVerificationRequest($vov);
            }
        }

        $this->assertEquals(3, $sender->getMutualVerifiersCount($recipients[0]));
        $this->assertEquals(3, $recipients[0]->getMutualVerifiersCount($sender));

        $this->assertEquals(2, $sender->getMutualVerifiersCount($recipients[1]));
        $this->assertEquals(2, $recipients[1]->getMutualVerifiersCount($sender));
    }
}
