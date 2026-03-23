<?php

namespace App\Console\Commands;

use App\Services\ModuleGenerator\ModuleGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The name of the module}';

    protected $description = 'Create a new module with the standard structure';

    public function __construct(
        private ModuleGenerator $moduleGenerator
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $moduleName = Str::studly($this->argument('name'));

        $this->info("Creating module: {$moduleName}");

        try {
            $this->moduleGenerator->generate(base_path(), $moduleName);

            $this->info("\n✅ Module {$moduleName} created successfully!");
            $this->displayNextSteps($moduleName);

            return self::SUCCESS;
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayNextSteps(string $moduleName): void
    {
        $this->newLine();
        $this->info('Next steps:');
        $this->info("1. Start adding your controllers in modules/{$moduleName}/Http/Controllers");
        $this->info("2. Define your models in modules/{$moduleName}/Domain/Models");
        $this->info("3. Add routes in modules/{$moduleName}/Routes/api.php");
    }
}
