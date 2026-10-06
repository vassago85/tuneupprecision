<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TrainingEvent;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class PruneEvents extends Command
{
    protected $signature = 'events:prune {--force : Delete the listed events (otherwise this is a dry run)}';

    protected $description = 'Delete events that are not on the public Courses page and have no bookings, RSVPs or testimonials.';

    public function handle(): int
    {
        $keepIds = TrainingEvent::query()->onCoursesPage()->pluck('id');

        $notListed = TrainingEvent::query()
            ->with('courseTemplate')
            ->whereNotIn('id', $keepIds)
            ->orderBy('starts_on');

        // Bookings and RSVPs cascade on delete, so anything with history stays.
        $hasHistory = fn (Builder $q): Builder => $q
            ->whereHas('bookings')
            ->orWhereHas('rsvps')
            ->orWhereHas('testimonials');

        $prunable = (clone $notListed)->whereNot($hasHistory)->get();
        $kept = (clone $notListed)->where($hasHistory)->get();

        $this->info("Kept on the Courses page: {$keepIds->count()}");

        if ($kept->isNotEmpty()) {
            $this->warn("Not on the Courses page but kept because they have bookings, RSVPs or testimonials: {$kept->count()}");
            $this->table(['ID', 'Event', 'Date', 'Status'], $this->rows($kept));
        }

        if ($prunable->isEmpty()) {
            $this->info('Nothing to delete.');

            return self::SUCCESS;
        }

        $this->line(($this->option('force') ? 'Deleting' : 'Would delete').": {$prunable->count()}");
        $this->table(['ID', 'Event', 'Date', 'Status'], $this->rows($prunable));

        if (! $this->option('force')) {
            $this->comment('Dry run — re-run with --force to delete.');

            return self::SUCCESS;
        }

        TrainingEvent::query()->whereKey($prunable->modelKeys())->delete();

        $this->info("Deleted {$prunable->count()} event(s).");

        return self::SUCCESS;
    }

    /**
     * @param  iterable<TrainingEvent>  $events
     * @return array<int, array<int, string|int>>
     */
    private function rows(iterable $events): array
    {
        $rows = [];

        foreach ($events as $event) {
            $rows[] = [
                $event->id,
                $event->displayTitle(),
                $event->starts_on->toDateString(),
                $event->status->getLabel(),
            ];
        }

        return $rows;
    }
}
