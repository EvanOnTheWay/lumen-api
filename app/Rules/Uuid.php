<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 15:08:52
 */

namespace App\Rules;

use App\Utils\FormatUtil;
use Illuminate\Contracts\Validation\Rule;

class Uuid implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return FormatUtil::uuid((string)$value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '错误的 UUID 格式';
    }
}