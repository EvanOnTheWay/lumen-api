### 工程说明
本工程用于实现业务接口。

### 工程结构
```
├── .env
├── .env.local
├── .env.development
├── .env.testing
├── .env.production
├── app
│   ├── Console
│   │   ├── Commands
│   │   └── Kernel.php
│   ├── Events
│   │   ├── Event.php
│   │   └── ExampleEvent.php
│   ├── Exceptions
│   │   └── Handler.php
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── Controller.php
│   │   │   └── ExampleController.php
│   │   └── Middleware
│   │       ├── Authenticate.php
│   │       └── ExampleMiddleware.php
│   ├── Jobs
│   │   ├── ExampleJob.php
│   │   └── Job.php
│   ├── Listeners
│   │   └── ExampleListener.php
│   ├── Models
│   │   ├── BaseModel.php
│   │   ├── Dotnet
│   │   │   ├── DotnetBaseModel.php
│   │   │   └── DotnetExample.php
│   │   └── Example.php
│   ├── Providers
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   └── EventServiceProvider.php
│   ├── Rules
│   ├── Services
│   │   ├── BaseService.php
│   │   ├── Dotnet
│   │   │   └── DotnetExampleService.php
│   │   └── ExampleService.php
│   └── Utils
│       └── ExampleUtil.php
├── artisan
├── bootstrap
│   └── app.php
├── composer.json
├── composer.lock
├── database
│   ├── factories
│   │   └── ModelFactory.php
│   ├── migrations
│   └── seeds
│       └── DatabaseSeeder.php
├── phpunit.xml
├── public
│   └── index.php
├── readme.md
├── resources
│   └── views
├── routes
│   └── web.php
├── storage
│   ├── app
│   ├── framework
│   │   ├── cache
│   │   └── views
│   └── logs
├── tests
│   ├── ExampleTest.php
│   └── TestCase.php
└── vendor

```

### 跨域设置
请参照下面内容进行 Nginx 配置:
```
    location / {
        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Allow-Origin' '*' always;
            add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELTE, PATCH,OPTIONS';
            add_header 'Access-Control-Allow-Headers' 'X-Requested-With,Content-Type,Authorization, token, token_version';
            add_header 'Access-Control-Max-Age' 1728000;
            add_header 'Content-Type' 'text/plain; charset=utf-8';
            add_header 'Content-Length' 0;

            return 204;
        }

        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ [^/]\.php(/|$)
    {
        try_files $uri =404;
        fastcgi_pass  unix:/tmp/php-cgi.sock;
        fastcgi_index index.php;
        include fastcgi.conf;

        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELTE, PATCH,OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'X-Requested-With,Content-Type,Authorization, token, token_version';
    }
```