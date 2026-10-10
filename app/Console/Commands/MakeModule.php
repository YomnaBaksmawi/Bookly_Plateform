<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;



class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name : The name of the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module with complete structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $studlyName = str($name)->studly();

        $this->info("Creating module: {$studlyName}");

        // Create directories
        $modulePath = app_path("Modules/{$studlyName}");
        $directories = [
            'Models',
            'Http/Controllers',
            'Http/Requests',
            'Http/Resources',
            'Services',
            'Actions',
            'Queries',
            'Enums',
            'Exceptions',
        ];

        foreach ($directories as $dir) {
            $fullPath = "{$modulePath}/{$dir}";
            if (! is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                $this->line("Created: {$fullPath}");
            }
        }

        // Create basic files
        $this->createController($studlyName);
        $this->createService($studlyName);
        $this->createResource($studlyName);
$this->createModel($studlyName);
        $this->info("Module {$studlyName} created successfully!");
        $this->line("Don't forget to add routes in routes/modules.php");
    }

    private function createController(string $name): void
    {
        $stub = "<?php

namespace App\\Modules\\{$name}\\Http\\Controllers;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;

class {$name}Controller extends Controller
{
    public function __construct()
    {
        // Add middleware if needed
    }

    public function index(Request \$request): JsonResponse
    {
        return response()->json([
            'data' => []
        ]);
    }
}";

        file_put_contents(
            app_path("Modules/{$name}/Http/Controllers/{$name}Controller.php"),
            $stub
        );
    }

    private function createService(string $name): void
    {
        $stub = "<?php

namespace App\\Modules\\{$name}\\Services;

class {$name}Service
{
    public function __construct()
    {
        //
    }
}";

        file_put_contents(
            app_path("Modules/{$name}/Services/{$name}Service.php"),
            $stub
        );
    }

    private function createResource(string $name): void
    {
        $stub = "<?php

namespace App\\Modules\\{$name}\\Http\\Resources;

use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

class {$name}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return [
            'id' => \$this->id,
            'created_at' => \$this->created_at?->toISOString(),
            'updated_at' => \$this->updated_at?->toISOString(),
        ];
    }
}";

        file_put_contents(
            app_path("Modules/{$name}/Http/Resources/{$name}Resource.php"),
            $stub
        );
    }
    private function createModel(string $name): void
{
    $stub = "<?php

namespace App\\Modules\\{$name}\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class {$name} extends Model
{
    //
}";

    file_put_contents(
        app_path("Modules/{$name}/Models/{$name}.php"),
        $stub
    );
}
}
