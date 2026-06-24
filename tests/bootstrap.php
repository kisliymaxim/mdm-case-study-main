<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

/*
 * Force test-only env values into ALL three places Laravel reads from
 * ($_SERVER, $_ENV, getenv) so they win over the host process env.
 *
 * PHPUnit's <env force="true"> only updates $_ENV + putenv() — Docker
 * compose populates $_SERVER as well, and Laravel's Env::get checks
 * $_SERVER first. Without these overrides, jobs hit real Redis,
 * broadcasts hit real Reverb, etc., during `php artisan test`.
 */
$overrides = [
    'APP_ENV' => 'testing',
    'APP_MAINTENANCE_DRIVER' => 'file',
    'BCRYPT_ROUNDS' => '4',
    'BROADCAST_CONNECTION' => 'null',
    'CACHE_STORE' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'QUEUE_FAILED_DRIVER' => 'null',
    'SESSION_DRIVER' => 'array',
    'PULSE_ENABLED' => 'false',
    'TELESCOPE_ENABLED' => 'false',
    'NIGHTWATCH_ENABLED' => 'false',
];

foreach ($overrides as $key => $value) {
    $_SERVER[$key] = $value;
    $_ENV[$key] = $value;
    putenv("{$key}={$value}");
}
