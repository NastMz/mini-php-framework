<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

/**
 * MakeControllerCommand
 *
 * Command to create a new controller class.
 * It generates a basic controller template with common methods.
 */
class MakeControllerCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('make:controller');
        $this->setDescription('Create a new controller class');
        $this->addArgument('name', true, 'The name of the controller');
    }

    /**
     * Execute the command to create a new controller.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        $name = $arguments['name'] ?? null;
        
        if (!$name) {
            $this->error('Controller name is required');
            return 1;
        }

        $controllerName = $this->formatClassName($name);
        $fileName = $controllerName . '.php';
        $filePath = __DIR__ . '/../../../../src/Presentation/Controller/' . $fileName;

        if (file_exists($filePath)) {
            $this->error("Controller '$controllerName' already exists");
            return 1;
        }

        $template = $this->getControllerTemplate($controllerName);
        
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, $template);
        
        $this->success("Controller '$controllerName' created successfully at $filePath");
        return 0;
    }

    /**
     * Format the controller name to follow naming conventions.
     *
     * @param string $name The raw name of the controller.
     * @return string The formatted class name.
     */
    private function formatClassName(string $name): string
    {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        
        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }
        
        return $name;
    }

    /**
     * Get the template for the controller class.
     *
     * @param string $className The name of the controller class.
     * @return string The controller class template.
     */
    private function getControllerTemplate(string $className): string
    {
        return <<<PHP
            <?php
            declare(strict_types=1);

            namespace App\Presentation\Controller;

            use App\Infrastructure\Http\RequestInterface;
            use App\Infrastructure\Http\ResponseInterface;

            class {$className}
            {
                public function index(RequestInterface \$request, ResponseInterface \$response): ResponseInterface
                {
                    // TODO: Implement index method
                    return \$response->withBody('Hello from {$className}!');
                }

                public function show(RequestInterface \$request, ResponseInterface \$response): ResponseInterface
                {
                    \$id = \$request->getAttribute('id');
                    
                    // TODO: Implement show method
                    return \$response->withBody("Showing resource: {\$id}");
                }

                public function create(RequestInterface \$request, ResponseInterface \$response): ResponseInterface
                {
                    // TODO: Implement create method
                    return \$response->withBody('Create form');
                }

                public function store(RequestInterface \$request, ResponseInterface \$response): ResponseInterface
                {
                    // TODO: Implement store method
                    return \$response->withBody('Resource created');
                }

                public function edit(RequestInterface \$request, ResponseInterface \$response): ResponseInterface
                {
                    \$id = \$request->getAttribute('id');
                    
                    // TODO: Implement edit method
                    return \$response->withBody("Edit form for resource: {\$id}");
                }

                public function update(RequestInterface \$request, ResponseInterface \$response): ResponseInterface
                {
                    \$id = \$request->getAttribute('id');
                    
                    // TODO: Implement update method
                    return \$response->withBody("Resource {\$id} updated");
                }

                public function destroy(RequestInterface \$request, ResponseInterface \$response): ResponseInterface
                {
                    \$id = \$request->getAttribute('id');
                    
                    // TODO: Implement destroy method
                    return \$response->withBody("Resource {\$id} deleted");
                }
            }
        PHP;
    }
}
