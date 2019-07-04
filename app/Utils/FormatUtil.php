<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-15 10:42:13
 */

namespace App\Utils;

class FormatUtil
{
    /**
     * UUID 格式校验
     *
     * @param string $string
     * @return bool
     */
    public static function uuid(string $string)
    {
        $pattern = '/^[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}$/';

        return preg_match($pattern, $string) === 1;
    }
    /**
     * 将数组生成树形结构
     *
     * @param array $list
     * @return array $menuList
     */
    public static function generateTree(array $lists,$parentId = 'parent_id',$id = 'id',$node = 'children')
    {
            $tree = array();
            foreach($lists as $item){
                if(isset($lists[$item[$parentId]])){
                    $lists[$item[$parentId]][$node][] = &$lists[$item[$id]];
                }else{
                    $tree[] = &$lists[$item[$id]];
                }
            }
            return $tree;
    }
}
