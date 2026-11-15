<?php

namespace Tests\Feature;

use App\Mail\PrivateInquiry;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PrivateInquiryTest extends TestCase
{
    use RefreshDatabase;

    private function inquiry(): array
    {
        return ['name' => 'Test Visitor', 'email' => 'visitor@example.test', 'category' => 'Cybersecurity', 'message' => 'Please contact me about a project.', 'website' => ''];
    }

    public function test_inquiry_is_sent_privately_and_success_is_shared(): void
    {
        $this->withoutVite();
        config(['mail.default' => 'smtp', 'mail.from.address' => 'owner@example.test']);
        Mail::fake();
        $recipient = Profile::query()->value('email') ?: config('portfolio.review_notification_email');

        $this->from('/')->post('/inquiry', $this->inquiry())
            ->assertRedirect('/')
            ->assertSessionHas('inquiry_success', 'Your message has been submitted for delivery.');
        Mail::assertSent(PrivateInquiry::class, fn (PrivateInquiry $mail) => $mail->hasTo($recipient) && $mail->inquiry['email'] === 'visitor@example.test' && $mail->inquiry['message'] === $this->inquiry()['message']);
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('flash.inquiry_success', 'Your message has been submitted for delivery.'));
        $this->assertDatabaseCount('guestbook_entries', 0);
    }

    public function test_log_transport_is_rejected_and_no_mail_is_sent(): void
    {
        config(['mail.default' => 'log']);
        Mail::fake();
        $this->post('/inquiry', $this->inquiry())->assertSessionHasErrors('inquiry');
        Mail::assertNothingSent();
    }

    public function test_invalid_input_and_honeypot_are_not_sent(): void
    {
        config(['mail.default' => 'smtp', 'mail.from.address' => 'owner@example.test']);
        Mail::fake();
        $this->post('/inquiry', [...$this->inquiry(), 'email' => 'bad', 'category' => 'Unknown', 'message' => 'short'])->assertSessionHasErrors(['email', 'category', 'message']);
        $this->post('/inquiry', [...$this->inquiry(), 'website' => 'spam'])->assertSessionHas('inquiry_success');
        Mail::assertNothingSent();
    }

    public function test_transport_failure_returns_a_generic_error(): void
    {
        config(['mail.default' => 'smtp', 'mail.from.address' => 'owner@example.test']);
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('secret transport details'));
        $this->post('/inquiry', $this->inquiry())
            ->assertSessionHasErrors(['inquiry' => 'Your message could not be sent. Please use the email option instead.']);
    }
}
