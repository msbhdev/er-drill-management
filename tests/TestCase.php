<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected static bool $testDatabasesInitialized = false;

    protected static ?array $dotEnvValues = null;

    protected function setUp(): void
    {
        $this->initializeTestDatabases();

        parent::setUp();
    }

    protected function connectionsToTransact()
    {
        return array_values(array_unique([
            config('database.default'),
            'auth',
        ]));
    }

    protected function initializeTestDatabases(): void
    {
        if (static::$testDatabasesInitialized) {
            return;
        }

        $connections = [
            $this->resolveConnectionConfig(''),
            $this->resolveConnectionConfig('AUTH_'),
        ];

        foreach ($connections as $connection) {
            if (! in_array($connection['driver'], ['mysql', 'mariadb'], true) || blank($connection['database'])) {
                continue;
            }

            $this->recreateMysqlDatabase($connection);
        }

        static::$testDatabasesInitialized = true;
    }

    protected function resolveConnectionConfig(string $prefix): array
    {
        $driver = $this->resolveEnvValue($prefix.'DB_CONNECTION', $this->resolveEnvValue('DB_CONNECTION', 'mysql'));

        return [
            'driver' => $driver,
            'host' => $this->resolveEnvValue($prefix.'DB_HOST', $this->resolveEnvValue('DB_HOST', '127.0.0.1')),
            'port' => $this->resolveEnvValue($prefix.'DB_PORT', $this->resolveEnvValue('DB_PORT', '3306')),
            'database' => $this->resolveEnvValue($prefix.'DB_DATABASE'),
            'username' => $this->resolveEnvValue($prefix.'DB_USERNAME', $this->resolveEnvValue('DB_USERNAME', 'root')),
            'password' => $this->resolveEnvValue($prefix.'DB_PASSWORD', $this->resolveEnvValue('DB_PASSWORD', '')),
            'charset' => $this->resolveEnvValue($prefix.'DB_CHARSET', $this->resolveEnvValue('DB_CHARSET', 'utf8mb4')),
            'collation' => $this->resolveEnvValue($prefix.'DB_COLLATION', $this->resolveEnvValue('DB_COLLATION', 'utf8mb4_unicode_ci')),
        ];
    }

    protected function recreateMysqlDatabase(array $connection): void
    {
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;charset=%s',
                $connection['host'],
                $connection['port'],
                $connection['charset']
            ),
            $connection['username'],
            $connection['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );

        $databaseName = str_replace('`', '``', $connection['database']);
        $charset = preg_replace('/[^A-Za-z0-9_]/', '', $connection['charset']) ?: 'utf8mb4';
        $collation = preg_replace('/[^A-Za-z0-9_]/', '', $connection['collation']) ?: 'utf8mb4_unicode_ci';

        $pdo->exec("DROP DATABASE IF EXISTS `{$databaseName}`");
        $pdo->exec("CREATE DATABASE `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");
    }

    protected function resolveEnvValue(string $key, ?string $fallback = null): ?string
    {
        $value = getenv($key);

        if ($value !== false && $value !== '') {
            return $value;
        }

        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        $dotEnvValues = static::$dotEnvValues ??= $this->parseDotEnvFile();

        if (array_key_exists($key, $dotEnvValues) && $dotEnvValues[$key] !== '') {
            return $dotEnvValues[$key];
        }

        return $fallback;
    }

    protected function parseDotEnvFile(): array
    {
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'.env';

        if (! file_exists($path)) {
            return [];
        }

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($trimmed, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $trimmed, 2);
            $values[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }

        return $values;
    }
}
