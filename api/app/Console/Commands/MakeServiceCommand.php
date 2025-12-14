<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

final class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name} {--repository=} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class with interface and service provider binding';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $repositoryName = $this->option('repository') ?? $this->guessRepositoryName($name);
        $modelName = $this->option('model') ?? $this->guessModelName($name);

        // Ensure name ends with Service
        if (! str_ends_with($name, 'Service')) {
            $name .= 'Service';
        }

        // Ensure repository name ends with RepositoryInterface
        if (! str_ends_with($repositoryName, 'RepositoryInterface')) {
            if (str_ends_with($repositoryName, 'Repository')) {
                $repositoryName .= 'Interface';
            } else {
                $repositoryName .= 'RepositoryInterface';
            }
        }

        // Generate service class
        $this->createService($name, $repositoryName, $modelName);

        // Generate interface
        $this->createInterface($name, $repositoryName, $modelName);

        // Update service provider
        $this->updateServiceProvider($name);

        $this->info("Service {$name} created successfully!");

        return Command::SUCCESS;
    }

    /**
     * Guess repository name from service name.
     */
    private function guessRepositoryName(string $serviceName): string
    {
        // Remove "Service" suffix if present
        $name = str_replace('Service', '', $serviceName);

        // Convert to singular if plural
        $name = Str::singular($name);

        return "{$name}RepositoryInterface";
    }

    /**
     * Guess model name from service name.
     */
    private function guessModelName(string $serviceName): string
    {
        // Remove "Service" suffix if present
        $name = str_replace('Service', '', $serviceName);

        // Convert to singular if plural
        $name = Str::singular($name);

        return $name;
    }

    /**
     * Create service class file.
     */
    private function createService(string $name, string $repositoryName, string $modelName): void
    {
        $stubPath = base_path('stubs/service.stub');

        if (! file_exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");

            return;
        }

        $stubContent = file_get_contents($stubPath);
        $content = $this->replacePlaceholders($stubContent, $name, $repositoryName, $modelName);

        $directory = app_path('Services/Concretes');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = "{$directory}/{$name}.php";
        file_put_contents($filePath, $content);

        $this->info("Created: {$filePath}");
    }

    /**
     * Create interface file.
     */
    private function createInterface(string $name, string $repositoryName, string $modelName): void
    {
        $stubPath = base_path('stubs/service-interface.stub');

        if (! file_exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");

            return;
        }

        $stubContent = file_get_contents($stubPath);
        $content = $this->replacePlaceholders($stubContent, $name, $repositoryName, $modelName);

        $directory = app_path('Services/Contracts');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = "{$directory}/{$name}Interface.php";
        file_put_contents($filePath, $content);

        $this->info("Created: {$filePath}");
    }

    /**
     * Update ServiceServiceProvider with new binding.
     */
    private function updateServiceProvider(string $name): void
    {
        $providerPath = app_path('Providers/ServiceServiceProvider.php');

        if (! file_exists($providerPath)) {
            $this->error("ServiceServiceProvider not found at: {$providerPath}");

            return;
        }

        $content = file_get_contents($providerPath);

        // Extract namespace parts
        $interfaceName = "{$name}Interface";
        $interfaceFQN = "App\\Services\\Contracts\\{$interfaceName}";
        $concreteFQN = "App\\Services\\Concretes\\{$name}";

        // Check if binding already exists
        if (str_contains($content, $interfaceFQN)) {
            $this->warn("Binding for {$interfaceFQN} already exists in ServiceServiceProvider");

            return;
        }

        // Add use statements if they don't exist
        if (! str_contains($content, "use {$interfaceFQN}")) {
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
                        $this->error('Failed to add use statements to ServiceServiceProvider');

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
            $this->error('Failed to add binding to ServiceServiceProvider register method');

            return;
        }

        file_put_contents($providerPath, $newContent);
        $this->info("Updated: {$providerPath}");
    }

    /**
     * Replace placeholders in stub content.
     */
    private function replacePlaceholders(string $content, string $serviceName, string $repositoryName, string $modelName): string
    {
        // Repository name should already end with RepositoryInterface
        // Extract the short name for the type hint (e.g., "CustomerRepositoryInterface")
        $repositoryShortName = class_basename($repositoryName);

        $replacements = [
            '{{ namespace }}' => 'App\\Services\\Concretes',
            '{{ interfaceNamespace }}' => 'App\\Services\\Contracts',
            '{{ class }}' => $serviceName,
            '{{ interface }}' => "{$serviceName}Interface",
            '{{ repository }}' => $repositoryShortName,
            '{{ repositoryNamespace }}' => "App\\Repositories\\Contracts\\{$repositoryName}",
            '{{ model }}' => $modelName,
            '{{ modelNamespace }}' => "App\\Models\\{$modelName}",
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
