<?php

namespace App\Console\Commands;

use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

class FetchNewsArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from all active news sources';

    /**
     * Execute the console command.
     */
    public function handle(NewsAggregatorService $newsAggregator)
    {
        $this->info('Starting news aggregation...');

        try {
            $newsAggregator->aggregateArticles();
            $this->info('News aggregation completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during news aggregation: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
