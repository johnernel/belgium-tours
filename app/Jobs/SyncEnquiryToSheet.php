<?php

namespace App\Jobs;

use App\Models\Enquiry;
use App\Services\SheetClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/** Pushes one enquiry to the Google Sheet. Never affects the visitor: failures are recorded and retried. */
class SyncEnquiryToSheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public function __construct(public int $enquiryId) {}

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(SheetClient $sheet): void
    {
        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry) {
            return;
        }

        if (! $sheet->enabled()) {
            $enquiry->update(['sheet_status' => 'skipped']);

            return;
        }

        try {
            $sheet->send($enquiry);
            $enquiry->update(['sheet_status' => 'synced', 'sheet_error' => null, 'sheet_synced_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Google Sheet sync failed for enquiry '.$enquiry->id.': '.mb_substr($e->getMessage(), 0, 300));
            $enquiry->update(['sheet_status' => 'failed', 'sheet_error' => mb_substr($e->getMessage(), 0, 500)]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Enquiry::whereKey($this->enquiryId)->update(['sheet_status' => 'failed']);
    }
}
