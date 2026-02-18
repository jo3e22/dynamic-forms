<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GDPRService;

class ApplyDataRetentionPolicies extends Command
{
    protected $signature = 'gdpr:apply-retention-policies {--dry-run : Preview what would be deleted without actually deleting}';
    protected $description = 'Apply GDPR data retention policies and delete expired submissions';

    public function __construct(
        protected GDPRService $gdprService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('dry-run')) {
            $this->info('DRY RUN MODE - No data will be deleted');
        }

        $deletedCount = $this->gdprService->applyRetentionPolicies();

        $this->info("✓ Deleted $deletedCount expired submissions");
    }
}
