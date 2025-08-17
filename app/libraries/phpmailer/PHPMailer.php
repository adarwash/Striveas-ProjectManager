<?php
namespace PHPMailer\PHPMailer;

class PHPMailer {
    const ENCRYPTION_SMTPS = 'ssl';
    const ENCRYPTION_STARTTLS = 'tls';
    public $Host; public $Port; public $SMTPAuth; public $SMTPSecure; public $Username; public $Password;
    private $fromEmail; private $fromName; private $to = []; private $cc = []; private $bcc = [];
    public $Subject = ''; public $Body = ''; public $AltBody = ''; private $isHtml = false; public $ErrorInfo = '';
    public function __construct($exceptions = null) {}
    public function isSMTP() {}
    public function setFrom($email, $name='') { $this->fromEmail = $email; $this->fromName = $name; }
    public function addAddress($email) { $this->to[] = $email; }
    public function addCC($email) { $this->cc[] = $email; }
    public function addBCC($email) { $this->bcc[] = $email; }
    public function addCustomHeader($name, $value) {}
    public function isHTML($bool) { $this->isHtml = (bool)$bool; }
    public function send() {
        // Fallback to PHP mail using headers approximating PHPMailer configuration
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: ' . ($this->isHtml ? 'text/html' : 'text/plain') . '; charset=UTF-8';
        $headers[] = 'From: ' . ($this->fromName ?: 'Mailer') . ' <' . $this->fromEmail . '>';
        if (!empty($this->cc)) { $headers[] = 'Cc: ' . implode(',', $this->cc); }
        if (!empty($this->bcc)) { $headers[] = 'Bcc: ' . implode(',', $this->bcc); }
        $to = implode(',', $this->to);
        $ok = mail($to, $this->Subject, $this->Body, implode("\r\n", $headers));
        if (!$ok) { $this->ErrorInfo = 'mail() returned false'; }
        return $ok;
    }
}

class SMTP {}
class Exception extends \Exception {}




