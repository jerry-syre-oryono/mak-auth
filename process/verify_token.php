<?php
/**
 * API Endpoint: Verify Authentication Token
 * 
 * Handles OTP verification from Step 2
 * Compares user-submitted OTP against server-stored OTP
 * Returns webmail credentials if verification succeeds
 */

require_once __DIR__ . '/../includes/config.php';

// Start session to access stored token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    // Get submitted OTP
    $submittedOtp = sanitizeInput($_POST['otpCode'] ?? '');
    
    // Validate input
    if (empty($submittedOtp)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter the OTP Code.'
        ]);
        exit;
    }
    
    // Check if an OTP was generated in this session
    if (!isset($_SESSION['auth_otp']) || !isset($_SESSION['student_webmail'])) {
        logMessage('OTP verification attempted without session OTP', 'warning');
        echo json_encode([
            'success' => false,
            'message' => 'No active OTP found. Please send a new OTP.'
        ]);
        exit;
    }
    
    // Check if OTP has expired
    $tokenTime = $_SESSION['auth_otp_time'] ?? 0;
    $currentTime = time();
    $tokenAge = $currentTime - $tokenTime;
    
    if ($tokenAge > TOKEN_EXPIRY_TIME) {
        unset($_SESSION['auth_otp']);
        unset($_SESSION['auth_otp_time']);
        logMessage('OTP verification failed: OTP expired', 'info');
        echo json_encode([
            'success' => false,
            'message' => 'OTP has expired. Please request a new one.'
        ]);
        exit;
    }
    
    // Track verification attempts
    if (!isset($_SESSION['verification_attempts'])) {
        $_SESSION['verification_attempts'] = 0;
    }
    
    if ($_SESSION['verification_attempts'] >= MAX_VERIFICATION_ATTEMPTS) {
        unset($_SESSION['auth_otp']);
        unset($_SESSION['verification_attempts']);
        logMessage('OTP verification blocked: max attempts exceeded', 'warning');
        echo json_encode([
            'success' => false,
            'message' => 'Too many failed attempts. Please request a new OTP.'
        ]);
        exit;
    }
    
    // Compare OTPs (constant-time comparison for security)
    $storedOtp = $_SESSION['auth_otp'];
    $tokensMatch = hash_equals($storedOtp, $submittedOtp);
    
    if (!$tokensMatch) {
        $_SESSION['verification_attempts']++;
        $remaining = MAX_VERIFICATION_ATTEMPTS - $_SESSION['verification_attempts'];
        
        logMessage('OTP verification failed. Attempts: ' . $_SESSION['verification_attempts'] . '/' . MAX_VERIFICATION_ATTEMPTS, 'info');
        
        echo json_encode([
            'success' => false,
            'message' => "Invalid OTP Code. {$remaining} attempt(s) remaining.",
            'attention' => 'Invalid or expired OTP'
        ]);
        exit;
    }
    
    // OTP is valid - retrieve stored webmail information
    $webmail = $_SESSION['student_webmail'];
    $studentName = $_SESSION['student_name'];
    $studentId = $_SESSION['student_id'];
    
    // Mark as verified
    $_SESSION['verified'] = true;
    $_SESSION['verified_time'] = time();
    
    // Clear the OTP after successful verification
    unset($_SESSION['auth_otp']);
    unset($_SESSION['auth_otp_time']);
    unset($_SESSION['verification_attempts']);
    
    logMessage("OTP verified successfully for student: $studentId ($webmail)", 'info');
    
    // Return credentials
    echo json_encode([
        'success' => true,
        'message' => 'Verification successful!',
        'credentials' => [
            'webmail' => $webmail,
            'password' => WEBMAIL_PASSWORD,
            'portalUrl' => WEBMAIL_PORTAL_URL,
            'accessCode' => WEBMAIL_ACCESS_CODE,
            'studentName' => $studentName
        ]
    ]);

} catch (\Exception $e) {
    logMessage('OTP verification error: ' . $e->getMessage(), 'error');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during verification. Please try again.'
    ]);
}
