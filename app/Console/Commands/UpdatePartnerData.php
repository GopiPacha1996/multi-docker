<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\V2\PartnerDataController;

class UpdatePartnerData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatePartnerData:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Job to update partner data';

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
        $update = new PartnerDataController();
        $update->update();
    }
}
