# Laravel-UEditor

UEditor integration for Laravel 


## 安装

```shell
$ composer require siaoynli/laravel-ueditor
```

## 配置

1. 添加下面一行到 `config/app.php` 中 `providers` 部分：

    ```php
    Siaoynli\LaravelUEditor\LaravelUEditorServiceProvider::class,
    ```

2. 发布配置文件与资源

    ```php
    $ php artisan vendor:publish --provider='Siaoynli\LaravelUEditor\LaravelUEditorServiceProvider'
    ```

3. 模板引入编辑器

    这行的作用是引入编辑器需要的 css,js 等文件，所以你不需要再手动去引入它们。

    ```php
    @include('vendor.ueditor.assets')
    ```

4. 编辑器的初始化

    ```html
    <!-- 实例化编辑器 -->
    <script type="text/javascript">
        var ue = UE.getEditor('container');
    </script>

    <!-- 编辑器容器 -->
    <script id="container" name="content" type="text/plain"></script>
    ```

# 说明

1. 依赖 siaoynli/laravel-upload https://github.com/siaoynli/laravel-upload  
1. 依赖 siaoynli/laravel-images https://github.com/siaoynli/laravel-images  
上传文件存放public或者storage
2. 具体方法请查阅源代码

# License

MIT
