<?php
/**
 * Writes the app config files from environment variables at container start.
 *
 * ECS injects every SSM parameter under /eventgo/<env>/app as an env var and
 * lists their names in DOTENV_KEYS (comma separated). Each one is written to
 * /var/www/html/.env, except the keys in $fileKeys, whose value is written
 * as-is to their own file.
 *
 * Values are never printed, only names.
 */

$dotenvPath = '/var/www/html/.env';

// Env var => file that receives its raw value.
$fileKeys = [
    'GOOGLE_ROUTES_SA_JSON' => '/var/www/html/ajax/gaxi-487815-79b122d9b0f0.json',
];

function fail(string $message): void
{
    fwrite(STDERR, "[write-dotenv] ERROR: {$message}\n");
    exit(1);
}

function info(string $message): void
{
    fwrite(STDERR, "[write-dotenv] {$message}\n");
}

$keys = array_values(array_filter(array_map('trim', explode(',', (string) getenv('DOTENV_KEYS')))));

if ($keys === []) {
    // Local runs may mount their own .env instead.
    if (is_file($dotenvPath) && filesize($dotenvPath) > 0) {
        info('DOTENV_KEYS is empty, keeping the existing .env');
        exit(0);
    }
    fail('DOTENV_KEYS is empty and there is no .env to fall back to');
}

$missing = [];
$lines   = [];

foreach ($keys as $key) {
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
        fail("invalid variable name '{$key}'");
    }

    $value = getenv($key);
    if ($value === false) {
        $missing[] = $key;
        continue;
    }

    if (isset($fileKeys[$key])) {
        if (file_put_contents($fileKeys[$key], $value, LOCK_EX) === false) {
            fail("could not write {$fileKeys[$key]}");
        }
        info("wrote {$key} to {$fileKeys[$key]}");
        continue;
    }

    // phpdotenv double-quoted value: escape \ " $ and keep newlines as \n.
    $escaped = strtr($value, [
        '\\' => '\\\\',
        '"'  => '\\"',
        '$'  => '\\$',
        "\r" => '\\r',
        "\n" => '\\n',
    ]);
    $lines[] = "{$key}=\"{$escaped}\"";
}

if ($missing !== []) {
    fail('listed in DOTENV_KEYS but not set: ' . implode(', ', $missing));
}

if (file_put_contents($dotenvPath, implode("\n", $lines) . "\n", LOCK_EX) === false) {
    fail("could not write {$dotenvPath}");
}

info('wrote ' . count($lines) . " variables to {$dotenvPath}");
