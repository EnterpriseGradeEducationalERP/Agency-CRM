<?php
/**
 * Simple .env Loader
 * Loads key=value pairs from ROOT_PATH/.env into getenv/$_ENV/$_SERVER
 */

class EnvLoader {
    public static function load($envFilePath) {
        if (!file_exists($envFilePath) || !is_readable($envFilePath)) {
            return;
        }

        $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = array_map('trim', explode('=', $line, 2));

            // Remove surrounding quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            // Expand variables like ${VAR}
            $value = preg_replace_callback('/\${([A-Z0-9_]+)}/i', function ($matches) {
                $var = $matches[1];
                return getenv($var) !== false ? getenv($var) : '';
            }, $value);

            // Set environment
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}
