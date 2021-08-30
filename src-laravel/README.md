# src-laravel

Dockerized Laravel boilerplate with phpredis and a buildbox for Laravel Mix.

Inside `src/` we just have the default [Laravel](https://github.com/laravel/laravel) framework + 1 custom artisan commands.

When upgrading Laravel to a new version, make sure to keep our custom Artisan commands:

```
app
├── Commands
│   ├── DatabaseIsReady.php
└── Kernel.php
```

# Laravel Jetstream

This boilerplate is using Laravel Jetstream with Inertia scaffolding.

Laravel Jetstream is a beautifully designed application starter kit for Laravel 
and provides the perfect starting point for your next Laravel application. 
Jetstream provides the implementation for your application's login, registration, 
email verification, two-factor authentication, session management, 
API via Laravel Sanctum, and optional team management features.

[Read more at https://jetstream.laravel.com/](https://jetstream.laravel.com/2.x/introduction.html)
