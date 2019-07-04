<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:47:10
 */

namespace App\Models\System;


/**
 * 用户信息表
 *
 * @package App\Models\Dotnet
 * @property string $userId     用户编号
 * @property string $userName   用户名称
 * @property integer $disabled  停用状态 1:停用 0:启用
 */
class SystemRole extends ScrmBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "system_role";

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