<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactEnquiry;
use App\Support\ContactLink;
use App\Support\OwnerInbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewContract;

class ContactController extends Controller
{
    /** Minimum seconds a real human takes to fill and submit the form. */
    private const int MIN_SUBMIT_SECONDS = 3;

    public function create(Request $request): ViewContract|RedirectResponse
    {
        // Old enquire links used ?subject=. Fold them onto the one contact URL.
        if ($request->query->has('subject')) {
            return new RedirectResponse(ContactLink::url($request->query('subject')), 301);
        }

        return View::make('contact', [
            'subject' => '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: the "company" field is hidden from humans. If it's filled,
        // silently pretend success so bots get no useful signal.
        if (filled($request->input('company'))) {
            return $this->done();
        }

        // Timing trap: submissions faster than a human are almost certainly bots.
        $renderedAt = (int) $request->input('ts', 0);
        if ($renderedAt > 0 && (now()->timestamp - $renderedAt) < self::MIN_SUBMIT_SECONDS) {
            return $this->done();
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $enquiry = [
            'name' => trim($validated['name']),
            'email' => mb_strtolower(trim($validated['email'])),
            'phone' => filled($validated['phone'] ?? null) ? trim((string) $validated['phone']) : null,
            'subject' => trim($validated['subject']),
            'message' => trim($validated['message']),
        ];

        Mail::to(OwnerInbox::email())->queue(new ContactEnquiry($enquiry, false));
        Mail::to($enquiry['email'])->queue(new ContactEnquiry($enquiry, true));

        return $this->done();
    }

    private function done(): RedirectResponse
    {
        return redirect()->route('contact.create')->with('contact_status', 'success');
    }
}
