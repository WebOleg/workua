<?php

namespace App\Console\Commands;

use App\Domain\Link\Contracts\LinkRepositoryInterface;
use Illuminate\Console\Command;

/**
 * CleanupExpiredLinks Command
 * 
 * Removes expired links from the database
 */
class CleanupExpiredLinks extends Command
{
    /**
     * The name and signature of the console command
     * 
     * @var string
     */
    protected $signature = 'links:cleanup
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description
     * 
     * @var string
     */
    protected $description = 'Clean up expired links from the database';

    /**
     * Execute the console command
     * 
     * @param LinkRepositoryInterface $repository
     * @return int
     */
    public function handle(LinkRepositoryInterface $repository): int
    {
        $this->info('Starting cleanup of expired links...');

        $expiredLinks = $repository->getExpiredLinks();
        $count = $expiredLinks->count();

        if ($count === 0) {
            $this->info('No expired links found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$count} expired link(s).");

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN - No links will be deleted');
            
            $this->table(
                ['ID', 'Short Code', 'Original URL', 'Expired At'],
                $expiredLinks->map(fn($link) => [
                    $link->id,
                    $link->short_code,
                    substr($link->original_url, 0, 50) . '...',
                    $link->expires_at->format('Y-m-d H:i:s'),
                ])
            );

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($expiredLinks as $link) {
            $repository->delete($link);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Successfully deleted {$count} expired link(s).");

        return Command::SUCCESS;
    }
}
