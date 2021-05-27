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

use littler\Utils;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\db\Column;

class TableColumn
{
    /**
     * tinyint.
     */
    public function tinyint(string $name, int $length): Column
    {
        return Column::tinyInteger($name);
    }

    /**
     * boolean.
     */
    public function boolean(string $name, int $length): Column
    {
        return Column::boolean($name);
    }

    /**
     * smallint.
     */
    public function smallint(string $name, int $length): Column
    {
        return Column::smallInteger($name);
    }

    /**
     * int.
     */
    public function int(string $name, int $length): Column
    {
        return Column::integer($name);
    }

    /**
     * mediumint.
     */
    public function mediumint(string $name, int $length): Column
    {
        return Column::mediumInteger($name);
    }

    /**
     * bigint.
     */
    public function bigint(string $name, int $length): Column
    {
        return Column::bigInteger($name);
    }

    /**
     * 浮点数.
     */
    public function float(string $name, int $length): Column
    {
        return Column::float($name);
    }

    /**
     * 浮点数.
     *
     * @param int $precision
     * @param int $scale
     */
    public function decimal(string $name, $precision = 8, $scale = 2): Column
    {
        return Column::decimal($name, $precision, $scale);
    }

    /**
     * string 类型.
     */
    public function varchar(string $name, int $length): Column
    {
        return Column::string($name, $length);
    }

    /**
     * char.
     */
    public function char(string $name, int $length): Column
    {
        return Column::char($name, $length);
    }

    /**
     * 普通文本.
     */
    public function text(string $name, int $length): Column
    {
        return Column::text($name);
    }

    /**
     * 小文本.
     */
    public function tinytext(string $name, int $length): Column
    {
        return Column::make($name, AdapterInterface::PHINX_TYPE_TEXT, ['length' => MysqlAdapter::TEXT_TINY]);
    }

    /**
     * 中长文本.
     */
    public function mediumtext(string $name, int $length): Column
    {
        return Column::mediumText($name);
    }

    /**
     * 超大文本.
     */
    public function longtext(string $name, int $length): Column
    {
        return Column::longText($name);
    }

    /**
     * binary.
     */
    public function binary(string $name, int $length): Column
    {
        return Column::binary($name);
    }

    /**
     * varbinary.
     */
    public function varbinary(string $name, int $length): Column
    {
        return Column::make($name, AdapterInterface::PHINX_TYPE_VARBINARY);
    }

    /**
     * tinyblob.
     */
    public function tinyblob(string $name, int $length): Column
    {
        return Column::make($name, AdapterInterface::PHINX_TYPE_BLOB, ['length' => MysqlAdapter::BLOB_TINY]);
    }

    /**
     * blob.
     */
    public function blob(string $name, int $length): Column
    {
        return Column::make($name, AdapterInterface::PHINX_TYPE_BLOB, ['length' => MysqlAdapter::BLOB_REGULAR]);
    }

    /**
     * mediumblob.
     */
    public function mediumblob(string $name, int $length): Column
    {
        return Column::make($name, AdapterInterface::PHINX_TYPE_BLOB, ['length' => MysqlAdapter::BLOB_MEDIUM]);
    }

    /**
     * longblob.
     */
    public function longblob(string $name, int $length): Column
    {
        return Column::make($name, AdapterInterface::PHINX_TYPE_BLOB, ['length' => MysqlAdapter::BLOB_LONG]);
    }

    /**
     * 时间类型.
     */
    public function date(string $name, int $length): Column
    {
        return Column::date($name);
    }

    /**
     * 日期时间.
     */
    public function datetime(string $name, int $length): Column
    {
        return Column::dateTime($name)->setOptions(['default' => 'CURRENT_TIMESTAMP']);
    }

    /**
     * 实践格式.
     */
    public function time(string $name, int $length): Column
    {
        return Column::time($name);
    }

    /**
     * 时间戳.
     */
    public function timestamp(string $name, int $length): Column
    {
        return Column::timestamp($name)->setOptions(['default' => 'CURRENT_TIMESTAMP']);
    }

    /**
     * enum 类型.
     *
     * @param $name
     * @param $values
     */
    public function enum(string $name, $values): Column
    {
        return Column::enum($name, is_string($values) ? Utils::stringToArrayBy($values) : $values);
    }

    /**
     * set 类型.
     *
     * @param $values
     */
    public function set(string $name, $values): Column
    {
        $values = is_string($values) ? Utils::stringToArrayBy($values) : $values;

        return Column::make($name, AdapterInterface::PHINX_TYPE_SET, compact('values'));
    }

    /**
     * json.
     */
    public function json(string $name): Column
    {
        return Column::json($name);
    }

    /**
     * uuid.
     */
    public function uuid(string $name): Column
    {
        return Column::uuid($name);
    }
}
