<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SubscriberStatus;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewContract;

class NewsletterController extends Controller
{
    /** Minimum seconds a real human takes to fill and submit the form. */
    private const int MIN_SUBMIT_SECONDS = 2;

    public function subscribe(Request $request): RedirectResponse
    {
        // Honeypot: the "company" field is hidden from humans. If it's filled,
        // silently pretend success so bots get no useful signal.
        if (filled($request->input('company'))) {
            return $this->done('success');
        }

        // Timing trap: submissions faster than a human are almost certainly bots.
        $renderedAt = (int) $request->input('ts', 0);
        if ($renderedAt > 0 && (now()->timestamp - $renderedAt) < self::MIN_SUBMIT_SECONDS) {
            return $this->done('success');
        }

        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $email = mb_strtolower(trim($validated['email']));

        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $email]);
        $subscriber->name = $validated['name'] ?? $subscriber->name;
        $subscriber->status = SubscriberStatus::Subscribed;
        $subscriber->subscribed_at ??= now();
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        return $this->done('success');
    }

    public function unsubscribe(string $token): ViewContract
    {
        $subscriber = NewsletterSubscriber::query()->where('token', $token)->first();

        if ($subscriber !== null && $subscriber->status !== SubscriberStatus::Unsubscribed) {
            $subscriber->update([
                'status' => SubscriberStatus::Unsubscribed,
                'unsubscribed_at' => now(),
            ]);
        }

        return View::make('newsletter.unsubscribed', [
            'found' => $subscriber !== null,
            'email' => $subscriber?->email,
        ]);
    }

    private function done(string $status): RedirectResponse
    {
        return redirect(url('/').'#newsletter')->with('newsletter_status', $status);
    }
}
