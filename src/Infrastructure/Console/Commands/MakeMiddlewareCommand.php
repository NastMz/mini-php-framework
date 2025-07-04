<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

/**
 * MakeMiddlewareCommand class
 *
 * This command creates a new middleware class in the application.
 */
class MakeMiddlewareCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('make:middleware');
        $this->setDescription('Create a new middleware class');
        $this->addArgument('name', true, 'The name of the middleware');
    }

    /**
     * Execute the command to create a new middleware.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        $name = $arguments['name'] ?? null;
        
        if (!$name) {
            $this->error('Middleware name is required');
            return 1;
        }

        $className = $this->formatClassName($name);
        $fileName = $className . '.php';
        $filePath = __DIR__ . '/../../../../src/Infrastructure/Middleware/' . $fileName;

        if (file_exists($filePath)) {
            $this->error("Middleware '$className' already exists");
            return 1;
        }

        $template = $this->getMiddlewareTemplate($className);
        file_put_contents($filePath, $template);
        
        $this->success("Middleware '$className' created successfully at $filePath");
        return 0;
    }

    /**
     * Format the middleware name to a valid class name.
     *
     * @param string $name The raw name of the middleware.
     * @return string The formatted class name.
     */
    private function formatClassName(string $name): string
    {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        
        if (!str_ends_with($name, 'Middleware')) {
            $name .= 'Middleware';
        }
        
        return $name;
    }

    /**
     * Get the template for the middleware class.
     *
     * @param string $className The name of the middleware class.
     * @return string The template content.
     */
    private function getMiddlewareTemplate(string $className): string
    {
        return <<<PHP
            <?php
            declare(strict_types=1);

            namespace App\Infrastructure\Middleware;

            use App\Infrastructure\Http\RequestInterface;
            use App\Infrastructure\Http\ResponseInterface;

            class {$className} implements MiddlewareInterface
            {
                public function process(RequestInterface \$request, RequestHandlerInterface \$handler): ResponseInterface
                {
                    // TODO: Implement your middleware logic here
                    // Process the request before passing it to the next handler
                    
                    // Call the next middleware/handler
                    \$response = \$handler->handle(\$request);
                    
                    // Process the response before returning it
                    
                    return \$response;
                }
            }
        PHP;
    }
}
