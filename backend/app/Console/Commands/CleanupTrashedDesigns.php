<?php

namespace App\Console\Commands;

use App\Models\CreativeAsset;
use Illuminate\Console\Command;

class CleanupTrashedDesigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'designs:cleanup-trash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete designs in trash for more than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        $count = CreativeAsset::designs()
            ->whereNotNull('trashed_at')
            ->where('trashed_at', '<', $thirtyDaysAgo)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
        
        $this->info("Permanently deleted {$count} designs that were in trash for more than 30 days");
        
        return Command::SUCCESS;
    }
}

