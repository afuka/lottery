<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriveReservation;
use App\Service\SupplierCrm;

class PushDriveReservationsToCrm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:push-drive-reservations-to-crm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '销售线索推送到crm';

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
        $ser = new SupplierCrm();
        $records = DriveReservation::where('status', '=', '1')->where('crm_sync', '=', '1')->get();
        foreach ($records as $record) {
            $ser->pushRecord($record);
        }
        
        echo 'complete';
    }
}
