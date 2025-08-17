<?php
/**
 * Microsoft Graph API - Alternative to IMAP
 * Use this if IMAP is completely blocked by Security Defaults
 */

class MicrosoftGraphEmail {
    private $tenantId;
    private $clientId;
    private $clientSecret;
    private $accessToken;
    
    public function __construct($tenantId, $clientId, $clientSecret) {
        $this->tenantId = $tenantId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->getAccessToken();
    }
    
    /**
     * Get OAuth2 access token
     */
    private function getAccessToken() {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        $this->accessToken = $result['access_token'] ?? null;
        
        if (!$this->accessToken) {
            throw new Exception('Failed to get access token');
        }
    }
    
    /**
     * Get emails from inbox
     */
    public function getEmails($userEmail = 'support@yourdomain.com', $limit = 10) {
        $url = "https://graph.microsoft.com/v1.0/users/{$userEmail}/messages";
        $url .= '?$top=' . $limit;
        $url .= '&$orderby=receivedDateTime desc';
        $url .= '&$select=id,subject,from,receivedDateTime,body,isRead';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get emails: ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Send email
     */
    public function sendEmail($from, $to, $subject, $body) {
        $url = "https://graph.microsoft.com/v1.0/users/{$from}/sendMail";
        
        $message = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $body
                ],
                'toRecipients' => [
                    ['emailAddress' => ['address' => $to]]
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 202; // 202 = Accepted
    }
}

// Usage example:
/*
// You need to register an app in Azure AD first:
// 1. Go to portal.azure.com
// 2. Azure Active Directory → App registrations → New registration
// 3. Add API permissions: Mail.Read, Mail.Send (Application permissions)
// 4. Create a client secret

$tenantId = 'your-tenant-id'; // Find in Azure AD overview
$clientId = 'your-app-id';    // From app registration
$clientSecret = 'your-secret'; // From certificates & secrets

try {
    $graph = new MicrosoftGraphEmail($tenantId, $clientId, $clientSecret);
    
    // Get emails
    $emails = $graph->getEmails('support@yourdomain.com', 10);
    foreach ($emails['value'] as $email) {
        echo "Subject: " . $email['subject'] . "\n";
        echo "From: " . $email['from']['emailAddress']['address'] . "\n";
        echo "Date: " . $email['receivedDateTime'] . "\n\n";
    }
    
    // Send email
    $sent = $graph->sendEmail(
        'support@yourdomain.com',
        'customer@example.com',
        'Ticket Update',
        '<p>Your ticket has been updated</p>'
    );
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
*/
?>
