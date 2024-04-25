<?php

namespace App\Console\Commands;

use App\Jobs\ResetPresence as JobsResetPresence;
use Illuminate\Console\Command;

class ResetPresence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset workers availability daily';

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
     * @return int
     */
    public function handle()
    {
        dispatch(new JobsResetPresence());
        $this->info('Tous les ouvriers sont abscent.');
    }
}
