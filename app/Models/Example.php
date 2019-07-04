<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 10:31:39
 */

namespace App\Models;

use Carbon\Carbon;

/**
 * 数据模型: 示例
 *
 * @package App\Models
 * @property integer $id                主键ID
 * @property string $username           用户名
 * @property string $password           密码
 * @property integer $state             状态
 * @property Carbon|null $created_at    创建时间
 * @property Carbon|null $updated_at    更新时间
 */
class Example extends BaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "example";
}