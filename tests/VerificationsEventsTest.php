<?php

namespace Tests;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;

class VerificationsEventsTest extends TestCase
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
    public function verification_request_is_sent(): void
    {
        Event::fake();

        $this->sender->verify($this->recipient, 'Test verification message');

        Event::assertDispatched('acq.verifications.sent');
    }

    #[Test]
    public function verification_request_is_accepted(): void
    {
        $this->sender->verify($this->recipient, 'Test verification message');

        Event::fake();

        $this->recipient->acceptVerificationRequest($this->sender);

        Event::assertDispatched('acq.verifications.accepted');
    }

    #[Test]
    public function verification_request_is_denied(): void
    {
        $this->sender->verify($this->recipient, 'Test verification message');

        Event::fake();

        $this->recipient->denyVerificationRequest($this->sender);

        Event::assertDispatched('acq.verifications.denied');
    }

    #[Test]
    public function verifier_is_blocked(): void
    {
        $this->sender->verify($this->recipient, 'Test verification message');
        $this->recipient->acceptVerificationRequest($this->sender);

        Event::fake();

        $this->recipient->blockVerification($this->sender);

        Event::assertDispatched('acq.verifications.blocked');
    }

    #[Test]
    public function verifier_is_unblocked(): void
    {
        $this->sender->verify($this->recipient, 'Test verification message');
        $this->recipient->acceptVerificationRequest($this->sender);
        $this->recipient->blockVerification($this->sender);

        Event::fake();

        $this->recipient->unblockVerification($this->sender);

        Event::assertDispatched('acq.verifications.unblocked');
    }

    #[Test]
    public function verification_is_cancelled(): void
    {
        $this->sender->verify($this->recipient, 'Test verification message');
        $this->recipient->acceptVerificationRequest($this->sender);

        Event::fake();

        $this->recipient->unverify($this->sender);

        Event::assertDispatched('acq.verifications.cancelled');
    }
}
