<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;

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

	/** @test */
	public function verification_request_is_sent()
	{
		Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.sent', Mockery::any()]);

		$this->sender->verify($this->recipient, 'Test verification message');
	}

	/** @test */
	public function verification_request_is_accepted()
	{
		$this->sender->verify($this->recipient, 'Test verification message');
		Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.accepted', Mockery::any()]);

		$this->recipient->acceptVerificationRequest($this->sender);
	}

	/** @test */
	public function verification_request_is_denied()
	{
		$this->sender->verify($this->recipient, 'Test verification message');
		Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.denied', Mockery::any()]);

		$this->recipient->denyVerificationRequest($this->sender);
	}

	/** @test */
	public function verifier_is_blocked()
	{
		$this->sender->verify($this->recipient, 'Test verification message');
		$this->recipient->acceptVerificationRequest($this->sender);
		Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.blocked', Mockery::any()]);

		$this->recipient->blockVerification($this->sender);
	}

	/** @test */
	public function verifier_is_unblocked()
	{
		$this->sender->verify($this->recipient, 'Test verification message');
		$this->recipient->acceptVerificationRequest($this->sender);
		$this->recipient->blockVerification($this->sender);
		Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.unblocked', Mockery::any()]);

		$this->recipient->unblockVerification($this->sender);
	}

	/** @test */
	public function verification_is_cancelled()
	{
		$this->sender->verify($this->recipient, 'Test verification message');
		$this->recipient->acceptVerificationRequest($this->sender);
		Event::shouldReceive('dispatch')->once()->withArgs(['acq.verifications.cancelled', Mockery::any()]);

		$this->recipient->unverify($this->sender);
	}
}
