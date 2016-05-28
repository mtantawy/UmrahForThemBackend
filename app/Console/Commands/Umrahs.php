<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\UmrahRepository;

class Umrahs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'umrahs:cancel-stalled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel Umrahs that have been "In Progress" for 3 days.';

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
    public function handle(UmrahRepository $umrah_repository)
    {
        foreach ($umrah_repository->getStalledUmrahs() as $umrah) {
            $umrah_repository->auth_user_id = $umrah->user_id;
            $umrah_repository->sendStalledUmrahEmails($umrah);
            $this->comment(PHP_EOL.'Cancelled Umrah ID: '.$umrah->id.', for Deceased ID: '.$umrah->deceased_id.PHP_EOL);
        }
    }
}
