<?php

namespace maike\util;

/**
 * 数组工具类
 * @package maike\util
 */
class ArrUtil
{
    /**
     * 数组转换为字符串
     * 
     * @param  array  $arr  要连接的数组
     * @param  string $glue 分割符
     * @return string
     */
    public static function ToString(array $arr, string $glue = ','): string
    {
        if (empty($arr) || !is_array($arr)) {
            return '';
        } else {
            return implode($glue, $arr);
        }
    }

    /**
     * 数组字符串值转为数值
     * 
     * @param  array|string  $arr  要连接的数组
     * @return array
     */
    public static function ValueToInt(array|string $arr): array
    {
        if (!$arr || empty($arr)) return [];
        if (is_string($arr) && !empty($arr)) {
            $arr = StrUtil::ToArray($arr);
        }
        return json_decode('[' . join(',', $arr) . ']', true);
    }

    /**
     * 数组转换成树型数组
     * 
     * @param array $arr 要转换的数组
     * @param string $pk 主键名
     * @param string $pid 父ID键名
     * @param string $child 子元素键名
     * @return array
     */
    public static function ToTree(array $arr, int $root = -1, string $pk = 'id', string $pid = 'parent_id', string $child = 'children', bool $onlyChild = false): array
    {
        $tree = [];
        if (is_array($arr)) {
            if ($root < 0) {
                $arr = self::ColumnToKey($arr, $pk);
                foreach ($arr as $item) {
                    if (isset($arr[$item[$pid]])) {
                        $arr[$item[$pid]][$child][] = &$arr[$item[$pk]];
                    } else {
                        $tree[] = &$arr[$item[$pk]];
                    }
                }
            } else {
                $tree = self::ColumnToKey($arr, $pk);
                foreach ($tree as $item) {
                    $tree[$item[$pid]][$child][] = &$tree[$item[$pk]];
                }
                return isset($tree[$root]) ? ($onlyChild ? $tree[$root][$child] : [$tree[$root]]) : [];
            }
        }
        return $tree;
    }

    /**
     * 二维数组排序
     * 
     * @param array $arr 数组
     * @param string $keys 排序的键名
     * @param bool $desc 排序类型
     * @return array
     */
    public static function Sort($arr, $keys, $desc = false): array
    {
        $key_value = $new_array = array();
        foreach ($arr as $k => $v) {
            $key_value[$k] = $v[$keys];
        }
        if ($desc) {
            arsort($key_value);
        } else {
            asort($key_value);
        }
        reset($key_value);
        foreach ($key_value as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /**
     * 多维数组排序
     *
     * @param  [type] $args          [description]
     * @return [type]                [description]
     */
    public static function MSort(...$args)
    {
        $arr = array_shift($args); // 取到要排序的数组，剩下的为要排序的键和排序类型
        $sort_arg = [];
        foreach ($args as $arg) {
            // 这里主要是为了得到排序的key对应的值
            $sort = $arr;
            if (is_string($arg)) {
                $arg = explode('.', $arg); // 我设定参数里面多维数组下的键，用‘.’连接下级的键，这里得到键，然后下面循环取得数组$arr里面该键对应的值
                foreach ($arg as $key) {
                    $sort = array_column($sort, $key); // 每次循环$sort的维度就会减一
                }
                $sort_arg[] = $sort;
            } else {
                $sort_arg[] = $arg; // 排序方法SORT_ASC、SORT_DESC等
            }
        }
        $sort_arg[] = &$arr; // 这个数组大致结构为：[$sort, SORT_ASC, $sort2, SORT_DESC,$arr]
        call_user_func_array('array_multisort', $sort_arg); // 因为参数不确定数量，所以不能直接array_multisort($sort, SORT_ASC, $sort2, SORT_DESC,$arr)，用call_user_func_array执行
        return ($arr);
    }

    /**
     * 给数据追加属性
     *
     * @param mixed $arr
     * @param array $defaultData
     * @return array
     */
    public static function AppendAttr(&$arr, $defaultData): array
    {
        array_walk($arr, function (&$value, $key, $defaultData) {
            $value = array_merge($value, $defaultData);
        }, $defaultData);
        return $arr;
    }

    /**
     * 从数组中选取属性
     * 
     * @param array $arr
     * @param array $keys
     * @return array
     */
    public static function Pick($arr, array $keys): array
    {
        $dataset = [];
        foreach ($arr as $key => $item) {
            in_array($key, $keys) && $dataset[$key] = $item;
        }
        return $dataset;
    }

    /**
     * 多维数组移除指定的列（属性）
     * 
     * @param array $arr
     * @param string $column
     * @return array
     */
    public static function Exclude($arr, $columns): array
    {
        $columnArr = [];
        foreach ($arr as $key => $item) {
            $temp = $item;
            foreach ($columns as $index) {
                if (isset($temp[$index])) unset($temp[$index]);
            }
            $columnArr[$key] = $temp;
        }
        return $columnArr;
    }

    /**
     * 获取多维数组中指定的列
     * 
     * @param array $arr
     * @param string $column
     * @return array
     */
    public static function Column($arr, $column): array
    {
        $columnArr = [];
        foreach ($arr as $item) {
            isset($item[$column]) && $columnArr[] = $item[$column];
        }
        return $columnArr;
    }

    /**
     * 获取数组中指定的列 (多列)
     * 
     * @param array $arr
     * @param array $columns
     * @return array
     */
    public static function Columns($arr, $columns): array
    {
        $columnArr = [];
        foreach ($arr as $item) {
            $temp = [];
            foreach ($columns as $index) {
                $temp[$index] = $item[$index];
            }
            $columnArr[] = $temp;
        }
        return $columnArr;
    }

    /**
     * 返回数组中KEY等于某值的项
     *
     * @param array $arr
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function Item($arr, $key, $value)
    {
        foreach ($arr as $item) {
            if ($item[$key] == $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * 获取数组中指定KEY的和
     * 
     * @param array $arr
     * @param string $column
     * @return float|int
     */
    public static function ColumnSum($arr, $column)
    {
        $sum = 0;
        foreach ($arr as $item) {
            $sum += $item[$column] * 100;
        }
        return $sum / 100;
    }

    /**
     * 把数组中某列的值设置为key
     * 
     * @param array $arr
     * @param string|integer $key
     * @return array
     */
    public static function ColumnToKey($arr, $key): array
    {
        $data = [];
        foreach ($arr as $item) {
            $data[$item[$key]] = $item;
        }
        return $data;
    }


    /**
     * 查找数组中指定值的项
     * 
     * @param array $arr 数组
     * @param string $searchKey 查找的索引
     * @param mixed $searchVal 查找的值
     * @param string $compare 比较运算符
     * @return iterable|array
     */
    public static function Search(array $arr, string $searchKey, mixed $searchVal, string $compare = '='): array
    {
        $new = [];
        foreach ($arr as $key => $item) {
            if (!isset($item[$searchKey])) continue;
            switch ($compare) {
                case '>':
                    $item[$searchKey] > $searchVal && $new[$key] = $item;
                    break;
                case '>=':
                    $item[$searchKey] >= $searchVal && $new[$key] = $item;
                    break;
                case '<':
                    $item[$searchKey] < $searchVal && $new[$key] = $item;
                    break;
                case '<=':
                    $item[$searchKey] <= $searchVal && $new[$key] = $item;
                    break;
                case '<>':
                    $item[$searchKey] != $searchVal && $new[$key] = $item;
                    break;
                case 'in':
                    in_array($item[$searchKey], (array)$searchVal) && $new[$key] = $item;
                    break;
                default:
                    $item[$searchKey] == $searchVal && $new[$key] = $item;
            }
        }
        return $new;
    }
    
    /**
     * 通过指定运算符比较两个值
     *
     * @param mixed $val1
     * @param mixed $val2
     * @param array $compare 运算符
     * @return boolean
     */
    private static function compare($val1, $val2, $compare)
    {
        switch ($compare) {
            case '>':
                return $val1 > $val2;
            case '>=':
                return $val1 >= $val2;
                break;
            case '<':
                return $val1 < $val2;
                break;
            case '<=':
                return $val1 <= $val2;
                break;
            case '<>':
                return $val1 != $val2;
                break;
            case 'in':
                return in_array($val1, (array)$val2);
                break;
            default:
                return $val1 == $val2;
        }
    }

    /**
     * 二维数组多条件查找返回
     *
     * @param array $array
     * @param array $conditions
     * @return array
     */
    public static function MSearch($array, $conditions, $or = false)
    {
        $result = [];
        foreach ($array as $index => $row) {
            $match = 0;
            foreach ($conditions as $key => $value) {
                if (!is_array($value)) break;
                if (count($value) == 2) {
                    $value = [$value[0], '=', $value[1]];
                }
                $field = strpos($value[0], ".") === false ? $value[0] : explode(".", $value[0]);
                $fieldValue = null;
                if (is_array($field)) {
                    if (!isset($row[$field[0]][$field[1]])) {
                        break;
                    }
                    $fieldValue = $row[$field[0]][$field[1]];
                } else {
                    if (!isset($row[$field])) {
                        break;
                    }
                    $fieldValue = $row[$field];
                }
                if (self::compare($fieldValue, $value[2], $value[1])) {
                    $match++;
                    if ($or) break; //满足其中一个条件即可
                }
            }
            if (!$or) {
                // 满足全部条件
                if ($match == count($conditions)) {
                    $result[] = $row;
                }
            } else {
                if ($match > 0) {
                    $result[] = $row;
                }
            }
        }
        return $result;
    }
}
