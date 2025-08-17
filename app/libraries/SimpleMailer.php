<?php

/**
 * Simple SMTP Mailer Implementation
 * Basic email sending functionality for the ticketing system
 */
class SimpleMailer {
    private $config;
    private $usePHPMailer = true;
    
    public function __construct($config = []) {
        $this->config = array_merge([
            'host' => 'localhost',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'from_email' => 'noreply@example.com',
            'from_name' => 'Ticketing System'
        ], $config);
    }
    
    /**
     * Send email using PHP mail() function or SMTP
     * 
     * @param array $emailData Email data
     * @return bool Success status
     */
    public function send($emailData) {
        try {
            if ($this->usePHPMailer) {
                return $this->sendViaPHPMailer($emailData);
            }
            // Build headers
            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
            $headers[] = 'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>';
            
            if (!empty($emailData['cc'])) {
                $cc = is_array($emailData['cc']) ? implode(',', $emailData['cc']) : $emailData['cc'];
                $headers[] = 'Cc: ' . $cc;
            }
            
            if (!empty($emailData['bcc'])) {
                $bcc = is_array($emailData['bcc']) ? implode(',', $emailData['bcc']) : $emailData['bcc'];
                $headers[] = 'Bcc: ' . $bcc;
            }
            
            // Add custom headers
            if (!empty($emailData['custom_headers'])) {
                foreach ($emailData['custom_headers'] as $header) {
                    $headers[] = $header;
                }
            }
            
            $to = is_array($emailData['to']) ? implode(',', $emailData['to']) : $emailData['to'];
            $subject = $emailData['subject'];
            $body = $emailData['html_body'] ?? $emailData['body'];
            $headerString = implode("\r\n", $headers);
            
            // Use PHP's mail() function
            $result = mail($to, $subject, $body, $headerString);
            
            if (!$result) {
                error_log('SimpleMailer: Failed to send email to ' . $to);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('SimpleMailer Error: ' . $e->getMessage());
            return false;
        }
    }

    private function sendViaPHPMailer($emailData) {
        try {
            // Minimal embedded PHPMailer to avoid composer requirement
            if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                require_once __DIR__ . '/phpmailer/PHPMailer.php';
            }
            if (!class_exists('PHPMailer\\PHPMailer\\SMTP')) {
                require_once __DIR__ . '/phpmailer/SMTP.php';
            }
            if (!class_exists('PHPMailer\\PHPMailer\\Exception')) {
                require_once __DIR__ . '/phpmailer/Exception.php';
            }
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->Port = $this->config['port'];
            $mail->SMTPAuth = !empty($this->config['username']);
            if (!empty($this->config['encryption'])) {
                $mail->SMTPSecure = $this->config['encryption'] === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            // Recipients
            $toList = is_array($emailData['to']) ? $emailData['to'] : explode(',', (string)$emailData['to']);
            foreach ($toList as $addr) { $addr = trim($addr); if ($addr) $mail->addAddress($addr); }
            if (!empty($emailData['cc'])) {
                $ccList = is_array($emailData['cc']) ? $emailData['cc'] : explode(',', (string)$emailData['cc']);
                foreach ($ccList as $addr) { $addr = trim($addr); if ($addr) $mail->addCC($addr); }
            }
            if (!empty($emailData['bcc'])) {
                $bccList = is_array($emailData['bcc']) ? $emailData['bcc'] : explode(',', (string)$emailData['bcc']);
                foreach ($bccList as $addr) { $addr = trim($addr); if ($addr) $mail->addBCC($addr); }
            }
            // Headers
            if (!empty($emailData['custom_headers'])) {
                foreach ($emailData['custom_headers'] as $headerLine) {
                    $parts = explode(':', $headerLine, 2);
                    if (count($parts) === 2) { $mail->addCustomHeader(trim($parts[0]), trim($parts[1])); }
                }
            }
            $mail->Subject = $emailData['subject'] ?? '';
            $html = $emailData['html_body'] ?? null;
            $text = $emailData['body'] ?? $emailData['text_body'] ?? strip_tags((string)$html);
            if ($html) { $mail->isHTML(true); $mail->Body = $html; $mail->AltBody = $text; }
            else { $mail->isHTML(false); $mail->Body = $text; }
            $ok = $mail->send();
            if (!$ok) { error_log('PHPMailer send failed: ' . $mail->ErrorInfo); }
            return $ok;
        } catch (Throwable $e) {
            error_log('PHPMailer exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate a unique message ID
     * 
     * @return string Message ID
     */
    public function getLastMessageID() {
        return '<' . uniqid() . '@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>';
    }
    
    /**
     * Add custom header method for compatibility
     * 
     * @param string $name Header name
     * @param string $value Header value
     */
    public function addCustomHeader($name, $value) {
        // Store for use in send() method
        if (!isset($this->customHeaders)) {
            $this->customHeaders = [];
        }
        $this->customHeaders[] = $name . ': ' . $value;
    }
}