<?php

namespace App\Console\Commands;

use App\Services\Ghl\GhlFields;
use App\Services\GhlClient;
use Illuminate\Console\Command;

class GhlSetup extends Command
{
    protected $signature = 'ghl:setup';

    protected $description = 'Create the GoHighLevel custom fields this site uses (safe to re-run)';

    public function handle(GhlClient $ghl): int
    {
        if (! $ghl->enabled()) {
            $this->error('No GoHighLevel credentials are set (Admin > GoHighLevel, or GHL_API_TOKEN and GHL_LOCATION_ID in .env), or the sync is paused. Nothing to do.');

            return self::FAILURE;
        }

        try {
            $existing = $ghl->fieldIds(fresh: true);
        } catch (\Throwable $e) {
            $this->error('Could not list custom fields: '.$e->getMessage());
            $this->line('Check the token scopes (see docs/reference/GHL_INTEGRATION.md).');

            return self::FAILURE;
        }

        $created = $skipped = $failed = 0;
        foreach (GhlFields::definitions() as $name => $type) {
            if (isset($existing[$name])) {
                $skipped++;

                continue;
            }
            try {
                $ghl->createField($name, $type);
                $this->info("Created: $name");
                $created++;
            } catch (\Throwable $e) {
                $this->error("Failed:  $name  ({$e->getMessage()})");
                $failed++;
            }
        }

        $ghl->fieldIds(fresh: true);
        $this->newLine();
        $this->line("Created $created, already existed $skipped, failed $failed.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
