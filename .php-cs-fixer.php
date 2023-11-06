<?php

declare(strict_types=1);

use PhpCsFixer\Finder;
use PhpCsFixer\Config;
use PhpCsFixer\Console\ConfigurationResolver;

/**
 * Custom finder.
 */
final class CustomFinder extends Finder
{
    /** @var array */
    private $excludes = [
        'vendor',
    ];

    /** @var \PhpCsFixer\Console\ConfigurationResolver|null */
    private $configurationResolver = null;

    /** @var string */
    private $input = '';

    /** @var array */
    private $files = [];

    /** @var array */
    private $directories = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initConfigurationResolver();
        $this->initFiles();
        $this->initDirectories();
        $this->initFinder();
        $this->outputInfo();
    }

    /**
     * Initialize configuration resolver.
     *
     * The ConfigurationResolver is the class instantiating this .php_cs file, and thus instantiating this CustomFinder.
     * In order to be able to see if a file/directory has been passed on the CLI, we need the current instance of this
     * ConfigurationResolver, which can be obtained by looping through the backtrace.
     */
    private function initConfigurationResolver(): void
    {
        foreach (debug_backtrace() as $backtrace) {
            if (isset($backtrace['object']) && $backtrace['object'] instanceof ConfigurationResolver) {
                $this->configurationResolver = $backtrace['object'];

                return;
            }
        }
        die('Unable to initialize configuration resolver' . PHP_EOL);
    }

    /**
     * Initialize files.
     */
    private function initFiles(): void
    {
        if ($this->configurationResolver->getPath()) {
            $this->initFilesFromCli();
        } elseif (!posix_isatty(STDIN)) {
            $this->initFilesFromStdin();
        } else {
            $this->initFilesFromGit();
        }
    }

    /**
     * Initialize files from CLI.
     */
    private function initFilesFromCli(): void
    {
        $this->input = 'CLI';
        $this->files = $this->findFiles($this->configurationResolver->getPath());
    }

    /**
     * Initialize files from STDIN.
     */
    private function initFilesFromStdin(): void
    {
        $this->input = 'STDIN';
        $files = [];
        $paths = explode(PHP_EOL, trim(stream_get_contents(STDIN)));
        $paths = array_map(function ($path) {
            return $this->findFiles($path);
        }, $paths);

        $files = array_merge($files, ...$paths);

        $this->files = array_unique($files);
    }

    /**
     * Initialize files from Git.
     */
    private function initFilesFromGit(): void
    {
        $this->input = 'Git';
        $branch = $this->pipedExec('git rev-parse --abbrev-ref HEAD 2>/dev/null');
        echo sprintf('What is the destination branch of %s [master]: ', $branch);
        $destinationBranch = trim(fgets(STDIN)) ?: 'master';
        $branchExists = $this->pipedExec(sprintf('git branch --remotes 2>/dev/null | grep --extended-regexp "^(\*| ) origin/%s( |$)" 2>/dev/null', $destinationBranch));
        if ($branchExists === false) {
            die(sprintf("fatal: Couldn't find remote ref %s", $destinationBranch) . PHP_EOL);
        }
        $this->pipedExec(sprintf('(git diff origin/%s.. --name-only --diff-filter=ACMRTUXB 2>/dev/null; git diff --cached --name-only --diff-filter=ACMRTUXB 2>/dev/null) | grep "\.php$" 2>/dev/null | sort 2>/dev/null | uniq 2>/dev/null', $destinationBranch), $this->files);
        $repositoryRoot = $this->pipedExec('git rev-parse --show-toplevel 2>/dev/null');
        chdir($repositoryRoot);
    }

    /**
     * Initialize directories from files.
     */
    private function initDirectories(): void
    {
        $directories = [];
        foreach ($this->files as $file) {
            $directory = dirname($file);
            foreach ($this->excludes as $exclude) {
                if (strpos($directory . '/', $exclude . '/') === 0) {
                    continue 2;
                }
            }
            $directories[] = $directory;
        }
        $this->directories = array_unique($directories);
    }

    /**
     * Initialize finder.
     */
    private function initFinder(): void
    {
        $files = &$this->files;

        $this
            ->files()
            ->name('')
            ->depth('== 0')
            ->in('.')
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->filter(function (SplFileInfo $fileinfo) use ($files) {
                return in_array($fileinfo->__toString(), $files, false);
            });

        foreach ($this->files as $file) {
            $this->name(basename($file));
        }

        foreach ($this->directories as $directory) {
            $this->in($directory);
        }
    }

    /**
     * Output information.
     */
    private function outputInfo(): void
    {
        echo sprintf('Loaded %d file(s) from %s', count($this), $this->input) . PHP_EOL;
    }

    /**
     * Find files in path.
     *
     * @param string|mixed $path
     */
    private function findFiles($path): array
    {
        if (is_file($path)) {
            return (array) $path;
        }

        if (is_dir($path)) {
            $finder = Finder::create()
                ->files()
                ->name('*.php')
                ->in($path)
                ->ignoreDotFiles(false)
                ->ignoreVCS(true);

            return array_keys(iterator_to_array($finder, true));
        }

        return [];
    }

    /**
     * Execute an external program without broken pipes.
     *
     * @return string|mixed|null
     */
    private function pipedExec(string $command, array &$output = null, int &$returnVar = null)
    {
        $contents = '';
        $handle = popen($command . '; echo $?', 'r');
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        pclose($handle);

        $output = explode(PHP_EOL, trim($contents));
        $returnVar = (int) array_pop($output);

        return end($output);
    }
}

return (new Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        // default
        '@PSR2' => true,
        '@Symfony' => true,
        // see https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/README.rst
        'concat_space' => ['spacing' => 'one'],
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'no_blank_lines_before_namespace' => false,
        'ordered_imports' => true,
        'phpdoc_align' => false,
        'general_phpdoc_tag_rename' => false,
        'phpdoc_order' => true,
        'simplified_null_return' => false,
        'no_unused_imports' => true,
        'declare_strict_types' => true,
        'final_internal_class' => false,
        'general_phpdoc_annotation_remove' => [
            'annotations' => [
                'author',
                'copyright',
                'category',
                'version',
            ],
            'case_sensitive' => false,
        ],
        'global_namespace_import' => ['import_classes' => null],
        'list_syntax' => ['syntax' => 'short'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'], // according to the documentation this is the default, but it ain't
        'no_php4_constructor' => true,
        'no_superfluous_elseif' => false,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'remove_inheritdoc' => true],
        'php_unit_internal_class' => false,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_no_empty_return' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'ordered_class_elements' => ['order' => ['use_trait', 'constant', 'property', 'construct', 'destruct', 'phpunit', 'method']],
        'ternary_to_null_coalescing' => true,
    ])
    ->setFinder(CustomFinder::create());
