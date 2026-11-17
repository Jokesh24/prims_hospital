<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Payroll;
use App\Services\PayrollService;

class GeneratePayrollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:generate';

    /** 
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly payroll';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = Carbon::now()->subMonth()->month;
        $year = Carbon::now()->subMonth()->year;
        $clients = Client::where('status', 1)->get();
        foreach ($clients as $client) {
            app(PayrollService::class)->generatePayroll($client->id, $month, $year);
        }
    }
}
