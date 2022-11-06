<?php

namespace Lbausch\BuildMetadataLaravel\Console\Commands;

use Illuminate\Console\Command;
use Lbausch\BuildMetadataLaravel\BuildMetadataManager;

class ClearBuildMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildmetadata:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forget cached build metadata';

    public function __construct(
        /**
         * Config.
         */
        protected BuildMetadataManager $manager
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->manager->forget();

        return Command::SUCCESS;
    }
}
