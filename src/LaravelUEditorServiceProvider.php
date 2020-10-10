<?php

namespace Siaoynli\LaravelUEditor;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class LaravelUEditorServiceProvider extends ServiceProvider
{

    public function register()
    {

    }


    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'ueditor');
        $this->publishes([
            __DIR__ . '/../config/ueditor.php' => config_path('ueditor.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../assets/ueditor' => public_path('static/ueditor'),
        ], 'assets');

        $this->publishes([
            __DIR__ . '/../views' => base_path('resources/views/vendor/ueditor'),
        ], 'resources');

        $this->publishes([
            __DIR__ . '/../demo' => base_path('resources/views'),
        ], 'resources');


        $this->registerRoute($router);
    }

    /**
     * @Author: hzwlxy
     * @Email: 120235331@qq.com
     * @Date: 2019/7/16 15:19
     * @Description:定义neditor上传路由
     * @param $router
     */
    protected function registerRoute($router)
    {
        if (!$this->app->routesAreCached()) {
            $router->group(['namespace' => __NAMESPACE__, "middleware" => config('ueditor.route.middleware', [])], function ($router) {
                $router->any(config('ueditor.route.uri', '/ueditor/server'), 'UEditorController@serve');
                if(env("APP_DEBUG") == true) {
                    $router->get('/ueditor/test', 'UEditorController@test');
                }
            });
        }
    }
}
