<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:export:web',
    description: 'Exportuje OBSAH vybraných částí projektu (Entities/Controllers/Forms/Repositories/Templates/Services) do jednoho ZIPu nebo MD.'
)]
class ProjectExportCommand extends Command
{
    /*
    php bin/console app:export:web --web-only --include=config,entities,controllers,forms,repositories,templates,services --out var/tt_web_export.zip --format=zip

  */

    public function __construct(private readonly KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('out', null, InputOption::VALUE_REQUIRED, 'Cesta k výstupnímu souboru (např. var/tt_web_export.zip)')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'zip|md', 'zip')
            ->addOption('include', null, InputOption::VALUE_REQUIRED,
                'Seznam částí: entities,controllers,forms,repositories,templates,services,config (čárkou)',
                'entities,controllers,forms,repositories,templates,services'
            )
            ->addOption('web-only', null, InputOption::VALUE_NONE,
                'Omezí export na Web vrstvu; pro services zahrne i Shared (src/Service/Web + src/Service/Shared)'
            )
            ->addOption('max-file-size', null, InputOption::VALUE_REQUIRED, 'Max velikost souboru v kB (0 = bez limitu)', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectDir = $this->kernel->getProjectDir();
        $src = $projectDir . '/src';
        $tpl = $projectDir . '/templates';
        $cfg = $projectDir . '/config';

        $format = strtolower((string)$input->getOption('format'));
        $out    = (string)$input->getOption('out');
        if (!$out) { $io->error('Zadej --out (např. var/tt_web_export.zip)'); return Command::FAILURE; }

        $webOnly = (bool)$input->getOption('web-only');
        $maxKb   = max(0, (int)$input->getOption('max-file-size'));
        $limit   = $maxKb > 0 ? $maxKb * 1024 : 0;

        $include = array_filter(array_map('trim', explode(',', (string)$input->getOption('include'))));
        $parts = array_flip($include);

        // Kořeny podle voleb
        $roots = [];
        if (isset($parts['entities']))     { $roots['entities']     = $webOnly ? [$src.'/Entity/Web']       : [$src.'/Entity']; }
        if (isset($parts['controllers']))  { $roots['controllers']  = $webOnly ? [$src.'/Controller/Web']   : [$src.'/Controller']; }
        if (isset($parts['forms']))        { $roots['forms']        = $webOnly ? [$src.'/Form/Web']         : [$src.'/Form']; }
        if (isset($parts['repositories'])) { $roots['repositories'] = $webOnly ? [$src.'/Repository/Web']   : [$src.'/Repository']; }
        if (isset($parts['templates']))    { $roots['templates']    = $webOnly ? [$tpl.'/web']              : [$tpl]; }
        if (isset($parts['services'])) {
            if ($webOnly) {
                // SPECIFICKY: když je web-only, chci Web i Shared služby
                $roots['services'] = [
                    $src.'/Service/Web',
                    $src.'/Service/Shared',
                ];
            } else {
                // Bez omezení – vezmi celý Service strom
                $roots['services'] = [$src.'/Service'];
            }
        }
        if (isset($parts['config']))       { $roots['config']       = [$cfg.'/routes', $cfg.'/packages/security.yaml']; }

        // Sbírej soubory
        $files = [];
        foreach ($roots as $group => $paths) {
            foreach ($paths as $path) {
                if (!file_exists($path)) { continue; }
                if (is_dir($path)) {
                    $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
                    foreach ($rii as $file) {
                        if ($file->isDir()) { continue; }
                        $p = $file->getPathname();
                        if ($this->allow($p)) {
                            if ($limit && filesize($p) > $limit) { continue; }
                            $files[] = $p;
                        }
                    }
                } else {
                    if ($this->allow($path)) {
                        if ($limit && filesize($path) > $limit) { continue; }
                        $files[] = $path;
                    }
                }
            }
        }

        // Odstraň duplicity
        $files = array_values(array_unique($files));

        // Výstup
        if ($format === 'zip') {
            @mkdir(dirname($out), 0777, true);
            $zip = new \ZipArchive();
            if ($zip->open($out, \ZipArchive::OVERWRITE | \ZipArchive::CREATE) !== true) {
                $io->error('Nejde otevřít ZIP: '.$out);
                return Command::FAILURE;
            }
            foreach ($files as $p) {
                $rel = ltrim(str_replace($projectDir, '', $p), '/\\');
                $zip->addFile($p, $rel);
            }
            $zip->close();
            $io->success('Export hotový: '.$out.' ('.count($files).' souborů)');
        } elseif ($format === 'md') {
            @mkdir(dirname($out), 0777, true);
            $md = "# TinyTransport Web Export\n\nVytvořeno: ".(new \DateTimeImmutable())->format('c')."\n\n";
            foreach ($files as $p) {
                $rel = ltrim(str_replace($projectDir, '', $p), '/\\');
                $code = @file_get_contents($p) ?: '';
                $md .= "\n---\n## ".$rel."\n\n";
                $md .= "```".$this->langByExt($p)."\n".$code."\n```\n";
            }
            file_put_contents($out, $md);
            $io->success('Export MD hotový: '.$out.' ('.count($files).' souborů)');
        } else {
            $io->error('Neznámý formát: '.$format.' (použij zip|md)');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function allow(string $path): bool
    {
        $bn = basename($path);
        if (str_starts_with($bn, '.')) { return false; }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $ok = ['php','twig','yaml','yml'];
        return in_array($ext, $ok, true);
    }

    private function langByExt(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'php' => 'php',
            'twig' => 'twig',
            'yaml', 'yml' => 'yaml',
            default => ''
        };
    }
}
