<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SalaryService;
use Carbon\Carbon;

class GenerateMonthlySalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:generate-monthly {--month=} {--year=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly salaries for all active employees';

    protected $salaryService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SalaryService $salaryService)
    {
        parent::__construct();
        $this->salaryService = $salaryService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $month = $this->option('month') ?: Carbon::now()->subMonth()->month;
        $year = $this->option('year') ?: Carbon::now()->subMonth()->year;

        $this->info("Generating salaries for {$year}-{$month}...");

        try {
            $salaries = $this->salaryService->generateSalariesForMonth($month, $year);
            
            $this->info("Successfully generated " . count($salaries) . " salary records.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error generating salaries: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
