<?php

namespace App\Support\Profile;

use App\Mail\GuestbookEntrySubmitted;
use App\Models\GuestbookEntry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GuestbookReviewNotifier
{
    public function send(GuestbookEntry $entry): bool
    {
        $recipient = trim((string) config('portfolio.review_notification_email'));
        $attemptedAt = now();

        try {
            if ($recipient === '') {
                throw new RuntimeException('The review notification recipient is not configured.');
            }

            Mail::to($recipient)->send(new GuestbookEntrySubmitted($entry));

            $entry->forceFill([
                'notification_status' => 'accepted',
                'notification_attempted_at' => $attemptedAt,
                'notification_accepted_at' => now(),
                'notification_error' => null,
            ])->save();

            return true;
        } catch (Throwable $exception) {
            $error = Str::limit($exception->getMessage(), 2000, '');

            try {
                $entry->forceFill([
                    'notification_status' => 'failed',
                    'notification_attempted_at' => $attemptedAt,
                    'notification_error' => $error,
                ])->save();
            } catch (Throwable $persistenceException) {
                report($persistenceException);
            }

            Log::error('Portfolio review notification failed.', [
                'guestbook_entry_id' => $entry->getKey(),
                'mailer' => config('mail.default'),
                'recipient_domain' => Str::afterLast($recipient, '@'),
                'exception' => $exception::class,
                'message' => $error,
            ]);
            report($exception);

            return false;
        }
    }
}
