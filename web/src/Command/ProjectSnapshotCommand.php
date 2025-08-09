<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:snapshot', description: 'Create a lightweight JSON snapshot of entities, controllers, forms, and templates.')]
class ProjectSnapshotCommand extends Command
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('out', null, InputOption::VALUE_REQUIRED, 'Output JSON file path (default: stdout)')
            ->addOption('web-only', null, InputOption::VALUE_NONE, 'Limit scan to Web layer (src/Entity/Web, Controller/Web, Form/Web, templates/web)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $projectDir = $this->kernel->getProjectDir();
        $srcDir = $projectDir . '/src';
        $tplDir = $projectDir . '/templates';

        $webOnly = (bool)$input->getOption('web-only');

        $snapshot = [
            'generated_at' => (new \DateTimeImmutable())->format('c'),
            'symfony_version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'paths' => [
                'project' => $projectDir,
                'src' => $srcDir,
                'templates' => $tplDir,
            ],
        ];

        // === ENTITIES ===
        $entityRoots = $webOnly
            ? [$srcDir . '/Entity/Web']
            : [$srcDir . '/Entity'];
        $snapshot['entities'] = $this->scanEntities($entityRoots);

        // === CONTROLLERS via Router ===
        $snapshot['routes'] = $this->collectRoutes();

        // === FORMS (static scan) ===
        $formRoots = $webOnly
            ? [$srcDir . '/Form/Web']
            : [$srcDir . '/Form'];
        $snapshot['forms'] = $this->scanForms($formRoots);

        // === TWIG templates (list) ===
        $twigRoots = $webOnly
            ? [$tplDir . '/web']
            : [$tplDir];
        $snapshot['templates'] = $this->scanTwig($twigRoots);

        $json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $out = $input->getOption('out');
        if ($out) {
            @mkdir(dirname($out), 0777, true);
            file_put_contents($out, $json . "\n");
            $io->success("Snapshot saved to {$out}");
        } else {
            $output->writeln($json);
        }

        return Command::SUCCESS;
    }

    /** @param string[] $roots */
    private function scanEntities(array $roots): array
    {
        $items = [];
        foreach ($roots as $root) {
            if (!is_dir($root)) { continue; }
            $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($rii as $file) {
                if ($file->isDir()) { continue; }
                if (substr($file->getFilename(), -4) !== '.php') { continue; }
                $path = $file->getPathname();
                $fqcn = $this->classFromFile($path);
                if (!$fqcn) { continue; }
                $info = [
                    'fqcn' => $fqcn,
                    'path' => $path,
                ];
                try {
                    if (class_exists($fqcn)) {
                        $ref = new \ReflectionClass($fqcn);
                        $info['short_name'] = $ref->getShortName();
                        $info['properties'] = [];
                        foreach ($ref->getProperties() as $prop) {
                            $p = [
                                'name' => $prop->getName(),
                                'type' => $prop->getType() ? (string)$prop->getType() : null,
                                'attributes' => [],
                            ];
                            foreach ($prop->getAttributes() as $attr) {
                                $a = $attr->getName();
                                if (str_starts_with($a, 'Doctrine\\ORM\\Mapping')) {
                                    $args = $attr->getArguments();
                                    $p['attributes'][] = ['name' => substr($a, strrpos($a, '\\')+1), 'args' => $args];
                                }
                            }
                            $info['properties'][] = $p;
                        }
                        // Class-level ORM attributes
                        $info['class_attributes'] = [];
                        foreach ($ref->getAttributes() as $attr) {
                            $a = $attr->getName();
                            if (str_starts_with($a, 'Doctrine\\ORM\\Mapping')) {
                                $info['class_attributes'][] = substr($a, strrpos($a, '\\')+1);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $info['reflection_error'] = $e->getMessage();
                }
                $items[] = $info;
            }
        }
        return $items;
    }

    private function collectRoutes(): array
    {
        $items = [];
        $routes = $this->router->getRouteCollection();
        foreach ($routes as $name => $route) {
            $defaults = $route->getDefaults();
            $controller = $defaults['_controller'] ?? null;
            $controllerClass = null;
            if (is_string($controller) && str_contains($controller, '::')) {
                [$controllerClass, $method] = explode('::', $controller, 2);
            }
            $items[] = [
                'name' => $name,
                'path' => $route->getPath(),
                'methods' => $route->getMethods(),
                'controller' => $controllerClass,
            ];
        }
        return $items;
    }

    /** @param string[] $roots */
    private function scanForms(array $roots): array
    {
        $items = [];
        foreach ($roots as $root) {
            if (!is_dir($root)) { continue; }
            $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($rii as $file) {
                if ($file->isDir()) { continue; }
                if (substr($file->getFilename(), -4) !== '.php') { continue; }
                $path = $file->getPathname();
                $fqcn = $this->classFromFile($path);
                if (!$fqcn) { continue; }

                $fields = [];
                $code = @file_get_contents($path) ?: '';
                if ($code) {
                    if (preg_match_all("/->add\(\s*'([^']+)'\s*,?/m", $code, $m)) {
                        $fields = array_values(array_unique($m[1]));
                    }
                }
                $items[] = [
                    'fqcn' => $fqcn,
                    'path' => $path,
                    'fields' => $fields,
                ];
            }
        }
        return $items;
    }

    /** @param string[] $roots */
    private function scanTwig(array $roots): array
    {
        $items = [];
        foreach ($roots as $root) {
            if (!is_dir($root)) { continue; }
            $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($rii as $file) {
                if ($file->isDir()) { continue; }
                if (!str_ends_with($file->getFilename(), '.html.twig')) { continue; }
                $path = $file->getPathname();
                $rel = str_replace($this->kernel->getProjectDir() . '/', '', $path);
                $info = [ 'path' => $rel ];
                $code = @file_get_contents($path) ?: '';
                if ($code) {
                    if (preg_match("/{%\\s*extends\\s*'([^']+)'\\s*%}/", $code, $m)) {
                        $info['extends'] = $m[1];
                    }
                    if (preg_match_all("/{%\\s*block\\s+([a-zA-Z0-9_]+)\\s*%}/", $code, $m)) {
                        $info['blocks'] = array_values(array_unique($m[1]));
                    }
                }
                $items[] = $info;
            }
        }
        return $items;
    }

    private function classFromFile(string $path): ?string
    {
        $code = @file_get_contents($path);
        if ($code === false) { return null; }
        $ns = '';
        $class = null;
        $tokens = token_get_all($code);
        $i = 0; $len = count($tokens);
        while ($i < $len) {
            $t = $tokens[$i];
            if (is_array($t) && $t[0] === T_NAMESPACE) {
                $ns = '';
                $i++;
                while ($i < $len && is_array($tokens[$i]) && ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NAME_QUALIFIED || $tokens[$i][0] === T_NS_SEPARATOR)) {
                    $ns .= $tokens[$i][1];
                    $i++;
                }
            }
            if (is_array($t) && $t[0] === T_CLASS) {
                // skip anonymous classes
                // ensure next significant token is class name
                $i++;
                while ($i < $len && (is_array($tokens[$i]) ? in_array($tokens[$i][0], [T_WHITESPACE, T_FINAL, T_ABSTRACT]) : false)) { $i++; }
                // read class name
                while ($i < $len && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) { $i++; }
                if ($i < $len && is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                    $class = $tokens[$i][1];
                    break;
                }
            }
            $i++;
        }
        if ($class) {
            return $ns ? $ns . '\\' . $class : $class;
        }
        return null;
    }
}
