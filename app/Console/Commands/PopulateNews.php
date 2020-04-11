<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\NewsFeed;

class PopulateNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:newsfeeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command populates the news feeds table with a news feed.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(NewsFeed::where('name', 'LIKE', 'CoinDesk')->count() == 0)
        {
            $newsFeed = new NewsFeed();
            $newsFeed->name = "CoinDesk";
            $newsFeed->url = "http://feeds.feedburner.com/CoinDesk";
            $newsFeed->save();
        }
    }
}
