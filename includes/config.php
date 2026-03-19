<?php
/**
 * Application Configuration
 * 
 * Sensitive information is loaded from the .env file in the root directory.
 */

// Simple .env file loader
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

// Load environment variables from .env file
loadEnv(__DIR__ . '/../.env');

// Application Settings
define('APP_NAME', getenv('APP_NAME') ?: 'MAK-AUTH');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Africa/Kampala');
date_default_timezone_set(APP_TIMEZONE);

// SMTP Configuration
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'mail.mak.ac.ug');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'email@mak.ac.ug');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'app-password-here');
define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'tls'); 

// Sender Information
define('FROM_EMAIL', getenv('FROM_EMAIL') ?: getenv('SMTP_USERNAME'));
define('FROM_NAME', getenv('FROM_NAME') ?: 'MAK Student Auth');

// Recipient Configuration
define('TEST_MODE', getenv('TEST_MODE') === 'true');
define('TEST_EMAIL', getenv('TEST_EMAIL') ?: 'test@example.com');

// Webmail Reveal Configuration
define('WEBMAIL_PASSWORD', getenv('WEBMAIL_PASSWORD') ?: 'mak_webmail_2026');
define('WEBMAIL_PORTAL_URL', getenv('WEBMAIL_PORTAL_URL') ?: 'https://students.mak.ac.ug/webmail');
define('WEBMAIL_ACCESS_CODE', getenv('WEBMAIL_ACCESS_CODE') ?: 'MAK_ENROLL_2026');

// Token Verification Configuration
define('TOKEN_EXPIRY_TIME', 600); // 10 minutes
define('MAX_VERIFICATION_ATTEMPTS', 3);

// Mock API Configuration
define('MOCK_API_URL', 'https://69b7a205ffbcd0286095ac67.mockapi.io/enrolled');
define('MOCK_API_TIMEOUT', 10);

// Security Settings
define('SESSION_TIMEOUT', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);

// Error Handling (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

/**
 * Helper function to generate secure OTPs
 */
function generateSecureOTP($length = 6) {
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    
    $otp = '';
    $otp .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $otp .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $otp .= $numbers[random_int(0, strlen($numbers) - 1)];
    
    $allChars = $uppercase . $lowercase . $numbers;
    for ($i = 3; $i < $length; $i++) {
        $otp .= $allChars[random_int(0, strlen($allChars) - 1)];
    }
    
    $otpArray = str_split($otp);
    shuffle($otpArray);
    
    return implode('', $otpArray);
}

/**
 * Helper function to sanitize user input
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Helper function to log messages
 */
function logMessage($message, $level = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/../logs/app.log';
    $logEntry = "[$timestamp] [$level] $message\n";
    error_log($logEntry, 3, $logFile);
}
