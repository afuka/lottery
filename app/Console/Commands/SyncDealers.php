<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Service\SupplierCrm;

class SyncDealers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sync-dealers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步CRM经销商';

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
        try{
            $res = $ser->syncDealers();
            if($res === false) echo $ser->getErr(), "\n";
            echo 'complete';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
