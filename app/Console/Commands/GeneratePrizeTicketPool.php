<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrizeTicket;
use Illuminate\Support\Facades\Redis;

class GeneratePrizeTicketPool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:generate-prize-tickets-pool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将ticket表的入库';

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
        $tickets = PrizeTicket::where('status', '=', '1')->where('in_pool', '=', '0')->get();
        foreach ($tickets as $ticket) {
            $allKey = 'LOTTERY_PRIZE_TICKETS_POOL_' . $ticket->prize_id;
            $liveKey = 'LOTTERY_PRIZE_TICKETS_ALIVE_' . $ticket->prize_id;
            $exists = Redis::sismember($allKey, $ticket->ticket);
            if(!$exists) {
                Redis::sadd($allKey, $ticket->ticket);
                Redis::sadd($liveKey, $ticket->ticket);
            }
            $ticket->in_pool = '1';
            $ticket->save();
        }

        echo 'complete';
    }
}
