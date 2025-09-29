<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsJob;
use App\Models\NewsSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchNewsArticles extends Command
{
    protected $signature = 'news:fetch
                            {--source= : Fetch from a specific source}
                            {--sync : Run synchronously without queue}';

    protected $description = 'Fetch articles from active news sources';

    public function handle(): int
    {
        $specificSource = $this->option('source');
        $useSync = $this->option('sync');

        $query = NewsSource::where('is_active', true);

        if ($specificSource) {
            $query->where('api_service', $specificSource);
        }

        $sources = $query->get();

        if ($sources->isEmpty()) {
            $this->error('No active news sources found!');
            return Command::FAILURE;
        }

        $this->info("Found {$sources->count()} active sources");

        foreach ($sources as $source) {
            if ($useSync) {
                // Run synchronously
                $this->info("Processing {$source->name} synchronously...");
                dispatch_now(new FetchNewsJob($source->id));
            } else {
                // Dispatch to queue
                $this->info("Dispatching job for {$source->name}...");
                FetchNewsJob::dispatch($source->id);
            }
        }

        if (!$useSync) {
            $this->info('All jobs dispatched to queue.');
        }

        return Command::SUCCESS;
    }
}
