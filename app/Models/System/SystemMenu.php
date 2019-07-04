<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:47:10
 */

namespace App\Models\System;


/**
 * 菜单表
 *
 * @package App\Models\Dotnet
 * @property integer $id     菜单编号
 * @property integer $parent_id     父级菜单编号
 * @property string $title   label
 * @property string $router_name   name
 * @property string $icon   icon
 */
class SystemMenu extends ScrmBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "system_menu";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "id";

    /**
     * 停用状态:已停用
     */
    public const ACTIVE_YSE = 1;

    /**
     * 停用状态:已启动
     */
    public const ACTIVE_NO = 0;
}