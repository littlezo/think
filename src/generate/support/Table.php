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
namespace littler\generate\support;

use littler\exceptions\FailedException;
use littler\Utils;
use Phinx\Db\Adapter\AdapterFactory;
use think\facade\Db;
use think\migration\db\Column;

class Table
{
    protected static $adapter = null;

    protected static $table = null;

    protected static $tableName = null;

    public function __construct(string $tableName)
    {
        self::$tableName = $tableName;
    }

    /**
     * create table.
     */
    public static function create(string $primaryKey, string $engine, string $comment): bool
    {
        self::getTable()
            ->setId($primaryKey)
            ->setPrimaryKey($primaryKey)
            ->setEngine($engine)
            ->setComment($comment)
            ->setCollation('utf8mb4_general_ci')
            ->create();

        return self::exist();
    }

    /**
     * 表是否存在.
     */
    public static function exist(): bool
    {
        return self::getTable()->exists();
    }

    /**
     * 删除表.
     */
    public static function drop(): bool
    {
        if (! self::exist()) {
            throw new FailedException(sprintf('table [%s] not exist, drop failed', self::$tableName));
        }

        self::getTable()->drop();

        return ! self::exist();
    }

    /**
     * 新增 column.
     *
     * @param mixed $column
     */
    public static function addColumn($column): bool
    {
        if ($column instanceof \Closure) {
            $column = $column();
        }

        if (! $column instanceof Column) {
            throw new FailedException('Column Must Be "think\migration\db\Column');
        }

        // 新增字段
        self::getTable()
            ->addColumn($column)
            ->update();

        return self::hasColumn($column->getName());
    }

    /**
     * 是否存在 column.
     */
    public static function hasColumn(string $column): bool
    {
        return self::getTable()->hasColumn($column);
    }

    /**
     * 获取表结构信息.
     */
    public static function columns(): array
    {
        return array_values(Db::getFields(Utils::tableWithPrefix(self::$tableName)));
    }

    /**
     * 删除 column.
     */
    public static function dropColumn(string $column): bool
    {
        self::getTable()->removeColumn($column)->update();

        if (self::getTable()->hasColumn($column)) {
            throw new FailedException('remove column [' . $column . '] failed');
        }

        return true;
    }

    /**
     * 唯一索引.
     *
     * @param string| array $columns
     */
    public static function addUniqueIndex($columns)
    {
        self::getTable()->addIndex($columns, [
            'unique' => true, 'name' => self::$tableName . '_' . (is_string($columns) ? $columns : implode('_', $columns)),
        ])->update();
    }

    /**
     * 添加普通索引.
     *
     * @param string| array $columns
     */
    public static function addIndex($columns)
    {
        self::getTable()->addIndex($columns, [
            'name' => self::$tableName . '_' . (is_string($columns) ? $columns : implode('_', $columns)),
        ])->update();
    }

    /**
     * 添加全文索引.
     *
     * @param string| array $columns
     */
    public static function addFulltextIndex($columns)
    {
        self::getTable()->addIndex($columns, [
            'type' => 'fulltext',
            'name' => self::$tableName . '_' . (is_string($columns) ? $columns : implode('_', $columns)),
        ])->update();
    }

    /**
     * 删除 index.
     */
    public static function dropIndex(string $column)
    {
        self::getTable()->removeIndex([$column])->update();
    }

    /**
     * column 是否是索引.
     */
    public static function isIndex(string $column): bool
    {
        return self::getTable()->hasIndex($column);
    }

    /**
     * 获取适配器.
     *
     * @return mixed
     */
    public static function getAdapter()
    {
        if (self::$adapter) {
            return self::$adapter;
        }

        $options = self::getDbConfig();

        $adapter = AdapterFactory::instance()->getAdapter($options['adapter'], $options);

        if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
            $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
        }

        self::$adapter = $adapter;

        return $adapter;
    }

    /**
     * 获取 table 对象
     */
    protected static function getTable(): \think\migration\db\Table
    {
        if (self::$table) {
            return self::$table;
        }

        return (new \think\migration\db\Table(Utils::tableWithoutPrefix(self::$tableName)))->setAdapter(self::getAdapter());
    }

    /**
     * 获取数据库配置.
     */
    protected static function getDbConfig(): array
    {
        $default = app()->config->get('database.default');

        $config = app()->config->get("database.connections.{$default}");

        if ($config['deploy'] == 0) {
            $dbConfig = [
                'adapter' => $config['type'],
                'host' => $config['hostname'],
                'name' => $config['database'],
                'user' => $config['username'],
                'pass' => $config['password'],
                'port' => $config['hostport'],
                'charset' => $config['charset'],
                'table_prefix' => $config['prefix'],
            ];
        } else {
            $dbConfig = [
                'adapter' => explode(',', $config['type'])[0],
                'host' => explode(',', $config['hostname'])[0],
                'name' => explode(',', $config['database'])[0],
                'user' => explode(',', $config['username'])[0],
                'pass' => explode(',', $config['password'])[0],
                'port' => explode(',', $config['hostport'])[0],
                'charset' => explode(',', $config['charset'])[0],
                'table_prefix' => explode(',', $config['prefix'])[0],
            ];
        }

        $table = app()->config->get('database.migration_table', 'migrations');

        $dbConfig['default_migration_table'] = $dbConfig['table_prefix'] . $table;

        return $dbConfig;
    }
}
