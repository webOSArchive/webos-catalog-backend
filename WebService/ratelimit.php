<?php
/**
 * Simple rate limiting implementation
 * Tracks requests per IP address with configurable limits
 */

class RateLimit {
    private $rate_limit_dir = '__rateLimit';
    private $default_limit = 300; // requests per window
    private $default_window = 3600; // 1 hour in seconds
    
    public function __construct() {
        if (!file_exists($this->rate_limit_dir)) {
            mkdir($this->rate_limit_dir, 0774, true);
        }
    }
    
    /**
     * Check if request should be rate limited
     * @param string $ip - Client IP address
     * @param int $limit - Max requests per window (optional)
     * @param int $window - Time window in seconds (optional)
     * @return bool - true if rate limit exceeded
     */
    public function isRateLimited($ip, $limit = null, $window = null) {
        if ($limit === null) $limit = $this->default_limit;
        if ($window === null) $window = $this->default_window;
        
        // Sanitize IP for filename
        $ip_safe = preg_replace('/[^a-zA-Z0-9\.]/', '_', $ip);
        $rate_file = $this->rate_limit_dir . '/' . $ip_safe . '.json';
        
        $now = time();
        $rate_data = $this->getRateData($rate_file);
        
        // Clean old entries outside the window
        $rate_data = array_filter($rate_data, function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });
        
        // Check if limit exceeded
        if (count($rate_data) >= $limit) {
            return true;
        }
        
        // Add current request
        $rate_data[] = $now;
        $this->saveRateData($rate_file, $rate_data);
        
        return false;
    }
    
    /**
     * Get client IP address with proxy support
     * @return string
     */
    public function getClientIP() {
        $headers_to_check = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers_to_check as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Clean up old rate limit files
     */
    public function cleanup() {
        if (!is_dir($this->rate_limit_dir)) {
            return;
        }
        
        $files = glob($this->rate_limit_dir . '/*.json');
        $cutoff = time() - ($this->default_window * 2); // Keep files for 2x window
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
    
    private function getRateData($file) {
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    
    private function saveRateData($file, $data) {
        file_put_contents($file, json_encode($data));
    }
}

/**
 * Check rate limit for current request
 * @param int $limit - Max requests per hour (optional)
 * @param int $window - Time window in seconds (optional)
 * @return bool - true if rate limited
 */
function checkRateLimit($limit = null, $window = null) {
    static $rate_limiter = null;
    
    if ($rate_limiter === null) {
        $rate_limiter = new RateLimit();
    }
    
    $ip = $rate_limiter->getClientIP();
    
    if ($rate_limiter->isRateLimited($ip, $limit, $window)) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Rate limit exceeded',
            'message' => 'Too many requests. Please try again later.'
        ]);
        exit;
    }
    
    // Occasionally clean up old files (1% chance)
    if (rand(1, 100) === 1) {
        $rate_limiter->cleanup();
    }
    
    return false;
}
?>