<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\LegalIdentity;
use Illuminate\Console\Command;

class LegalCheck extends Command
{
    protected $signature = 'legal:check';

    protected $description = 'Fail if any required legal identity value is empty or still a placeholder.';

    public function handle(): int
    {
        $failures = LegalIdentity::failures();

        if ($failures === []) {
            $this->info('Legal identity check passed.');

            return self::SUCCESS;
        }

        $this->error('Legal identity check failed. These keys are empty or still placeholders:');

        foreach ($failures as $key) {
            $this->line('  - '.$key);
        }

        $this->newLine();
        $this->line('Set them in .env (LEGAL_*) or, for telephone / email / VAT / dealer licence, on the admin Settings page.');

        return self::FAILURE;
    }
}
