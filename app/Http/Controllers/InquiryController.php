<?php

namespace App\Http\Controllers;

use App\Mail\PrivateInquiry;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;
use Illuminate\Validation\Rule;
use Throwable;

class InquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $success = 'Your message has been submitted for delivery.';
        if ($request->filled('website')) {
            return back()->with('inquiry_success', $success);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'category' => ['required', Rule::in(['Software development', 'Network infrastructure', 'Cybersecurity', 'Systems integration', 'Research collaboration', 'IT consulting'])],
            'message' => ['required', 'string', 'min:12', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        $mailer = (string) config('mail.default');
        $transport = config("mail.mailers.{$mailer}.transport");
        $recipient = Profile::query()->value('email') ?: config('portfolio.review_notification_email');
        $sender = config('mail.from.address');

        if (! in_array($transport, ['smtp', 'sendmail', 'ses', 'ses-v2', 'postmark', 'resend', 'mailgun'], true)
            || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)
            || ! filter_var($sender, FILTER_VALIDATE_EMAIL)
            || $sender === 'hello@example.com') {
            return back()->withErrors(['inquiry' => 'The inquiry service is unavailable. Please use the email option instead.'])->withInput();
        }

        try {
            $sent = Mail::to($recipient)->send(new PrivateInquiry($data));
            if ($sent === null && ! (app()->environment('testing') && Mail::getFacadeRoot() instanceof MailFake)) {
                throw new \RuntimeException('Mail was not accepted.');
            }
        } catch (Throwable) {
            return back()->withErrors(['inquiry' => 'Your message could not be sent. Please use the email option instead.'])->withInput();
        }

        return back()->with('inquiry_success', $success);
    }
}
