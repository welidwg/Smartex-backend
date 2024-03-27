<?php

namespace App\Console\Commands;

use App\Jobs\VerifPanne as JobsVerifPanne;
use Illuminate\Console\Command;

class VerifPanne extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verif:panne';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifier si une ou plusieurs machines vont tombées en panne les prochaines jours';

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
        dispatch(new JobsVerifPanne());
        $this->info('Vérification de la panne effectuée avec succès !');
    }
}
