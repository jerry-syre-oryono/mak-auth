<?php
/**
 * API Endpoint: Create Webmail Credentials
 * 
 * Handles securely receiving the password, enforcing strict
 * validation rules, and simulating secure storage logic.
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

try {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Basic check: Ensure user is actually verified
    if (empty($_SESSION['verified'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized account creation attempt. Please verify OTP first.'
        ]);
        exit;
    }

    $password = trim($_POST['password'] ?? '');
    $altEmail = filter_var(trim($_POST['altEmail'] ?? ''), FILTER_VALIDATE_EMAIL);

    if (empty($password) || empty($altEmail)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing valid password or alternative email.'
        ]);
        exit;
    }

    // Backend Strict Password Validation
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[!@#$%^&*()\\[\]{}|\\\\;:\'",.<>\/?`~_\-=+]/', $password)) {
        
        logMessage('Webmail creation failed: Weak password supplied.', 'warning');
        echo json_encode([
            'success' => false, 
            'message' => 'Password does not meet security requirements. Need 8+ characters, uppercase, lowercase, number, and special character.'
        ]);
        exit;
    }

    // Simulate secure backend password hashing and database storage
    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    // e.g. execute DB query here

    // Clear verification session to prevent replay
    unset($_SESSION['verified']);
    unset($_SESSION['verified_time']);
    
    // Webmail info retrieval (simulated)
    $webmail = $_SESSION['student_webmail'] ?? 'student@students.mak.ac.ug';

    logMessage("Webmail credentials successfully created for: $webmail", 'info');

    echo json_encode([
        'success' => true,
        'message' => 'Webmail configured successfully.'
    ]);

} catch (\Exception $e) {
    logMessage('Webmail Creation Error: ' . $e->getMessage(), 'error');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected server error occurred.'
    ]);
}
