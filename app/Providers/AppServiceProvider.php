<?php

namespace App\Providers;

use App\Utils\FormatUtil;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 注册 UUID 验证规则
        Validator::extend('uuid', function ($attribute, $value, $parameters) {
            return FormatUtil::uuid((string)$value);
        });
    }
}
