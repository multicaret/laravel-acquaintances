<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

class VerificationsEventsTest extends TestCase
{
    use RefreshDatabase;

    protected $sender;
    protected $recipient;

    public function setUp(): void
    {
        parent::setUp();

        $this->sender = User::factory()->create();
        $this->recipient = User::factory()->create();
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    #[Group('verificationevents')]
    public function verification_request_is_sent()
    {
        Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.sent', Mockery::any()]);

        $this->sender->verify($this->recipient, 'Test verification message');
    }

    #[Test]
    #[Group('verificationevents')]
    public function verification_request_is_accepted()
    {
        $verification = $this->sender->verify($this->recipient, 'Test verification message');
        Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.accepted', Mockery::any()]);

        $this->recipient->acceptVerificationRequest($verification->id);
    }

    #[Test]
    #[Group('verificationevents')]
    public function verification_request_is_denied()
    {
        $verification = $this->sender->verify($this->recipient, 'Test verification message');
        Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.denied', Mockery::any()]);

        $this->recipient->denyVerificationRequest($verification->id);
    }

    #[Test]
    #[Group('verificationevents')]
    public function verification_is_cancelled()
    {
        $verification = $this->sender->verify($this->recipient, 'Test verification message');
        $this->recipient->acceptVerificationRequest($verification->id);
        Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.cancelled', Mockery::any()]);

        $this->sender->unverify($this->recipient, $verification->id);
    }

    #[Test]
    #[Group('verificationevents')]
    public function multiple_verification_events_are_dispatched()
    {
        Event::shouldReceive('dispatch')->times(3)->withArgs(['acq.verifications.sent', Mockery::any()]);

        // Send multiple verifications - each should trigger an event
        $this->sender->verify($this->recipient, 'First verification');
        $this->sender->verify($this->recipient, 'Second verification');
        $this->sender->verify($this->recipient, 'Third verification');
    }

    #[Test]
    #[Group('verificationevents')]
    public function events_are_dispatched_for_different_verification_statuses()
    {
        $verification1 = $this->sender->verify($this->recipient, 'First verification');
        $verification2 = $this->sender->verify($this->recipient, 'Second verification');
        $verification3 = $this->sender->verify($this->recipient, 'Third verification');

        Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.accepted', Mockery::any()]);
        Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.denied', Mockery::any()]);
        Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.cancelled', Mockery::any()]);

        // Different actions on different verifications
        $this->recipient->acceptVerificationRequest($verification1->id);
        $this->recipient->denyVerificationRequest($verification2->id);
        $this->sender->unverify($this->recipient, $verification3->id);
    }

    #[Test]
    #[Group('verificationevents')]
    public function verification_events_with_groups()
    {
        Event::shouldReceive('dispatch')->times(2)->withArgs(['acq.verifications.sent', Mockery::any()]);

        $verification1 = $this->sender->verify($this->recipient, 'Family verification', 'family');
        $verification2 = $this->sender->verify($this->recipient, 'Work verification', 'work');

        Event::shouldReceive('dispatch')->times(2)->withArgs(['acq.verifications.accepted', Mockery::any()]);

        $this->recipient->acceptVerificationRequest($verification1->id);
        $this->recipient->acceptVerificationRequest($verification2->id);
    }
}
