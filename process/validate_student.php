<?php
/**
 * API Endpoint: Validate Student Enrollment
 * 
 * Handles AJAX requests from Step 1 form
 * Verifies student ID and registration number against mock API
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
    // Get and validate input
    $studentId = sanitizeInput($_POST['studentId'] ?? '');
    $registrationNumber = sanitizeInput($_POST['registrationNumber'] ?? '');

    // Validation
    $errors = [];
    if (empty($studentId)) {
        $errors[] = 'Student ID is required.';
    }
    if (empty($registrationNumber)) {
        $errors[] = 'Registration number is required.';
    }

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode(' ', $errors)
        ]);
        exit;
    }

    // Fetch from mock API
    $context = stream_context_create([
        'http' => [
            'timeout' => MOCK_API_TIMEOUT
        ]
    ]);

    $response = @file_get_contents(MOCK_API_URL, false, $context);

    if ($response === false) {
        logMessage("Mock API request failed. URL: " . MOCK_API_URL, 'error');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to connect to enrollment database. Please try again.'
        ]);
        exit;
    }

    $students = json_decode($response, true);

    if (!is_array($students)) {
        logMessage("Invalid JSON response from Mock API", 'error');
        echo json_encode([
            'success' => false,
            'message' => 'Enrollment database error. Please try again.'
        ]);
        exit;
    }

    // Match against provided credentials
    // The mock API returns array with student_id and reg_number fields
    $foundStudent = null;
    foreach ($students as $student) {
        if ((string)$student['student_id'] === $studentId && 
            (string)$student['reg_number'] === $registrationNumber) {
            $foundStudent = $student;
            break;
        }
    }

    if (!$foundStudent) {
        logMessage("Student not found. ID: $studentId, Reg: $registrationNumber", 'info');
        echo json_encode([
            'success' => false,
            'message' => 'Student record not found. Please check your credentials and try again.'
        ]);
        exit;
    }

    // Generate unique webmail using the helper function
    $webmail = generateUniqueWebmail(
        $foundStudent['Firstname'], 
        $foundStudent['Lastname'], 
        $foundStudent['Middlename'] ?? ''
    );

    // Success - return student data
    logMessage("Student validated successfully. ID: $studentId. Generated webmail: $webmail", 'info');
    echo json_encode([
        'success' => true,
        'message' => 'Student found. Please provide an alternative email.',
        'student' => [
            'fullName' => trim($foundStudent['Firstname'] . ' ' . ($foundStudent['Middlename'] ?? '') . ' ' . $foundStudent['Lastname']),
            'firstName' => $foundStudent['Firstname'],
            'lastName' => $foundStudent['Lastname'],
            'studentId' => $foundStudent['student_id'],
            'registrationNumber' => $foundStudent['reg_number'],
            'program' => $foundStudent['course'] ?? 'Not specified',
            'webmail' => $webmail
        ]
    ]);

} catch (\Exception $e) {
    logMessage('Validation error: ' . $e->getMessage(), 'error');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
}
