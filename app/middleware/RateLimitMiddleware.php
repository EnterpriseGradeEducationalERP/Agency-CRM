<?php
/**
 * Rate Limit Middleware
 * Simple IP+route file-backed rate limiter
 */

class RateLimitMiddleware {
    private $requests;
    private $period;
    private $storageDir;

    public function __construct() {
        $config = require ROOT_PATH . '/config/app.php';
        $this->requests = (int)($config['rate_limit_requests'] ?? 100);
        $this->period = (int)($config['rate_limit_period'] ?? 60);
        $this->storageDir = STORAGE_PATH . '/cache/ratelimit';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function handle() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $key = hash('sha256', $ip . '|' . $uri);
        $bucketPath = $this->storageDir . '/' . substr($key, 0, 2);
        if (!is_dir($bucketPath)) {
            mkdir($bucketPath, 0755, true);
        }
        $file = $bucketPath . '/' . $key . '.json';

        $now = time();
        $windowStart = $now - $this->period;
        $entries = [];
        if (file_exists($file)) {
            $raw = file_get_contents($file);
            $entries = json_decode($raw, true) ?: [];
        }
        // purge old
        $entries = array_values(array_filter($entries, function ($ts) use ($windowStart) {
            return $ts >= $windowStart;
        }));

        if (count($entries) >= $this->requests) {
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: ' . max(1, $this->period));
            echo json_encode([
                'success' => false,
                'message' => 'Too many requests. Please try again later.'
            ]);
            exit;
        }

        $entries[] = $now;
        file_put_contents($file, json_encode($entries), LOCK_EX);
        return true;
    }
}
