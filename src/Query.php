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
namespace littler;

use think\helper\Str;
use think\Paginator;

class Query extends \think\db\Query
{
    /**
     * @param mixed $model
     */
    public function lzJoin($model, string $joinField, string $currentJoinField, array $field = [], string $type = 'INNER', array $bind = []): Query
    {
        $tableAlias = null;

        if (is_string($model)) {
            $table = app($model)->getTable();
        } else {
            [$model, $tableAlias] = $model;
            $table = app($model)->getTable();
        }

        // 合并字段
        $this->options['field'] = array_merge($this->options['field'] ?? [], array_map(function ($value) use ($table, $tableAlias) {
            return ($tableAlias ?: $table) . '.' . $value;
        }, $field));

        return $this->join($tableAlias ? sprintf('%s %s', $table, $tableAlias) : $table, sprintf('%s.%s=%s.%s', $tableAlias ? $tableAlias : $table, $joinField, $this->getAlias(), $currentJoinField), $type, $bind);
    }

    /**
     * @param mixed $model
     */
    public function lzLeftJoin($model, string $joinField, string $currentJoinField, array $field = [], array $bind = []): Query
    {
        return $this->lzJoin($model, $joinField, $currentJoinField, $field, 'LEFT', $bind);
    }

    /**
     * @param mixed $model
     */
    public function lzRightJoin($model, string $joinField, string $currentJoinField, array $field = [], array $bind = []): Query
    {
        return $this->lzJoin($model, $joinField, $currentJoinField, $field, 'RIGHT', $bind);
    }

    /**
     * rewrite.
     *
     * @param array|string $field
     * @param bool $needAlias
     * @return $this|Query
     */
    public function withoutField($field, $needAlias = false)
    {
        if (empty($field)) {
            return $this;
        }

        if (is_string($field)) {
            $field = array_map('trim', explode(',', $field));
        }

        // 过滤软删除字段
        if ($this->model->hasField($this->model->getDeleteAtField())) {
            $field[] = $this->model->getDeleteAtField();
        }

        // 字段排除
        $fields = $this->getTableFields();
        $field = $fields ? array_diff($fields, $field) : $field;
        // dd($field);

        if (isset($this->options['field'])) {
            $field = array_merge((array) $this->options['field'], $field);
        }

        $this->options['field'] = array_unique($field);

        if ($needAlias) {
            $alias = $this->getAlias();
            $this->options['field'] = array_map(function ($field) use ($alias) {
                return $alias . '.' . $field;
            }, $this->options['field']);
        }

        return $this;
    }

    /**
     * @param array $params
     */
    public function lzSearch($params = []): Query
    {
        $params = empty($params) ? \request()->param() : $params;

        if (empty($params)) {
            return $this;
        }

        foreach ($params as $field => $value) {
            $method = 'search' . Str::studly($field) . 'Attr';
            // value in [null, '']
            if ($value !== null && $value !== '' && method_exists($this->model, $method)) {
                $this->model->{$method}($this, $value, $params);
            }
        }
        return $this;
    }

    /**
     * 快速搜索.
     *
     * @param array $params
     */
    public function quickSearch($params = []): Query
    {
        $requestParams = \request()->param();

        if (empty($params) && empty($requestParams)) {
            return $this;
        }

        foreach ($requestParams as $field => $value) {
            if (isset($params[$field])) {
                // ['>', value] || value
                if (is_array($params[$field])) {
                    $this->where($field, $params[$field][0], $params[$field][1]);
                } else {
                    $this->where($field, $value);
                }
            } else {
                // 区间范围 start_数据库字段 & end_数据库字段
                $startPos = strpos($field, 'start_');
                if ($startPos === 0) {
                    $this->where(str_replace('start_', '', $field), '>=', strtotime($value));
                }
                $endPos = strpos($field, 'end_');
                if ($endPos === 0) {
                    $this->where(str_replace('end_', '', $field), '<=', strtotime($value));
                }
                // 模糊搜索
                if (Str::contains($field, 'like')) {
                    [$operate, $field] = explode('_', $field);
                    if ($operate === 'like') {
                        $this->whereLike($field, $value);
                    } elseif ($operate === '%like') {
                        $this->whereLeftLike($field, $value);
                    } else {
                        $this->whereRightLike($field, $value);
                    }
                }

                // = 值搜索
                if ($value || is_numeric($value)) {
                    $this->where($field, $value);
                }
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return isset($this->options['alias']) ? $this->options['alias'][$this->getTable()] : $this->getTable();
    }

    /**
     * rewrite.
     *
     * @param mixed $condition
     * @param string $option
     */
    public function whereLike(string $field, $condition, string $logic = 'AND', $option = 'both'): Query
    {
        switch ($option) {
          case 'both':
              $condition = '%' . $condition . '%';
              break;
          case 'left':
              $condition = '%' . $condition;
              break;
          default:
              $condition .= '%';
        }

        if (strpos($field, '.') === false) {
            $field = $this->getAlias() . '.' . $field;
        }

        return parent::whereLike($field, $condition, $logic);
    }

    /**
     * @param $condition
     */
    public function whereLeftLike(string $field, $condition, string $logic = 'AND'): Query
    {
        return $this->where($field, $condition, $logic, 'left');
    }

    /**
     * @param $condition
     */
    public function whereRightLike(string $field, $condition, string $logic = 'AND'): Query
    {
        return $this->where($field, $condition, $logic, 'right');
    }

    /**
     * 额外的字段.
     *
     * @param $fields
     */
    public function addFields($fields): Query
    {
        if (is_string($fields)) {
            $this->options['field'][] = $fields;

            return $this;
        }

        $this->options['field'] = array_merge($this->options['field'], $fields);

        return $this;
    }

    public function paginate($listRows = null, $simple = false): Paginator
    {
        if (! $listRows) {
            $limit = \request()->param('limit');
            $listRows = $limit ?: BaseModel::$limit;
        }
        // dd($simple);
        return parent::paginate($listRows, $simple);
    }

    /**
     * 默认排序.
     *
     * @param string $order
     * @return $this
     */
    public function lzOrder($order = 'desc')
    {
        if (in_array('sort', array_keys($this->getFields()))) {
            $this->order($this->getTable() . '.sort', $order);
        }

        if (in_array('weight', array_keys($this->getFields()))) {
            $this->order($this->getTable() . '.weight', $order);
        }
        // dd($this->getAutoPk());
        $this->order($this->getTable() . '.' . $this->getAutoPk(), $order);

        return $this;
    }

    /**
     * 新增 Select 子查询.
     *
     * @return $this
     */
    public function addSelectSub(callable $callable, string $as)
    {
        $this->field(sprintf('%s as %s', $callable()->buildSql(), $as));

        return $this;
    }

    /**
     * 字段增加.
     *
     * @param $field
     * @param int $amount
     * @throws \think\db\exception\DbException
     * @return int
     */
    public function setInc($field, $amount = 1)
    {
        return $this->inc($field, $amount)->update();
    }

    /**
     * 字段减少.
     *
     * @param $field
     * @param int $amount
     * @throws \think\db\exception\DbException
     * @return int
     */
    public function setDec($field, $amount = 1)
    {
        return $this->dec($field, $amount)->update();
    }

    public function getAutoPk()
    {
        return $this->connection->getPk($this->getTable());
    }
}
