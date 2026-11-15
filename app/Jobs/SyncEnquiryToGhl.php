<?php

namespace App\Jobs;

use App\Models\Enquiry;
use App\Services\GhlClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/** Pushes one enquiry to GoHighLevel. Never affects the visitor: failures are recorded and retried. */
class SyncEnquiryToGhl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public function __construct(public int $enquiryId) {}

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(GhlClient $ghl): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry) {
            return;
        }

        if (! $ghl->enabled()) {
            $enquiry->update(['ghl_status' => 'skipped']);

            return;
        }

        try {
            $id = $ghl->sync($enquiry);
            $enquiry->update([
                'ghl_status' => 'synced', 'ghl_contact_id' => $id,
                'ghl_error' => null, 'ghl_synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Log the message only (never headers or the token).
            Log::warning('GHL sync failed for enquiry '.$enquiry->id.': '.mb_substr($e->getMessage(), 0, 300));
            $enquiry->update(['ghl_status' => 'failed', 'ghl_error' => mb_substr($e->getMessage(), 0, 500)]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Enquiry::whereKey($this->enquiryId)->update(['ghl_status' => 'failed']);
    }
}
