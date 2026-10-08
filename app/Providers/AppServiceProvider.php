<?php

namespace App\Providers;

use App\Support\EmailLogger;
use App\Support\MailSettings;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Livewire's default temporary-upload rule is 12 MB. Range clips are
        // larger than that, so the file never attached and the public play
        // button was left with an empty URL. 524288 KB = 512 MB, matching
        // nginx, PHP, and the video form.
        config([
            'livewire.temporary_file_upload.rules' => ['required', 'file', 'max:524288'],
            'livewire.temporary_file_upload.max_upload_time' => 15,
        ]);

        // Let the admin-configured mail settings (settings table) override the
        // .env defaults without a deploy. No-op until the settings table exists.
        MailSettings::apply();

        // Queue workers are long-lived and every site email is queued, so the
        // boot-time apply above would pin the worker to whatever mailer was
        // saved when it started. Re-read before each job and drop resolved
        // mailer instances so admin changes take effect immediately.
        Queue::before(function (): void {
            MailSettings::apply();
            Mail::forgetMailers();
        });

        Event::listen(fn (MessageSent $event) => EmailLogger::sent($event));
        Event::listen(fn (JobFailed $event) => EmailLogger::failed($event));
    }
}
