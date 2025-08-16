<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerificationsGroupsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_add_a_verified_user_to_a_group()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->verify($recipient, 'Test verification message');
        $recipient->acceptVerificationRequest($sender);

        $this->assertTrue((bool) $recipient->groupVerification($sender, 'text'));
        $this->assertTrue((bool) $sender->groupVerification($recipient, 'phone'));

        // it only adds a verifier to a group once
        $this->assertFalse((bool) $sender->groupVerification($recipient, 'phone'));

        // expect that users have been attached to specified groups
        $this->assertCount(1, $sender->getVerifiers(0, 'phone'));
        $this->assertCount(1, $recipient->getVerifiers(0, 'text'));

        $this->assertEquals($recipient->id, $sender->getVerifiers(0, 'phone')->first()->id);
        $this->assertEquals($sender->id, $recipient->getVerifiers(0, 'text')->first()->id);
    }

    /** @test */
    public function user_cannot_add_a_non_verified_user_to_a_group()
    {
        $sender = User::factory()->create();
        $stranger = User::factory()->create();

        $this->assertFalse((bool) $sender->groupVerification($stranger, 'phone'));
        $this->assertCount(0, $sender->getVerifiers(0, 'phone'));
    }

    /** @test */
    public function user_can_remove_a_verifier_from_group()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->verify($recipient, 'Test verification message');
        $recipient->acceptVerificationRequest($sender);

        $recipient->groupVerification($sender, 'text');
        $recipient->groupVerification($sender, 'phone');

        $this->assertEquals(1, $recipient->ungroupVerification($sender, 'text'));

        // expect that verifier has been removed from text but not phone
        $this->assertCount(0, $recipient->getVerifiers(0, 'text'));
        $this->assertCount(1, $recipient->getVerifiers(0, 'phone'));
    }

    /** @test */
    public function user_cannot_remove_a_non_existing_verifier_from_group()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $recipient2 = User::factory()->create();

        $sender->verify($recipient, 'Test verification message');

        $this->assertEquals(0, $recipient->ungroupVerification($sender, 'text'));
        $this->assertEquals(0, $recipient2->ungroupVerification($sender, 'text'));
    }

    /** @test */
    public function user_can_remove_a_verifier_from_all_groups()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $sender->verify($recipient, 'Test verification message');
        $recipient->acceptVerificationRequest($sender);

        $sender->groupVerification($recipient, 'phone');
        $sender->groupVerification($recipient, 'text');

        $sender->ungroupVerification($recipient);

        $this->assertCount(0, $sender->getVerifiers(0, 'phone'));
        $this->assertCount(0, $sender->getVerifiers(0, 'text'));
    }

    /** @test */
    public function it_returns_verifiers_of_a_group()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(10)->create();

        foreach ($recipients as $key => $recipient) {
            $sender->verify($recipient, 'Test verification message');
            $recipient->acceptVerificationRequest($sender);

            if ($key % 2 === 0) {
                $sender->groupVerification($recipient, 'phone');
            }
        }

        $this->assertCount(5, $sender->getVerifiers(0, 'phone'));
        $this->assertCount(10, $sender->getVerifiers());
    }

    /** @test */
    public function it_returns_all_user_verifications_by_group()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(5)->create();

        foreach ($recipients as $key => $recipient) {
            $sender->verify($recipient, 'Test verification message');

            if ($key < 4) {
                $recipient->acceptVerificationRequest($sender);
                if ($key < 3) {
                    $sender->groupVerification($recipient, 'text');
                } else {
                    $sender->groupVerification($recipient, 'phone');
                }
            } else {
                $recipient->denyVerificationRequest($sender);
            }
        }

        //Assertions
        $this->assertCount(3, $sender->getAllVerifications('text'));
        $this->assertCount(1, $sender->getAllVerifications('phone'));
        $this->assertCount(0, $sender->getAllVerifications('cam'));
        $this->assertCount(5, $sender->getAllVerifications('whatever'));
    }

    /** @test */
    public function it_returns_accepted_user_verifications_by_group()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(4)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
        }

        $recipients[0]->acceptVerificationRequest($sender);
        $recipients[1]->acceptVerificationRequest($sender);
        $recipients[2]->denyVerificationRequest($sender);

        $sender->groupVerification($recipients[0], 'phone');
        $sender->groupVerification($recipients[1], 'phone');

        $this->assertCount(2, $sender->getAcceptedVerifications('phone'));
    }

    /** @test */
    public function it_returns_accepted_user_verifications_number_by_group()
    {
        $sender = User::factory()->create();
        $recipients = User::factory()->count(5)->create();

        foreach ($recipients as $recipient) {
            $sender->verify($recipient, 'Test verification message');
            $recipient->acceptVerificationRequest($sender);
            $sender->groupVerification($recipient, 'text');
        }

        //Assertions
        $this->assertEquals(5, $sender->getVerifiersCount('text'));
        $this->assertEquals(0, $sender->getVerifiersCount('phone'));
        $this->assertEquals(0, $recipients[0]->getVerifiersCount('text'));
        $this->assertEquals(0, $recipients[0]->getVerifiersCount('phone'));
    }

    /** @test */
    public function it_returns_user_verifiers_by_group_per_page()
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

        $sender->groupVerification($recipients[0], 'text');
        $sender->groupVerification($recipients[1], 'text');
        $sender->groupVerification($recipients[3], 'text');
        $sender->groupVerification($recipients[4], 'text');

        $sender->groupVerification($recipients[0], 'cam');
        $sender->groupVerification($recipients[3], 'cam');

        $sender->groupVerification($recipients[4], 'phone');

        //Assertions
        $this->assertCount(2, $sender->getVerifiers(2, 'text'));
        $this->assertCount(4, $sender->getVerifiers(0, 'text'));
        $this->assertCount(4, $sender->getVerifiers(10, 'text'));

        $this->assertCount(2, $sender->getVerifiers(0, 'cam'));
        $this->assertCount(1, $sender->getVerifiers(1, 'cam'));

        $this->assertCount(1, $sender->getVerifiers(0, 'phone'));

        $this->assertContainsOnlyInstancesOf(User::class, $sender->getVerifiers(0, 'text'));
    }
}
