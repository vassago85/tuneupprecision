<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent whenever the "Site questionnaire" Filament page is saved. Delivers a
 * human-readable dump of every answered question to Dirk (ADMIN_EMAIL) and
 * Paul so the homepage rewrite can start from real answers.
 */
class SiteQuestionnaireSubmitted extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $answers  Raw form state keyed by q1_priority, q2_tagline, ...
     */
    public function __construct(public array $answers) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Site questionnaire — new answers from Dirk',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.site-questionnaire-submitted',
            with: [
                'sections' => $this->buildSections(),
                'submittedAt' => now()->format('D d M Y H:i'),
            ],
        );
    }

    /**
     * Turn the raw form keys into a list of sections the Blade view can loop
     * over. Each section is [heading, list-of-[label, value]]. Empty answers
     * are dropped so the email is not padded with blanks.
     *
     * @return list<array{heading: string, rows: list<array{label: string, value: string}>}>
     */
    private function buildSections(): array
    {
        $labels = $this->optionLabels();

        $definition = [
            [
                'heading' => '1 · Website priority',
                'fields' => [
                    'q1_priority' => 'Priority',
                    'q1_priority_other' => 'Something else',
                ],
            ],
            [
                'heading' => '2 · Homepage tagline',
                'fields' => [
                    'q2_tagline' => 'Chosen line',
                    'q2_tagline_own' => 'Own line',
                ],
            ],
            [
                'heading' => '3 · Primary visitor',
                'fields' => [
                    'q3_primary_visitor' => 'Who the homepage talks to first',
                ],
            ],
            [
                'heading' => '4 · Course mapping',
                'fields' => [
                    'q4_beginner_course' => 'Never shot long range',
                    'q4_hunter_course' => 'Hunter / ballistics',
                    'q4_prs_starter_course' => 'Wants to start PRS',
                    'q4_prs_existing_course' => 'Already shoots PRS',
                    'q4_reloading_course' => 'Wants to learn reloading',
                    'q4_private_course' => 'Wants one-on-one',
                    'q4_handgun_course' => 'Wants handgun training',
                    'q4_handgun_placement' => 'Handgun placement on homepage',
                ],
            ],
            [
                'heading' => '5 · Long range: one card or two',
                'fields' => [
                    'q5_long_range' => 'Choice',
                    'q5_long_range_own' => 'Own preference',
                ],
            ],
            [
                'heading' => '6 · Day timeline',
                'fields' => [
                    'q6_timeline' => 'Choice',
                ],
            ],
            [
                'heading' => '7 · Proof we can publish',
                'fields' => [
                    'q7_more_quotes' => 'More quotes to add',
                    'q7_name_style' => 'How shooters are credited',
                    'q7_photos_available' => 'Photos available',
                    'q7_photo_consent' => 'Photo consent',
                    'q7_real_results' => 'Real results to claim',
                    'q7_video_available' => 'Video available',
                ],
            ],
            [
                'heading' => '8 · Prices and kit',
                'fields' => [
                    'q8_handgun_publish_price' => 'Publish a handgun price?',
                    'q8_handgun_price' => 'Handgun price (R)',
                    'q8_one_on_one_pricing' => 'One-on-one pricing',
                    'q8_one_on_one_start_price' => 'One-on-one starting price (R)',
                    'q8_off_homepage_prices' => 'Prices to keep off the homepage',
                    'q8_per_course_notes' => 'Per-course kit notes',
                ],
            ],
            [
                'heading' => '9 · Booking flow',
                'fields' => [
                    'q9_booking_flow' => 'Preferred flow',
                    'q9_whatsapp_number' => 'WhatsApp number',
                    'q9_whatsapp_placement' => 'Where the WhatsApp button sits',
                ],
            ],
            [
                'heading' => '10 · Menu "Book now" target',
                'fields' => [
                    'q10_book_now_target' => 'Target',
                ],
            ],
            [
                'heading' => '11 · Instructor intro',
                'fields' => [
                    'q11_bio_edits' => 'Bio edits',
                    'q11_why_it_matters' => 'Why it matters to a student',
                    'q11_photo_choice' => 'Photo',
                ],
            ],
            [
                'heading' => '12 · The range',
                'fields' => [
                    'q12_range_publish' => 'What a visitor must know',
                    'q12_range_do_not_publish' => 'What we must NOT publish',
                ],
            ],
            [
                'heading' => '13 · Shop scope',
                'fields' => [
                    'q13_shop_scope' => 'Choice',
                    'q13_specific_gear' => 'Specific gear to sell soon',
                ],
            ],
            [
                'heading' => '14 · Off-limits',
                'fields' => [
                    'q14_do_not_change' => 'Do not change',
                ],
            ],
            [
                'heading' => '15 · Who writes the new lines',
                'fields' => [
                    'q15_who_writes' => 'Choice',
                ],
            ],
        ];

        $sections = [];

        foreach ($definition as $section) {
            $rows = [];

            foreach ($section['fields'] as $key => $label) {
                $raw = $this->answers[$key] ?? null;
                $display = $this->formatValue($key, $raw, $labels);

                if ($display === null) {
                    continue;
                }

                $rows[] = ['label' => $label, 'value' => $display];
            }

            if ($rows !== []) {
                $sections[] = ['heading' => $section['heading'], 'rows' => $rows];
            }
        }

        return $sections;
    }

    /**
     * Turn a raw form value into a human-readable string, or null if the field
     * should be omitted from the email.
     *
     * @param  array<string, array<string, string>>  $labels
     */
    private function formatValue(string $key, mixed $raw, array $labels): ?string
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return null;
        }

        if (is_bool($raw)) {
            return $raw ? 'Yes' : 'No';
        }

        $stringValue = (string) $raw;

        if (isset($labels[$key][$stringValue])) {
            return $labels[$key][$stringValue];
        }

        return $stringValue;
    }

    /**
     * Radio/Select label lookup keyed by form field. Mirrors the options
     * declared in SiteQuestionnaire::form(). Kept alongside so the email
     * reads "More course bookings" instead of "bookings".
     *
     * @return array<string, array<string, string>>
     */
    private function optionLabels(): array
    {
        $courses = [
            'zero_to_first_steel' => 'Zero to First Steel',
            'applied_long_range' => 'Applied Long Range',
            'prs_match_skills' => 'PRS Match Skills',
            'precision_reloading' => 'Precision Reloading',
            'handgun_fundamentals' => 'Handgun Fundamentals',
            'one_on_one' => 'One-on-one coaching (quoted)',
            'none' => "Don't offer this",
        ];

        return [
            'q1_priority' => [
                'bookings' => 'More course bookings',
                'gear' => 'Sell more gear',
                'brand' => 'Build the Tune Up name (courses and merch together)',
                'other' => 'Something else',
            ],
            'q2_tagline' => [
                'keep' => 'Keep "Dial in your distance."',
                'precision_process' => '"Precision is a process."',
                'shoot_farther' => '"Shoot farther. Shoot better. Know why you hit — and why you missed."',
                'own' => 'My own line',
            ],
            'q3_primary_visitor' => [
                'beginner' => 'Complete beginner',
                'hunter' => 'Hunter who wants to shoot farther',
                'rifle_shooter' => 'Rifle shooter new to precision',
                'prs_starter' => 'Someone who wants to start PRS',
                'prs_existing' => 'Someone who already shoots PRS',
                'reloading' => 'Someone who wants to learn reloading',
                'private' => 'Someone who wants private coaching',
            ],
            'q4_beginner_course' => $courses,
            'q4_hunter_course' => $courses,
            'q4_prs_starter_course' => $courses,
            'q4_prs_existing_course' => $courses,
            'q4_reloading_course' => $courses,
            'q4_private_course' => $courses,
            'q4_handgun_course' => $courses,
            'q4_handgun_placement' => [
                'main' => 'Main course on the homepage',
                'extra' => 'Smaller extra / secondary offer',
            ],
            'q5_long_range' => [
                'both' => 'Show both on the homepage',
                'lead_zero' => 'Lead with Zero to First Steel and mention Applied as the next step',
                'other' => 'Custom preference',
            ],
            'q6_timeline' => [
                'same_shape' => 'Same shape for every course',
                'per_course' => 'Each course is different — Dirk will send the real order',
                'none' => 'No timeline on the site',
            ],
            'q7_name_style' => [
                'full' => 'Full name',
                'first' => 'First name only',
                'anonymous' => 'Anonymous',
                'mixed' => 'Depends on the shooter',
            ],
            'q7_photo_consent' => [
                'yes_all' => 'Yes, for everyone',
                'yes_some' => 'Only for some — Dirk will flag which',
                'no' => 'No faces — crop or blur',
                'unknown' => 'Not sure yet',
            ],
            'q7_video_available' => [
                '1' => 'Yes', '0' => 'No', 'true' => 'Yes', 'false' => 'No',
            ],
            'q8_handgun_publish_price' => [
                '1' => 'Yes', '0' => 'No', 'true' => 'Yes', 'false' => 'No',
            ],
            'q8_one_on_one_pricing' => [
                'quoted' => 'Keep "quoted"',
                'starting' => 'Show a starting price',
            ],
            'q9_booking_flow' => [
                'enquire' => 'Keep enquire — Dirk confirms the seat',
                'card' => 'Take card payment on the site',
                'whatsapp_too' => 'Also offer WhatsApp',
            ],
            'q9_whatsapp_placement' => [
                'every_course' => 'Next to every course',
                'homepage_only' => 'Once on the homepage / in the header',
                'contact_only' => 'Only on the contact page',
            ],
            'q10_book_now_target' => [
                'courses' => 'The courses list',
                'calendar' => 'The calendar',
                'contact' => 'The contact form',
            ],
            'q11_photo_choice' => [
                'keep' => 'Keep the current photo',
                'coaching' => 'Dirk has a coaching photo to send',
                'new' => 'Take a new photo',
            ],
            'q13_shop_scope' => [
                'merch_only' => 'Leave it as merch for now',
                'specific_gear' => 'There is specific gear to sell soon',
            ],
            'q15_who_writes' => [
                'draft_approve' => 'Paul drafts, Dirk approves',
                'i_send' => 'Dirk sends the final headlines',
            ],
        ];
    }
}
