<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckDatabaseConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that the configured database is reachable';

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
            DB::select('select 1');
        } catch (Throwable) {
            $this->components->error('Database connection failed. Check the configured database service and credentials.');

            return self::FAILURE;
        }

        $this->components->info(sprintf(
            'Database connection is healthy (%s).',
            DB::getDefaultConnection(),
        ));

        return self::SUCCESS;
    }
}
