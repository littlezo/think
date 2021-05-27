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
namespace littler\generate\factory;

use JaguarJack\MigrateGenerator\MigrateGenerator;
use little\generate\factory\Factory;
use littler\App;
use littler\exceptions\FailedException;
use littler\Utils;
use think\helper\Str;

class Migration extends Factory
{
    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \JaguarJack\MigrateGenerator\Exceptions\EmptyInDatabaseException
     */
    public function done(array $params): string
    {
        [$module, $tableName] = $params;

        // TODO: Implement done() method.
        $migrationPath = App::directory() . $module . DIRECTORY_SEPARATOR .
            'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR;

        App::makeDirectory($migrationPath);

        $migrateGenerator = (new MigrateGenerator('thinkphp'));

        $tables = $migrateGenerator->getDatabase()->getAllTables($tableName);

        $version = date('YmdHis');

        $file = $migrationPath . $version . '_' . $tableName . '.php';

        foreach ($tables as $table) {
            if ($table->getName() == $tableName) {
                $content = $migrateGenerator->getMigrationContent($table);
                $noPrefix = str_replace(Utils::tablePrefix(), '', $tableName);
                $_content = str_replace($tableName, $noPrefix, $content, $count);
                file_put_contents($file, $count == 1 ? $_content : $content);

                if (! file_exists($file)) {
                    throw new FailedException('migration generate failed');
                }
                $model = new class() extends \think\Model {
                    protected $name = 'migrations';
                };

                $model->insert([
                    'version' => $version,
                    'migration_name' => ucfirst(Str::camel($tableName)),
                    'start_time' => date('Y-m-d H:i:s'),
                    'end_time' => date('Y-m-d H:i:s'),
                ]);
                break;
            }
        }

        return $file;
    }
}
