<?php
/**
 * API Endpoint: Send Authentication Token
 * 
 * Handles AJAX requests from Step 2 form
 * Generates OTP Code and sends via email
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../classes/EmailHandler.php';

// Start session to store tokens
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
    // Get and validate input
    $altEmail = sanitizeInput($_POST['altEmail'] ?? '');
    $studentId = sanitizeInput($_POST['studentId'] ?? '');
    $registrationNumber = sanitizeInput($_POST['registrationNumber'] ?? '');
    $studentName = sanitizeInput($_POST['studentName'] ?? '');
    $webmail = sanitizeInput($_POST['webmail'] ?? '');

    // Validation
    $errors = [];
    
    if (empty($altEmail)) {
        $errors[] = 'Alternative email is required.';
    } elseif (!isValidEmail($altEmail)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($studentId)) {
        $errors[] = 'Student ID is required.';
    }
    
    if (empty($registrationNumber)) {
        $errors[] = 'Registration number is required.';
    }
    
    if (empty($studentName)) {
        $errors[] = 'Student name is required.';
    }

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode(' ', $errors)
        ]);
        exit;
    }

    // Generate secure OTP Code
    $otpCode = generateSecureOTP(6);

    // Initialize email handler
    $emailHandler = new EmailHandler();

    if (!$emailHandler->isReady()) {
        $errors = $emailHandler->getErrors();
        logMessage('EmailHandler initialization failed: ' . implode(', ', $errors), 'error');
        
        // In test mode, show the OTP anyway for development
        if (TEST_MODE) {
            logMessage("TEST MODE: OTP would be sent to $altEmail. OTP: $otpCode", 'info');
            echo json_encode([
                'success' => true,
                'message' => '[TEST MODE] OTP generated successfully. Check logs for OTP.',
                'otpCode' => $otpCode // Only in test mode
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Email service is currently unavailable. Please try again later.'
        ]);
        exit;
    }

    // Determine recipient email
    $recipientEmail = TEST_MODE ? TEST_EMAIL : $altEmail;

    // Send OTP Code email
    $emailResult = $emailHandler->sendAuthenticationOTP(
        $recipientEmail,
        $studentName,
        $studentId,
        $registrationNumber,
        $otpCode,
        $webmail
    );

    if ($emailResult['success']) {
        // Store OTP and student data in session for later verification
        $_SESSION['auth_otp'] = $otpCode;
        $_SESSION['auth_otp_time'] = time();
        $_SESSION['student_webmail'] = $webmail;
        $_SESSION['student_name'] = $studentName;
        $_SESSION['student_id'] = $studentId;
        $_SESSION['verification_attempts'] = 0;
        
        logMessage("OTP Code sent successfully to: $recipientEmail for student: $studentId", 'info');
        echo json_encode([
            'success' => true,
            'message' => 'OTP Code has been sent to your email. Check your inbox (and spam folder) for the code.',
            'recipient' => $recipientEmail
        ]);
    } else {
        logMessage("Failed to send OTP email to: $recipientEmail. Reason: " . $emailResult['message'], 'error');
        echo json_encode([
            'success' => false,
            'message' => $emailResult['message']
        ]);
    }

} catch (\Exception $e) {
    logMessage('OTP sending error: ' . $e->getMessage(), 'error');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again later.'
    ]);
}
