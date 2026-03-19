<?php
/**
 * Email Handler Class
 * 
 * Handles SMTP email sending using PHPMailer
 * Manages email validation, template rendering, and error handling
 */

require_once __DIR__ . '/../includes/config.php';

// Load PHPMailer from Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

class EmailHandler
{
    private $mailer;
    private $errors = [];

    /**
     * Constructor - Initialize PHPMailer
     */
    public function __construct()
    {
        // Check if PHPMailer is available
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $this->errors[] = 'PHPMailer library not installed.';
            logMessage('PHPMailer not found. Install via: composer require phpmailer/phpmailer', 'error');
            return;
        }

        try {
            $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $this->configureSMTP();
        } catch (\Exception $e) {
            $this->errors[] = 'Failed to initialize email service.';
            logMessage('PHPMailer initialization error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Configure SMTP settings
     */
    private function configureSMTP()
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->Port = SMTP_PORT;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
            
            // Disable debug output for production
            $this->mailer->SMTPDebug = 0; 
        } catch (\Exception $e) {
            logMessage('SMTP configuration error: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Send OTP Code email
     * 
     * @param string $recipientEmail Recipient email address
     * @param string $studentName Student's full name
     * @param string $studentId Student ID
     * @param string $registrationNumber Registration number
     * @param string $otpCode Generated OTP Code
     * @param string $webmail University webmail
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendAuthenticationOTP($recipientEmail, $studentName, $studentId, $registrationNumber, $otpCode, $webmail)
    {
        if (!$this->isReady()) {
            return [
                'success' => false,
                'message' => 'Email service is not properly configured.'
            ];
        }

        try {
            // Validate recipient email
            if (!isValidEmail($recipientEmail)) {
                return [
                    'success' => false,
                    'message' => 'Invalid recipient email address.'
                ];
            }

            // Set recipient
            $this->mailer->addAddress($recipientEmail);

            // Set subject and HTML content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'MAK-AUTH: Your Secure OTP Code';
            $this->mailer->Body = $this->generateOTPEmailTemplate($studentName, $studentId, $registrationNumber, $otpCode, $webmail);
            $this->mailer->AltBody = $this->generatePlainTextOTPTemplate($studentName, $otpCode);

            // Send email
            if ($this->mailer->send()) {
                logMessage("OTP Code sent to: $recipientEmail for student: $studentId", 'info');
                return [
                    'success' => true,
                    'message' => 'OTP Code has been sent successfully.'
                ];
            } else {
                logMessage("Failed to send email to: $recipientEmail. Error: " . $this->mailer->ErrorInfo, 'error');
                return [
                    'success' => false,
                    'message' => 'Failed to send email. Please try again later.'
                ];
            }
        } catch (\Exception $e) {
            logMessage('Email sending exception: ' . $e->getMessage(), 'error');
            return [
                'success' => false,
                'message' => 'Email service error. Please try again later.'
            ];
        } finally {
            // Clear recipients for next email
            $this->mailer->clearAddresses();
        }
    }

    /**
     * Generate HTML email template for OTP Code
     * 
     * @return string HTML email content
     */
    private function generateOTPEmailTemplate($studentName, $studentId, $registrationNumber, $otpCode, $webmail)
    {

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f3f4f6;
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #059669;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.025em;
        }
        .header p {
            color: #d1fae5;
            margin: 8px 0 0 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .intro {
            color: #4b5563;
            margin-bottom: 32px;
        }
        .student-box {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 32px;
            border: 1px solid #e5e7eb;
        }
        .student-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .student-row:last-child {
            margin-bottom: 0;
        }
        .student-label {
            color: #6b7280;
            width: 140px;
            flex-shrink: 0;
        }
        .student-value {
            font-weight: 600;
            color: #111827;
        }
        .otp-container {
            text-align: center;
            padding: 32px;
            background-color: #ecfdf5;
            border-radius: 12px;
            border: 2px dashed #10b981;
            margin-bottom: 32px;
        }
        .otp-label {
            font-size: 12px;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 42px;
            font-weight: 800;
            color: #064e3b;
            letter-spacing: 8px;
            margin: 0;
        }
        .footer {
            text-align: center;
            padding: 0 30px 40px 30px;
            font-size: 12px;
            color: #9ca3af;
        }
        .important-note {
            font-size: 13px;
            color: #92400e;
            background-color: #fffbeb;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin-bottom: 32px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>MAK-AUTH</h1>
                <p>Secure Student Verification</p>
            </div>
            
            <div class="content">
                <div class="greeting">Hello {$studentName},</div>
                <div class="intro">Your student record has been successfully verified. Please use the following One-Time Password (OTP) to complete your webmail setup.</div>
                
                <div class="student-box">
                    <div class="student-row">
                        <span class="student-label">Student Name</span>
                        <span class="student-value">{$studentName}</span>
                    </div>
                    <div class="student-row">
                        <span class="student-label">Student ID</span>
                        <span class="student-value">{$studentId}</span>
                    </div>
                    <div class="student-row">
                        <span class="student-label">Registration No</span>
                        <span class="student-value">{$registrationNumber}</span>
                    </div>
                </div>
                
                <div class="otp-container">
                    <div class="otp-label">Your Secure OTP Code</div>
                    <div class="otp-code">{$otpCode}</div>
                </div>
                
                <div class="important-note">
                    <strong>IMPORTANT:</strong> This code is confidential and will expire in 10 minutes. If you did not request this, please ignore this email or contact support.
                </div>
                
                <p style="font-size: 14px; color: #4b5563;">
                    Best regards,<br>
                    <strong style="color: #111827;">MAK-AUTH Team</strong>
                </p>
            </div>
            
            <div class="footer">
                <p>This is an automated security notification from Makerere University.</p>
                <p>&copy; 2026 Makerere University. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate plain text email template
     * 
     * @return string Plain text email content
     */
    private function generatePlainTextOTPTemplate($studentName, $otpCode)
    {
        return <<<TEXT
MAK-AUTH: OTP CODE
Secure Authentication System

Dear $studentName,

Your student enrollment has been verified successfully.

Your OTP Code:
$otpCode

IMPORTANT: Keep this code confidential. This OTP will expire in 10 minutes.

If you did not request this OTP, please contact the ICT Help Desk immediately.

Best regards,
MAK-AUTH System

---
This is an automated message. Please do not reply to this email.
© 2026 Makerere University. All rights reserved.
TEXT;
    }

    /**
     * Check if email service is ready
     * 
     * @return bool
     */
    public function isReady()
    {
        return !empty($this->errors) === false && $this->mailer !== null;
    }

    /**
     * Get any initialization errors
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
