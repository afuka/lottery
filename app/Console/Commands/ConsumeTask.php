<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Service\TaskConsumer;

class ConsumeTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:comsume-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '消费任务';

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
        // 去循环获取消息，并消费
        $consumer = new TaskConsumer();
        Task::where('status', '=', '0')->chunk(200, function($tasks) use ($consumer) {
            foreach ($tasks as $tasks) {
                $consumer->consume($tasks);
            }
        });
    }
}
