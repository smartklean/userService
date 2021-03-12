<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('USERSWS_DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => env('DB_PREFIX', ''),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('USERSWS_DB_HOST', config('constants.db.host')),
            'port' => env('USERSWS_DB_PORT', config('constants.db.port')),
            'database' => env('USERSWS_DB_DATABASE', config('constants.db.database')),
            'username' => env('USERSWS_DB_USERNAME', config('constants.db.username')),
            'password' => env('USERSWS_DB_PASSWORD', config('constants.db.password')),
            'unix_socket' => env('USERSWS_DB_SOCKET', config('constants.db.socket')),
            'charset' => env('USERSWS_DB_CHARSET', config('constants.db.charset')),
            'collation' => env('USERSWS_DB_COLLATION', config('constants.db.collation')),
            'prefix' => env('USERSWS_DB_PREFIX', config('constants.db.prefix')),
            'strict' => env('USERSWS_STRICT_MODE', config('constants.db.strict')),
            'engine' => env('USERSWS_DB_ENGINE', config('constants.db.engine')),
            'timezone' => env('USERSWS_DB_TIMEZONE', config('constants.db.timezone')),
        ],

        'mysql_test' => [
            'driver' => 'mysql',
            'host' => env('USERSWS_TEST_DB_HOST', config('constants.db.host')),
            'port' => env('USERSWS_TEST_DB_PORT', config('constants.db.port')),
            'database' => env('USERSWS_TEST_DB_DATABASE', config('constants.db.database')),
            'username' => env('USERSWS_TEST_DB_USERNAME', config('constants.db.username')),
            'password' => env('USERSWS_TEST_DB_PASSWORD', config('constants.db.password')),
            'unix_socket' => env('USERSWS_TEST_DB_SOCKET', config('constants.db.socket')),
            'charset' => env('USERSWS_TEST_DB_CHARSET', config('constants.db.charset')),
            'collation' => env('USERSWS_TEST_DB_COLLATION', config('constants.db.collation')),
            'prefix' => env('USERSWS_TEST_DB_PREFIX', config('constants.db.prefix')),
            'strict' => env('USERSWS_TEST_STRICT_MODE', config('constants.db.strict')),
            'engine' => env('USERSWS_TEST_DB_ENGINE', config('constants.db.engine')),
            'timezone' => env('USERSWS_TEST_DB_TIMEZONE', config('constants.db.timezone')),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => env('DB_PREFIX', ''),
            'schema' => env('DB_SCHEMA', 'public'),
            'sslmode' => env('DB_SSL_MODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 1433),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => env('DB_PREFIX', ''),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'lumen'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
