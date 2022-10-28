<?php

namespace Lbausch\BuildMetadataLaravel\Console\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;

class SaveBuildMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildmetadata:save {metadata* : build metadata, e.g. BUILD_REF=foo BUILD_SHA=12345678}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save build metadata';

    public function __construct(
        /**
         * Config.
         */
        protected Repository $config
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
        // Obtain metadata argument
        $metadata_raw = $this->argument('metadata');

        $metadata = [];

        // The metadata are expected to be key=value-pairs
        foreach ($metadata_raw as $data) {
            // Verify string contains an equal sign
            if (!str_contains((string) $data, '=')) {
                $this->warn('Invalid data provided: '.$data);

                continue;
            }

            // Split string at the first equal sign
            [$key, $val] = explode('=', (string) $data, 2);

            $key = trim($key);

            $metadata[$key] = $val;
        }

        // Read destination file from config
        $metadata_file = $this->config->get('build-metadata.file');

        // Save build metadata as JSON to file
        $this->info('Saving build metadata to '.$metadata_file);

        $metadata_json = json_encode($metadata, JSON_THROW_ON_ERROR);

        file_put_contents($metadata_file, $metadata_json);

        return Command::SUCCESS;
    }
}
