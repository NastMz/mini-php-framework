<?php
declare(strict_types=1);

namespace App\Infrastructure\Templating;

use RuntimeException;

/**
 * TemplateEngine
 *
 * A simple template engine that supports:
 * - Template inheritance with @extends and @section
 * - Variable interpolation with {{ $var }}
 * - Section yields with @yield
 *
 * Templates are compiled to PHP files for performance.
 */
class TemplateEngine
{
    /**
     * @param string $viewsPath Path to the directory containing template files
     * @param string $cachePath Path to the directory where compiled templates will be cached
     */
    public function __construct(
        private string $viewsPath,
        private string $cachePath
    ) {
        if (! is_dir($this->cachePath) && ! mkdir($this->cachePath, 0755, true)) {
            throw new RuntimeException("Unable to create cache directory: {$this->cachePath}");
        }
    }

    /**
     * Render a template by name (without .php) with given variables.
     */
    public function render(string $template, array $vars = []): string
    {
        $sections = [];
        $output   = $this->renderInternal($template, $vars, $sections);
        return $output;
    }

    /**
     * Internal render that carries $sections for layout inheritance.
     *
     * @param array<string,string> $sections  Section contents from child templates
     */
    private function renderInternal(string $template, array $vars, array &$sections): string
    {
        $tplFile = rtrim($this->viewsPath, '/')
                 . DIRECTORY_SEPARATOR
                 . $template . '.php';

        if (! file_exists($tplFile)) {
            throw new RuntimeException("Template not found: {$tplFile}");
        }

        $contents = file_get_contents($tplFile);
        if ($contents === false) {
            throw new RuntimeException("Unable to read template: {$tplFile}");
        }

        // 1) Handle @extends('parent')
        if (preg_match('/@extends\(\s*[\'"](.+?)[\'"]\s*\)/', $contents, $m)) {
            $parent = $m[1];
            // remove the @extends directive
            $contents = preg_replace('/@extends\(\s*[\'"].+?[\'"]\s*\)/', '', $contents);

            // 2) Extract @section blocks
            $contents = preg_replace_callback(
                '/@section\(\s*[\'"](.+?)[\'"]\s*\)(.*?)@endsection/s',
                function($match) use (&$sections) {
                    $sections[$match[1]] = $match[2];
                    return '';
                },
                $contents
            );

            // recurse into parent, carrying collected $sections
            return $this->renderInternal($parent, $vars, $sections);
        }

        // 3) Compile if needed
        $cacheFile = $this->cachePath
                   . DIRECTORY_SEPARATOR
                   . str_replace(['/', '\\'], ['-', '_'], $template)
                   . '.php';

        if (!file_exists($cacheFile) || filemtime($cacheFile) < filemtime($tplFile)) {
            $this->compile($contents, $cacheFile);
        }

        // 4) Extract variables and sections into local scope
        extract($vars, EXTR_SKIP);
        // sections available as $sections
        /** @var array<string,string> $sections */

        // 5) Include compiled template and capture output
        ob_start();
        include $cacheFile;
        return (string) ob_get_clean();
    }

    /**
     * Compile raw template source into pure PHP and save to cache.
     */
    private function compile(string $src, string $cacheFile): void
    {
        // 1) Escape variables: {{ $var }}
        $compiled = preg_replace(
            '/\{\{\s*(.+?)\s*\}\}/',
            '<?php echo htmlspecialchars($1, ENT_QUOTES, \'UTF-8\'); ?>',
            $src
        );

        // 2) Yields: @yield('name')
        $compiled = preg_replace(
            '/@yield\(\s*[\'"](.+?)[\'"]\s*\)/',
            '<?php echo $sections[\'$1\'] ?? \'\'; ?>',
            $compiled
        );

        // 3) (Optionally) you could add more directives here…

        // 4) Write to cache
        file_put_contents($cacheFile, $compiled);
    }
}
