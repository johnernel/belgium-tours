<?php

namespace App\Console\Commands;

use App\Services\GhlClient;
use Illuminate\Console\Command;

class GhlTest extends Command
{
    protected $signature = 'ghl:test {--keep : Keep the test contact instead of deleting it}';

    protected $description = 'Check the GHL token and location, and create a labelled test contact';

    public function handle(GhlClient $ghl): int
    {
        if (! $ghl->enabled()) {
            $this->error('GHL_API_TOKEN and GHL_LOCATION_ID are not set in .env.');

            return self::FAILURE;
        }

        $check = $ghl->checkConnection();
        if (! $check['ok']) {
            $this->error('Location check failed (HTTP '.$check['status'].'). Check the token and location id.');

            return self::FAILURE;
        }
        $this->info('Connected to location: '.($check['name'] ?: $ghl->locationId()));

        try {
            $res = $ghl->upsertRaw([
                'locationId' => $ghl->locationId(),
                'firstName' => 'TEST',
                'lastName' => 'Do Not Contact',
                'email' => 'ghl-test+'.time().'@example.com',
                'tags' => ['website-test'],
                'source' => 'ghl:test command',
            ]);
        } catch (\Throwable $e) {
            $this->error('Could not create the test contact: '.$e->getMessage());

            return self::FAILURE;
        }

        $id = $res['contact']['id'] ?? null;
        $this->info('Test contact saved: '.$id);

        if ($id && ! $this->option('keep')) {
            $this->line($ghl->deleteContact($id) ? 'Test contact removed.' : 'Could not remove the test contact; delete it by hand in GHL.');
        }

        return self::SUCCESS;
    }
}
