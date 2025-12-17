<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

final class MakeRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name} {--model=} {--queryable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class with interface and service provider binding';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $modelName = $this->option('model') ?? $this->guessModelName($name);
        $isQueryable = $this->option('queryable');

        // Ensure name ends with Repository
        if (!str_ends_with($name, 'Repository')) {
            $name .= 'Repository';
        }

        // Generate repository class
        $this->createRepository($name, $modelName, $isQueryable);

        // Generate interface
        $this->createInterface($name, $modelName, $isQueryable);

        // Update service provider
        $this->updateServiceProvider($name);

        $this->info("Repository {$name} created successfully!");

        return Command::SUCCESS;
    }

    /**
     * Guess model name from repository name.
     */
    private function guessModelName(string $repositoryName): string
    {
        // Remove "Repository" suffix if present
        $name = str_replace('Repository', '', $repositoryName);

        // Convert to singular if plural
        $name = Str::singular($name);

        return $name;
    }

    /**
     * Create repository class file.
     */
    private function createRepository(string $name, string $modelName, bool $isQueryable): void
    {
        $stub = $isQueryable ? 'repository-queryable.stub' : 'repository.stub';
        $stubPath = base_path("stubs/{$stub}");

        if (!file_exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");

            return;
        }

        $stubContent = file_get_contents($stubPath);
        $content = $this->replacePlaceholders($stubContent, $name, $modelName, $isQueryable);

        $directory = app_path('Repositories/Concretes');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = "{$directory}/{$name}.php";
        file_put_contents($filePath, $content);

        $this->info("Created: {$filePath}");
    }

    /**
     * Create interface file.
     */
    private function createInterface(string $name, string $modelName, bool $isQueryable): void
    {
        $stubPath = base_path('stubs/repository-interface.stub');

        if (!file_exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");

            return;
        }

        $stubContent = file_get_contents($stubPath);
        $content = $this->replacePlaceholders($stubContent, $name, $modelName, $isQueryable);

        $directory = app_path('Repositories/Contracts');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = "{$directory}/{$name}Interface.php";
        file_put_contents($filePath, $content);

        $this->info("Created: {$filePath}");
    }

    /**
     * Update RepositoryServiceProvider with new binding.
     */
    private function updateServiceProvider(string $name): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (!file_exists($providerPath)) {
            $this->error("RepositoryServiceProvider not found at: {$providerPath}");

            return;
        }

        $content = file_get_contents($providerPath);

        // Extract namespace parts
        $interfaceName = "{$name}Interface";
        $interfaceFQN = "App\\Repositories\\Contracts\\{$interfaceName}";
        $concreteFQN = "App\\Repositories\\Concretes\\{$name}";

        // Check if binding already exists
        if (str_contains($content, $interfaceFQN)) {
            $this->warn("Binding for {$interfaceFQN} already exists in RepositoryServiceProvider");

            return;
        }

        // Add use statements if they don't exist
        if (!str_contains($content, "use {$interfaceFQN}")) {
            // Find the position right before the class declaration
            // Look for the last use statement followed by blank lines and then class
            $pattern = '/(use [^;]+;)(\s*\n)(\s*\n)(\s*(?:final\s+)?class)/';
            if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                // Insert new use statements after the last use statement, before the blank line
                $insertPos = $matches[0][1] + mb_strlen($matches[1][0]) + mb_strlen($matches[2][0]);
                $newUseStatements = "\nuse {$interfaceFQN};\nuse {$concreteFQN};";
                $content = substr_replace($content, $newUseStatements, $insertPos, 0);
            } else {
                // Fallback: find last use statement and add after it, ensuring blank line before class
                $pattern = '/(use [^;]+;)(\s*\n)/';
                if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $lastMatch = end($matches[0]);
                    $insertPos = $lastMatch[1] + mb_strlen($lastMatch[0]);
                    // Check what comes after the last use statement
                    $afterLastUse = mb_substr($content, $insertPos, 20);
                    // If next non-whitespace is 'class', we need to add blank line
                    if (preg_match('/^\s*(final\s+)?class/', $afterLastUse)) {
                        // No blank line, add use statements with blank line
                        $newUseStatements = "use {$interfaceFQN};\nuse {$concreteFQN};\n";
                    } elseif (preg_match('/^\s*\n\s*\n/', $afterLastUse)) {
                        // Already has blank line, just add use statements
                        $newUseStatements = "use {$interfaceFQN};\nuse {$concreteFQN};";
                    } else {
                        // Has some whitespace but not a full blank line, add use statements with blank line
                        $newUseStatements = "use {$interfaceFQN};\nuse {$concreteFQN};\n";
                    }

                    $content = substr_replace($content, $newUseStatements, $insertPos, 0);
                } else {
                    // Last resort: insert before class declaration
                    $pattern = '/(\s*)(final\s+)?class/';
                    $replacement = "use {$interfaceFQN};\nuse {$concreteFQN};\n$1$2class";
                    $content = preg_replace($pattern, $replacement, $content, 1);
                    if ($content === null) {
                        $this->error('Failed to add use statements to RepositoryServiceProvider');

                        return;
                    }
                }
            }
        }

        // Add binding in register method
        $binding = "        \$this->app->bind({$interfaceName}::class, {$name}::class);";
        $pattern = '/(public function register\(\): void\s*\{)([^}]*?)(\s*\})/s';
        $replacement = "$1$2\n{$binding}$3";
        $newContent = preg_replace($pattern, $replacement, $content, 1);

        if ($newContent === null || $newContent === $content) {
            $this->error('Failed to add binding to RepositoryServiceProvider register method');

            return;
        }

        file_put_contents($providerPath, $newContent);
        $this->info("Updated: {$providerPath}");
    }

    /**
     * Replace placeholders in stub content.
     */
    private function replacePlaceholders(string $content, string $repositoryName, string $modelName, ?bool $isQueryable = null): string
    {
        $replacements = [
            '{{ namespace }}'               => 'App\\Repositories\\Concretes',
            '{{ interfaceNamespace }}'      => 'App\\Repositories\\Contracts',
            '{{ class }}'                   => $repositoryName,
            '{{ interface }}'               => "{$repositoryName}Interface",
            '{{ model }}'                   => $modelName,
            '{{ modelNamespace }}'          => "App\\Models\\{$modelName}",
            '{{ baseRepository }}'          => $isQueryable ? 'QueryableRepository' : 'BaseRepository',
            '{{ baseRepositoryInterface }}' => $isQueryable ? 'QueryableRepositoryInterface' : 'BaseRepositoryInterface',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
