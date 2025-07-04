<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

class MakeMiddlewareCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('make:middleware');
        $this->setDescription('Create a new middleware class');
        $this->addArgument('name', true, 'The name of the middleware');
    }

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
