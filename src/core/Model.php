<?php

declare(strict_types=1);

namespace maike\core;

use think\Model as BaseModel;
use maike\trait\ErrorTrait;

/**
 * 模型基类
 * @package maike\core
 */
abstract class Model extends BaseModel
{
    use ErrorTrait;

    // 模型别名
    protected $alias = '';

    protected $join__getList = [];

    protected $join__getAll = [];

    protected $with = [];

    /**
     * 模型基类初始化
     */
    public static function init()
    {
        parent::init();
    }

    /**
     * 查找单条记录
     * @param mixed $where 查询条件
     * @param array $with 关联查询
     * @return array|static|null
     */
    public static function get($where, array $with = [])
    {
        try {
            $model = new static;
            $with = is_array($with) && count($with) > 0 ? $with : $model->with;
            $query = $model->with($with);
            return is_array($where) ? $query->where($where)->find() : $query->find((int)$where);
        } catch (\think\Exception $e) {
            return null;
        }
    }

    /**
     * 查询分页数据
     * @param array $where 查询条件
     * @param string $order 排序
     * @param int $pagesize 分页大小
     * @param array $with 预载入
     * @return \think\Collection
     */
    public function getList($where = false, $order = '', $pagesize = 10, $with = [])
    {
        $with = is_array($with) && count($with) > 0 ? $with : $this->with;
        if ($this->join__getList && !empty($this->join__getList) && is_array($this->join__getList) && count($this->join__getList) > 0) {
            return $this->setJoinQuery($this->join__getList, $this->alias)->with($with)->order($order)->paginate($pagesize);
        }
        return $this->with($with)->where($where)->order($order)->paginate($pagesize);
    }

    /**
     * 查询获取所有数据
     * @param array $where 查询条件
     * @param string $order 排序
     * @param int $limit 返回记录条数
     * @param array $with 预加入
     * @return \think\Collection
     */
    public function getAll($where = false, $order = '', $limit = 1000, $with = [])
    {
        $with = is_array($with) && count($with) > 0 ? $with : $this->with;
        if ($this->join__getAll && !empty($this->join__getAll) && is_array($this->join__getAll) && count($this->join__getAll) > 0) {
            return $this->setJoinQuery($this->join__getAll, $this->alias)->with($with)->order($order)->limit($limit)->select();
        }
        return $this->with($with)->where($where)->order($order)->limit($limit)->select();
    }

    /**
     * 更新一条数据
     * @param array $data 更新的数据
     * @param array|int $where 更新条件
     * @return bool
     */
    public static function updateOne(array $data, $where)
    {
        $model = new static;
        if (!is_array($where)) {
            $where = [$model->pk => (int)$where];
        }
        return $model->update($data, $where);
    }

    /**
     * 批量更新多条数据(支持带where条件)
     * @param iterable $dataSet [['data'=>[], 'where'=>1],['data'=>[], 'where'=>[['id','=',1]]]]
     * @return array|false
     */
    public function batchUpdate(iterable $dataSet)
    {
        if (empty($dataSet)) {
            return false;
        }
        return $this->transaction(function () use ($dataSet) {
            $result = [];
            foreach ($dataSet as $key => $item) {
                $result[$key] = self::updateOne($item['data'], $item['where']);
            }
            return $result;
        });
    }

    /**
     * 批量新增数据
     * @param array $dataSet [['id'=>101, 'name'=>'li'],['id'=>102, 'name'=>'wang']]
     * @return array|false
     */
    public function batchCreate(array $dataSet)
    {
        if (empty($dataSet)) {
            return false;
        }
        return $this->transaction(function () use ($dataSet) {
            $result = [];
            foreach ($dataSet as $key => $item) {
                $result[$key] = self::create($item, $this->field);
            }
            return $result;
        });
    }

    /**
     * 批量删除记录
     * @param array $where
     * @return bool|int
     */
    public static function batchDelete(array $where)
    {
        return (new static)->where($where)->delete();
    }

    /**
     * 字段值自增
     * @param array|int|bool $where
     * @param string $field
     * @param float $step
     * @return mixed
     */
    public static function setInc($where, string $field, float $step = 1)
    {
        $model = new static;
        if (is_numeric($where)) {
            $where = [$model->getPk() => (int)$where];
        }
        return $model->where($where)->inc($field, $step)->update();
    }

    /**
     * 字段值自减
     * @param array|int|bool $where
     * @param string $field
     * @param float $step
     * @return mixed
     */
    public static function setDec($where, string $field, float $step = 1)
    {
        $model = new static;
        if (is_numeric($where)) {
            $where = [$model->getPk() => (int)$where];
        }
        return $model->where($where)->dec($field, $step)->update();
    }

    /**
     * 返回查询总记录数
     *
     * @param array $where
     * @param boolean $isCache
     * @return integer
     */
    public static function getCount($where = null, $isCache = false)
    {
        try {
            $model = new static;
            return is_array($where) ? $model->where($where)->cache($isCache)->count() : $model->cache($isCache)->count();
        } catch (\think\Exception $e) {
            return 0;
        }
    }

    /**
     * 合计字段值
     *
     * @param string $field
     * @param array $where
     * @param boolean $isCache
     * @return float
     */
    public static function getSum($field, $where = null, $isCache = false)
    {
        try {
            $model = new static;
            return is_array($where) ? $model->where($where)->cache($isCache)->sum($field) : $model->cache($isCache)->sum($field);
        } catch (\think\Exception $e) {
            return 0;
        }
    }

    /**
     * 设置JOIN查询
     * @param string $alias
     * @param array $join
     * @return $this
     */
    public function setJoinQuery(array $join = [], string $alias = '', string $field = '')
    {
        if ($join && !empty($join) && is_array($join)) {
            $aliasValue = $alias ?: $this->alias;
            $query = $this->alias($aliasValue)->field(empty($field) ? "{$aliasValue}.*" : $field);
            foreach ($join as $item) {
                if (is_array($item[0]) && count($item[0]) > 1) {
                    $tb = $item[0];
                    $tbName = $tb[0];
                    $tbAlias = $tb[1];
                } else {
                    $tbName = $item[0];
                    $tbAlias = $item[0];
                }
                $query->join($tbName . " " . $tbAlias, "{$tbAlias}.{$item[1]}={$aliasValue}." . ($item[2] ?? $item[1]));
            }
            return $query;
        }
        return $this;
    }
}
