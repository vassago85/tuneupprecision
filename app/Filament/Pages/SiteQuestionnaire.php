<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Mail\SiteQuestionnaireSubmitted;
use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Throwable;
use UnitEnum;

/**
 * "Site questionnaire" admin page — Dirk answers 15 questions about
 * positioning, courses, pricing and proof so we can rewrite the homepage
 * from real answers instead of guesses. Answers are stored in the settings
 * table (one row per question) and emailed to the ADMIN_EMAIL address plus
 * paul@charsley.co.za on save.
 */
class SiteQuestionnaire extends Page
{
    protected string $view = 'filament.pages.site-questionnaire';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Site questionnaire';

    protected static ?string $title = 'Site questionnaire';

    /** Second recipient (in addition to ADMIN_EMAIL) copied on every save. */
    private const NOTIFY_CC = 'paul@charsley.co.za';

    /**
     * Every settings key this page reads/writes. Keeping the list here means
     * mount() and save() can't drift out of sync when a new question is added.
     *
     * @var list<string>
     */
    private const KEYS = [
        // 1 — Website priority
        'q1_priority',
        'q1_priority_other',

        // 2 — Tagline
        'q2_tagline',
        'q2_tagline_own',

        // 3 — Primary visitor
        'q3_primary_visitor',

        // 4 — Course mapping (per persona)
        'q4_beginner_course',
        'q4_hunter_course',
        'q4_prs_starter_course',
        'q4_prs_existing_course',
        'q4_reloading_course',
        'q4_private_course',
        'q4_handgun_course',
        'q4_handgun_placement',

        // 5 — Long range cards
        'q5_long_range',
        'q5_long_range_own',

        // 6 — Day timeline
        'q6_timeline',

        // 7 — Proof
        'q7_more_quotes',
        'q7_name_style',
        'q7_photos_available',
        'q7_photo_consent',
        'q7_real_results',
        'q7_video_available',

        // 8 — Prices & kit
        'q8_handgun_publish_price',
        'q8_handgun_price',
        'q8_one_on_one_pricing',
        'q8_one_on_one_start_price',
        'q8_off_homepage_prices',
        'q8_per_course_notes',

        // 9 — Booking flow
        'q9_booking_flow',
        'q9_whatsapp_number',
        'q9_whatsapp_placement',

        // 10 — Menu "Book now" target
        'q10_book_now_target',

        // 11 — Instructor intro
        'q11_bio_edits',
        'q11_why_it_matters',
        'q11_photo_choice',

        // 12 — Range
        'q12_range_publish',
        'q12_range_do_not_publish',

        // 13 — Shop scope
        'q13_shop_scope',
        'q13_specific_gear',

        // 14 — Off-limits
        'q14_do_not_change',

        // 15 — Who writes the new lines
        'q15_who_writes',
    ];

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        // Pre-fill from any previously saved answers so Dirk can iterate over
        // several sittings without losing what he already typed.
        $initial = [];

        foreach (self::KEYS as $key) {
            $initial[$key] = Setting::get("questionnaire.{$key}");
        }

        $this->form->fill($initial);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('How to use this')
                    ->description('Short answers are fine. Answers are saved as you click "Save answers" and emailed to Paul so he can turn them into the new homepage. You can come back and change anything later.')
                    ->collapsible()
                    ->schema([]),

                Section::make('1 · What should the website be for over the next few months?')
                    ->schema([
                        Radio::make('q1_priority')
                            ->hiddenLabel()
                            ->options([
                                'bookings' => 'More course bookings',
                                'gear' => 'Sell more gear',
                                'brand' => 'Build the Tune Up name (courses and merch together)',
                                'other' => 'Something else',
                            ]),
                        TextInput::make('q1_priority_other')
                            ->label('Something else — what?')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('q1_priority') === 'other'),
                    ]),

                Section::make('2 · Which line should someone remember?')
                    ->description('Current homepage headline: "Dial in your distance."')
                    ->schema([
                        Radio::make('q2_tagline')
                            ->hiddenLabel()
                            ->options([
                                'keep' => 'Keep "Dial in your distance."',
                                'precision_process' => '"Precision is a process."',
                                'shoot_farther' => '"Shoot farther. Shoot better. Know why you hit — and why you missed."',
                                'own' => 'My own line',
                            ]),
                        TextInput::make('q2_tagline_own')
                            ->label('Your own line')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('q2_tagline') === 'own'),
                    ]),

                Section::make('3 · Who is the homepage talking to first?')
                    ->description('Pick one primary visitor. We can still mention the others, but one has to lead.')
                    ->schema([
                        Radio::make('q3_primary_visitor')
                            ->hiddenLabel()
                            ->options([
                                'beginner' => 'Complete beginner',
                                'hunter' => 'Hunter who wants to shoot farther',
                                'rifle_shooter' => 'Rifle shooter new to precision',
                                'prs_starter' => 'Someone who wants to start PRS',
                                'prs_existing' => 'Someone who already shoots PRS',
                                'reloading' => 'Someone who wants to learn reloading',
                                'private' => 'Someone who wants private coaching',
                            ]),
                    ]),

                Section::make('4 · Which course should each person book?')
                    ->description('For each row, pick the course you want to send them to — or "Don\'t offer this".')
                    ->columns(2)
                    ->schema([
                        Select::make('q4_beginner_course')
                            ->label('I have never shot long range')
                            ->options(self::courseOptions()),
                        Select::make('q4_hunter_course')
                            ->label('I hunt and want to understand my rifle and ballistics')
                            ->options(self::courseOptions()),
                        Select::make('q4_prs_starter_course')
                            ->label('I want to get into PRS')
                            ->options(self::courseOptions()),
                        Select::make('q4_prs_existing_course')
                            ->label('I already shoot PRS and want to improve')
                            ->options(self::courseOptions()),
                        Select::make('q4_reloading_course')
                            ->label('I want to learn precision reloading')
                            ->options(self::courseOptions()),
                        Select::make('q4_private_course')
                            ->label('I want one-on-one coaching')
                            ->options(self::courseOptions()),
                        Select::make('q4_handgun_course')
                            ->label('I want handgun training')
                            ->options(self::courseOptions()),
                        Radio::make('q4_handgun_placement')
                            ->label('Where does handgun live on the homepage?')
                            ->options([
                                'main' => 'Main course on the homepage',
                                'extra' => 'Smaller extra / secondary offer',
                            ]),
                    ]),

                Section::make('5 · Long range: one card or two?')
                    ->description('We have two levels — Zero to First Steel, then Applied Long Range.')
                    ->schema([
                        Radio::make('q5_long_range')
                            ->hiddenLabel()
                            ->options([
                                'both' => 'Show both on the homepage',
                                'lead_zero' => 'Lead with Zero to First Steel and mention Applied as the next step',
                                'other' => 'My preference below',
                            ]),
                        TextInput::make('q5_long_range_own')
                            ->label('Your preference')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('q5_long_range') === 'other'),
                    ]),

                Section::make('6 · What does a training day actually look like?')
                    ->description('Only useful on the site if it is true.')
                    ->schema([
                        Radio::make('q6_timeline')
                            ->hiddenLabel()
                            ->options([
                                'same_shape' => 'Same shape for every course (briefing → theory → dry fire → live fire → data → assessment)',
                                'per_course' => 'Each course is different — I will send the real order for each',
                                'none' => 'Do not put a timeline on the site',
                            ]),
                    ]),

                Section::make('7 · Proof we can publish')
                    ->description('The homepage already shows quotes. This is where we make that section stronger.')
                    ->columns(2)
                    ->schema([
                        Textarea::make('q7_more_quotes')
                            ->label('More quotes I can send (paste them here, one per line, include the shooter and the course)')
                            ->rows(4)
                            ->columnSpanFull(),
                        Radio::make('q7_name_style')
                            ->label('How should shooters be credited?')
                            ->options([
                                'full' => 'Full name',
                                'first' => 'First name only',
                                'anonymous' => 'Anonymous',
                                'mixed' => 'Depends on the shooter — I will mark each one',
                            ]),
                        Toggle::make('q7_video_available')
                            ->label('I have short video I can send (coaching / hits / matches)'),
                        Textarea::make('q7_photos_available')
                            ->label('Photos I can send')
                            ->placeholder('e.g. coaching a student, steel at 800 m, the bench, the range, barricades')
                            ->rows(3),
                        Radio::make('q7_photo_consent')
                            ->label('Do we have permission to show faces?')
                            ->options([
                                'yes_all' => 'Yes, for everyone in the photos',
                                'yes_some' => 'Only for some — I will flag which',
                                'no' => 'No faces — crop or blur',
                                'unknown' => 'Not sure yet',
                            ]),
                        Textarea::make('q7_real_results')
                            ->label('Real results I am happy to claim')
                            ->placeholder('e.g. student\'s first 1 km hit, group sizes, match results — only ones you can stand behind')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('8 · Prices and what the shooter must bring')
                    ->description('Prices and seats are already live on the courses page. Handgun and one-on-one currently say "on request".')
                    ->columns(2)
                    ->schema([
                        Toggle::make('q8_handgun_publish_price')
                            ->label('Publish a handgun price on the site'),
                        TextInput::make('q8_handgun_price')
                            ->label('Handgun price (Rands, per shooter)')
                            ->prefix('R')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->visible(fn (Get $get): bool => (bool) $get('q8_handgun_publish_price')),
                        Radio::make('q8_one_on_one_pricing')
                            ->label('One-on-one coaching')
                            ->options([
                                'quoted' => 'Keep "quoted" — no number on the site',
                                'starting' => 'Show a starting price',
                            ])
                            ->columnSpanFull(),
                        TextInput::make('q8_one_on_one_start_price')
                            ->label('One-on-one starting price (Rands, from)')
                            ->prefix('R')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->visible(fn (Get $get): bool => $get('q8_one_on_one_pricing') === 'starting'),
                        Textarea::make('q8_off_homepage_prices')
                            ->label('Any price you want kept off the homepage')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('q8_per_course_notes')
                            ->label('Per-course kit notes')
                            ->placeholder("For each course, tell us:\n- duration\n- group size\n- how you want the location described publicly\n- rounds to bring\n- what you supply vs what they bring")
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),

                Section::make('9 · How should someone book?')
                    ->description('"Book" currently opens the contact form with the course and date pre-filled. No card payment for courses.')
                    ->schema([
                        Radio::make('q9_booking_flow')
                            ->hiddenLabel()
                            ->options([
                                'enquire' => 'Keep enquire — I confirm the seat',
                                'card' => 'Take payment on the site and reserve the seat',
                                'whatsapp_too' => 'Also offer WhatsApp',
                            ]),
                        TextInput::make('q9_whatsapp_number')
                            ->label('WhatsApp number to publish')
                            ->tel()
                            ->placeholder('e.g. +27 82 123 4567')
                            ->visible(fn (Get $get): bool => $get('q9_booking_flow') === 'whatsapp_too'),
                        Radio::make('q9_whatsapp_placement')
                            ->label('Where should the WhatsApp button sit?')
                            ->options([
                                'every_course' => 'Next to every course',
                                'homepage_only' => 'Just once on the homepage / in the header',
                                'contact_only' => 'Only on the contact page',
                            ])
                            ->visible(fn (Get $get): bool => $get('q9_booking_flow') === 'whatsapp_too'),
                    ]),

                Section::make('10 · Where should a "Book now" button in the menu go?')
                    ->schema([
                        Radio::make('q10_book_now_target')
                            ->hiddenLabel()
                            ->options([
                                'courses' => 'The courses list',
                                'calendar' => 'The calendar',
                                'contact' => 'The contact form',
                            ]),
                    ]),

                Section::make('11 · Your intro')
                    ->description('The About section currently lists: founder & first chair of Pretoria Precision Rifle Club, co-founder of Royal Flush Steel Challenge, SAPRF board, match director, 10+ years on the line.')
                    ->schema([
                        Textarea::make('q11_bio_edits')
                            ->label('Anything to drop or reword? (leave blank to keep all of it)')
                            ->rows(3),
                        Textarea::make('q11_why_it_matters')
                            ->label('One sentence, in your voice, on why that matters to a student')
                            ->rows(2),
                        Radio::make('q11_photo_choice')
                            ->label('Photo')
                            ->options([
                                'keep' => 'Keep the current photo',
                                'coaching' => 'I have a photo of me actually coaching — I will send it',
                                'new' => 'Take a new photo for the site',
                            ]),
                    ]),

                Section::make('12 · The range')
                    ->schema([
                        Textarea::make('q12_range_publish')
                            ->label('What must a visitor know about the range?')
                            ->rows(3),
                        Textarea::make('q12_range_do_not_publish')
                            ->label('What must we NOT publish? (exact location, gate details, exact distances, etc.)')
                            ->rows(3),
                    ]),

                Section::make('13 · The shop, for this round')
                    ->description('Right now it is apparel. The suggestion was to grow it into training gear, PRS accessories, reloading gear, or a "what I use" list.')
                    ->schema([
                        Radio::make('q13_shop_scope')
                            ->hiddenLabel()
                            ->options([
                                'merch_only' => 'Leave it as merch for now',
                                'specific_gear' => 'There is specific gear I want to sell soon',
                            ]),
                        Textarea::make('q13_specific_gear')
                            ->label('What gear?')
                            ->rows(3)
                            ->visible(fn (Get $get): bool => $get('q13_shop_scope') === 'specific_gear'),
                    ]),

                Section::make('14 · What should we NOT change?')
                    ->description('Logo, colours, "Dial in your distance", the rifle builder, anything else off limits.')
                    ->schema([
                        Textarea::make('q14_do_not_change')
                            ->hiddenLabel()
                            ->rows(3),
                    ]),

                Section::make('15 · Who writes the new lines?')
                    ->schema([
                        Radio::make('q15_who_writes')
                            ->hiddenLabel()
                            ->options([
                                'draft_approve' => 'Paul drafts in my voice and I approve before it goes live',
                                'i_send' => 'I will send the final headlines myself',
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Persist each answer under a stable settings key. Empty strings are
        // stored as NULL so the settings table stays clean.
        foreach (self::KEYS as $key) {
            $value = $data[$key] ?? null;

            if (is_string($value)) {
                $value = trim($value);
                $value = $value === '' ? null : $value;
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif ($value !== null) {
                $value = (string) $value;
            }

            Setting::put("questionnaire.{$key}", $value);
        }

        // Email a formatted copy to Dirk + Paul. Failure to send must not
        // swallow the save (his answers are already persisted).
        try {
            $recipients = array_values(array_filter(array_unique([
                (string) env('ADMIN_EMAIL', 'dirkpio01@gmail.com'),
                self::NOTIFY_CC,
            ])));

            Mail::to($recipients)->queue(new SiteQuestionnaireSubmitted($data));

            Notification::make()
                ->title('Answers saved')
                ->body('Emailed to '.implode(' and ', $recipients).'. Come back any time — the form remembers what you typed.')
                ->success()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Answers saved (email failed)')
                ->body('Your answers were saved but the email did not go out: '.$e->getMessage())
                ->warning()
                ->persistent()
                ->send();
        }
    }

    /**
     * The five bookable courses on the public site, plus the "not offered"
     * escape hatch. Keep in sync with the seeded course templates.
     *
     * @return array<string, string>
     */
    private static function courseOptions(): array
    {
        return [
            'zero_to_first_steel' => 'Zero to First Steel',
            'applied_long_range' => 'Applied Long Range',
            'prs_match_skills' => 'PRS Match Skills',
            'precision_reloading' => 'Precision Reloading',
            'handgun_fundamentals' => 'Handgun Fundamentals',
            'one_on_one' => 'One-on-one coaching (quoted)',
            'none' => "Don't offer this",
        ];
    }
}
