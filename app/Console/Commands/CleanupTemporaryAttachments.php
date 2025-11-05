<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CleanupTemporaryAttachments extends Command
{
    protected $signature = 'custodianship:cleanup-temporary-attachments';

    protected $description = 'Delete temporary attachments older than configured hours';

    public function handle(): int
    {
        $hours = config('custodianship.attachments.temporary_cleanup_hours', 24);
        $cutoffTime = now()->subHours($hours);

        $deletedCount = Media::where('collection_name', 'temporary-attachments')
            ->where('created_at', '<', $cutoffTime)
            ->each(fn ($media) => $media->delete());

        $this->info("Deleted {$deletedCount} temporary attachments older than {$hours} hours.");

        return self::SUCCESS;
    }
}
