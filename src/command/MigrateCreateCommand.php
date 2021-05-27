<?php

declare(strict_types=1);
/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
namespace littler\command;

use littler\App;
use Phinx\Util\Util;
use RuntimeException;
use think\console\Input;
use think\console\input\Argument as InputArgument;
use think\console\Output;
use think\exception\InvalidArgumentException;
use think\migration\command\migrate\Create;

class MigrateCreateCommand extends Create
{
    /*
     *
    * {@inheritdoc}
    */
    protected function configure()
    {
        $this->setName('lz-migrate:create')
            ->setDescription('Create a new migration')
            ->addArgument('module', InputArgument::REQUIRED, 'the module where you create')
            ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the migration?')
            ->setHelp(sprintf('%sCreates a new database migration%s', PHP_EOL, PHP_EOL));
    }

    /**
     * Create the new migration.
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function execute(Input $input, Output $output)
    {
        $module = $input->getArgument('module');

        $className = $input->getArgument('name');

        $path = $this->create($module, $className);

        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', realpath($path)));
    }

    /**
     * @param $module
     * @param $className
     */
    protected function create($module, $className): string
    {
        $path = App::makeDirectory(App::moduleMigrationsDirectory($module));

        if (! Util::isValidPhinxClassName($className)) {
            throw new InvalidArgumentException(sprintf('The migration class name "%s" is invalid. Please use CamelCase format.', $className));
        }

        if (! Util::isUniqueMigrationClassName($className, $path)) {
            throw new InvalidArgumentException(sprintf('The migration class name "%s" already exists', $className));
        }

        // Compute the file path
        $fileName = Util::mapClassNameToFileName($className);

        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($filePath)) {
            throw new InvalidArgumentException(sprintf('The file "%s" already exists', $filePath));
        }

        // Verify that the template creation class (or the aliased class) exists and that it implements the required interface.
        $aliasedClassName = null;

        // Load the alternative template if it is defined.
        $contents = file_get_contents($this->getTemplate());

        // inject the class names appropriate to this migration
        $contents = strtr($contents, [
            'MigratorClass' => $className,
        ]);

        if (file_put_contents($filePath, $contents) === false) {
            throw new RuntimeException(sprintf('The file "%s" could not be written to', $path));
        }

        return $filePath;
    }

    protected function getTemplate()
    {
        return __DIR__ . '/stubs/migrate.stub';
    }
}
