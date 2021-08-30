<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseIsReady extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:isready';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the database is ready to accept connections';

    /**
     * Create a new database IsReady command instance.
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
        // Test database connection
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->error('false');
            return;
        }
        $this->info('true');
    }
}
