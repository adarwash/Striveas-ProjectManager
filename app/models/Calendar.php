<?php
class Calendar {
    private $db;

    public function __construct() {
        // Initialize database connection
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Get all calendars for a user
     * 
     * @param int $user_id User ID
     * @return array Calendars
     */
    public function getCalendarsByUser($user_id) {
        $query = "SELECT * FROM external_calendars WHERE user_id = :user_id";
        return $this->db->select($query, [':user_id' => $user_id]);
    }
    
    /**
     * Get a calendar by ID
     * 
     * @param int $id Calendar ID
     * @return array|false Calendar array or false
     */
    public function getCalendarById($id) {
        $query = "SELECT * FROM external_calendars WHERE id = :id";
        $result = $this->db->select($query, [':id' => $id]);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Add a new calendar
     * 
     * @param array $data Calendar data
     * @return int|false Last insert ID or false
     */
    public function addCalendar($data) {
        // Prepare the query
        $query = "INSERT INTO external_calendars (user_id, name, source, source_id, color, auto_refresh, access_token, refresh_token, active) 
                 VALUES (:user_id, :name, :source, :source_id, :color, :auto_refresh, :access_token, :refresh_token, :active)";
        
        // Prepare params
        $params = [
            ':user_id' => $data['user_id'],
            ':name' => $data['name'],
            ':source' => $data['source'],
            ':source_id' => $data['source_id'],
            ':color' => $data['color'],
            ':auto_refresh' => $data['auto_refresh'],
            ':access_token' => $data['access_token'],
            ':refresh_token' => $data['refresh_token'],
            ':active' => $data['active']
        ];
        
        // Execute
        $result = $this->db->insert($query, $params);
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update a calendar
     * 
     * @param array $data Calendar data
     * @return bool True on success, false on failure
     */
    public function updateCalendar($data) {
        // Start building the query
        $query = 'UPDATE external_calendars SET name = :name, color = :color, auto_refresh = :auto_refresh, active = :active';
        
        // Prepare params
        $params = [
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':color' => $data['color'],
            ':auto_refresh' => $data['auto_refresh'],
            ':active' => $data['active']
        ];
        
        // Add source_id if provided
        if (isset($data['source_id'])) {
            $query .= ', source_id = :source_id';
            $params[':source_id'] = $data['source_id'];
        }
        
        // Add access_token and refresh_token if provided
        if (isset($data['access_token'])) {
            $query .= ', access_token = :access_token';
            $params[':access_token'] = $data['access_token'];
        }
        
        if (isset($data['refresh_token'])) {
            $query .= ', refresh_token = :refresh_token';
            $params[':refresh_token'] = $data['refresh_token'];
        }
        
        if (isset($data['token_expires'])) {
            $query .= ', token_expires = :token_expires';
            $params[':token_expires'] = $data['token_expires'];
        }
        
        // Add the WHERE clause
        $query .= ' WHERE id = :id';
        
        // Execute
        return $this->db->update($query, $params);
    }
    
    /**
     * Remove a calendar
     * 
     * @param int $id Calendar ID
     * @return bool True on success, false on failure
     */
    public function removeCalendar($id) {
        try {
            // First delete all events from this calendar
            $query1 = "DELETE FROM calendar_events WHERE calendar_id = :calendar_id";
            $this->db->remove($query1, [':calendar_id' => $id]);
            
            // Then delete the calendar
            $query2 = "DELETE FROM external_calendars WHERE id = :id";
            $this->db->remove($query2, [':id' => $id]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error in removeCalendar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sync a calendar
     * 
     * @param int $id Calendar ID
     * @return bool True on success, false on failure
     */
    public function syncCalendar($id) {
        // Get the calendar
        $calendar = $this->getCalendarById($id);
        
        if (!$calendar) {
            return false;
        }
        
        // Different sync process based on calendar source
        switch ($calendar['source']) {
            case 'google':
                return $this->syncGoogleCalendar($calendar);
                
            case 'outlook':
            case 'microsoft365':
                return $this->syncMicrosoftCalendar($calendar);
                
            case 'ical':
                return $this->syncIcalCalendar($calendar);
                
            default:
                return false;
        }
    }
    
    /**
     * Sync a Google calendar
     * 
     * @param array $calendar Calendar array
     * @return bool True on success, false on failure
     */
    private function syncGoogleCalendar($calendar) {
        // In a real implementation, this would use the Google Calendar API
        // For now, we'll just return true
        
        // Clear old events
        $this->clearCalendarEvents($calendar['id']);
        
        // Update last synced time
        $this->updateLastSynced($calendar['id']);
        
        return true;
    }
    
    /**
     * Sync a Microsoft 365 calendar using Graph API
     * 
     * @param object $calendar Calendar object
     * @return bool True on success, false on failure
     */
    private function syncMicrosoftCalendar($calendar) {
        try {
            // Check if token is expired and refresh if needed
            if (!$this->ensureValidMicrosoftToken($calendar)) {
                return false;
            }
            
            // Get calendar events from Microsoft Graph API
            $events = $this->getMicrosoftCalendarEvents($calendar);
            
            if ($events === false) {
                return false;
            }
            
            // Clear old events
            $this->clearCalendarEvents($calendar['id']);
            
            // Insert new events
            if (!empty($events)) {
                $this->insertMicrosoftEvents($calendar['id'], $events);
            }
            
            // Update last synced time
            $this->updateLastSynced($calendar['id']);
            
            return true;
            
        } catch (Exception $e) {
            error_log('Microsoft calendar sync error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ensure Microsoft access token is valid, refresh if needed
     * 
     * @param object $calendar Calendar object
     * @return bool True if token is valid, false otherwise
     */
    private function ensureValidMicrosoftToken(&$calendar) {
        // Check if token exists
        if (empty($calendar['access_token'])) {
            return false;
        }
        
        // Check if token is expired (with 5-minute buffer)
        if (!empty($calendar['token_expires'])) {
            $expirationTime = strtotime($calendar['token_expires']);
            $bufferTime = time() + 300; // 5 minutes buffer
            
            if ($expirationTime <= $bufferTime) {
                // Token is expired or about to expire, try to refresh
                return $this->refreshMicrosoftToken($calendar);
            }
        }
        
        return true;
    }
    
    /**
     * Refresh Microsoft access token
     * 
     * @param object $calendar Calendar object
     * @return bool True on success, false on failure
     */
    private function refreshMicrosoftToken(&$calendar) {
        if (empty($calendar['refresh_token'])) {
            return false;
        }
        
        $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
        $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
        
        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }
        
        $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
        
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $calendar['refresh_token'],
            'grant_type' => 'refresh_token',
            'scope' => 'https://graph.microsoft.com/calendars.read offline_access'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $tokenData = json_decode($response, true);
            
            if (isset($tokenData['access_token'])) {
                // Calculate new expiration time
                $expiresIn = $tokenData['expires_in'] ?? 3600;
                $tokenExpires = date('Y-m-d H:i:s', time() + $expiresIn);
                
                // Update the calendar with new token
                $updateData = [
                    'id' => $calendar['id'],
                    'access_token' => $tokenData['access_token'],
                    'token_expires' => $tokenExpires
                ];
                
                // Update refresh token if provided
                if (isset($tokenData['refresh_token'])) {
                    $updateData['refresh_token'] = $tokenData['refresh_token'];
                }
                
                $this->updateCalendar($updateData);
                
                // Update the calendar array for current sync
                $calendar['access_token'] = $tokenData['access_token'];
                $calendar['token_expires'] = $tokenExpires;
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get calendar events from Microsoft Graph API
     * 
     * @param object $calendar Calendar object
     * @return array|false Events array or false on failure
     */
    private function getMicrosoftCalendarEvents($calendar) {
        // Get events from the past month to 3 months in the future
        $startTime = date('c', strtotime('-1 month'));
        $endTime = date('c', strtotime('+3 months'));
        
        $graphUrl = 'https://graph.microsoft.com/v1.0/me/events';
        $params = http_build_query([
            '$filter' => "start/dateTime ge '$startTime' and end/dateTime le '$endTime'",
            '$select' => 'id,subject,bodyPreview,location,start,end,isAllDay,recurrence,webLink',
            '$orderby' => 'start/dateTime',
            '$top' => 1000
        ]);
        
        $url = $graphUrl . '?' . $params;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $calendar['access_token'],
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['value'] ?? [];
        }
        
        return false;
    }
    
    /**
     * Insert Microsoft calendar events into database
     * 
     * @param int $calendar_id Calendar ID
     * @param array $events Events array
     * @return bool True on success, false on failure
     */
    private function insertMicrosoftEvents($calendar_id, $events) {
        foreach ($events as $event) {
            // Parse event data
            $eventId = $event['id'];
            $title = $event['subject'] ?? 'Untitled Event';
            $description = $event['bodyPreview'] ?? '';
            $location = isset($event['location']['displayName']) ? $event['location']['displayName'] : '';
            
            // Parse start and end times
            $startTime = $this->parseMicrosoftDateTime($event['start']);
            $endTime = $this->parseMicrosoftDateTime($event['end']);
            $isAllDay = $event['isAllDay'] ?? false;
            
            $webLink = $event['webLink'] ?? '';
            
            // Insert event (MS SQL Server syntax)
            $query = "MERGE calendar_events AS target
                     USING (SELECT :calendar_id as calendar_id, :event_uid as event_uid, :title as title, 
                            :description as description, :location as location, :start_time as start_time, 
                            :end_time as end_time, :all_day as all_day, :url as url) AS source
                     ON target.calendar_id = source.calendar_id AND target.event_uid = source.event_uid
                     WHEN MATCHED THEN 
                         UPDATE SET title = source.title, description = source.description, location = source.location,
                                   start_time = source.start_time, end_time = source.end_time, all_day = source.all_day,
                                   url = source.url, updated_at = GETDATE()
                     WHEN NOT MATCHED THEN 
                         INSERT (calendar_id, event_uid, title, description, location, start_time, end_time, all_day, url)
                         VALUES (source.calendar_id, source.event_uid, source.title, source.description, source.location,
                                source.start_time, source.end_time, source.all_day, source.url);";
            
            $params = [
                ':calendar_id' => $calendar_id,
                ':event_uid' => $eventId,
                ':title' => $title,
                ':description' => $description,
                ':location' => $location,
                ':start_time' => $startTime,
                ':end_time' => $endTime,
                ':all_day' => $isAllDay ? 1 : 0,
                ':url' => $webLink
            ];
            
            $this->db->insert($query, $params);
        }
        
        return true;
    }
    
    /**
     * Parse Microsoft Graph API datetime format
     * 
     * @param array $dateTimeData DateTime data from Microsoft Graph
     * @return string MySQL datetime format
     */
    private function parseMicrosoftDateTime($dateTimeData) {
        $dateTime = $dateTimeData['dateTime'] ?? '';
        $timeZone = $dateTimeData['timeZone'] ?? 'UTC';
        
        try {
            $dt = new DateTime($dateTime, new DateTimeZone($timeZone));
            $dt->setTimezone(new DateTimeZone('UTC')); // Convert to UTC for storage
            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            // Fallback to current time if parsing fails
            return date('Y-m-d H:i:s');
        }
    }
    
    /**
     * Sync an iCal calendar
     * 
     * @param array $calendar Calendar array
     * @return bool True on success, false on failure
     */
    private function syncIcalCalendar($calendar) {
        try {
            $icalUrl = $calendar['source_id'];
            
            if (empty($icalUrl)) {
                error_log("iCal sync failed: No URL provided for calendar {$calendar['id']}");
                return false;
            }
            
            // Fetch the iCal data using curl for better control
            error_log("Fetching iCal data from: {$icalUrl}");
            $icalData = $this->fetchIcalContent($icalUrl);
            
            if ($icalData === false) {
                error_log("iCal sync failed: Could not fetch data from {$icalUrl}");
                return false;
            }
            
            // Validate that we received iCal content
            if (!$this->isValidIcalContent($icalData)) {
                error_log("iCal sync failed: URL did not return valid iCal content. Got: " . substr($icalData, 0, 200));
                return false;
            }
            
            // Clear old events first
            $this->clearCalendarEvents($calendar['id']);
            
            // Parse the iCal data
            $events = $this->parseIcalData($icalData, $calendar['id']);
            
            error_log("iCal sync: Parsed " . count($events) . " events from calendar {$calendar['id']}");
            
            // Update last synced time
            $this->updateLastSynced($calendar['id']);
            
            return true;
            
        } catch (Exception $e) {
            error_log("iCal sync error for calendar {$calendar['id']}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Fetch iCal content from URL with proper headers
     * 
     * @param string $url The iCal URL
     * @return string|false The iCal content or false on failure
     */
    private function fetchIcalContent($url) {
        // Try curl first (better for handling headers and redirects)
        if (function_exists('curl_init')) {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/calendar,application/calendar,text/plain,*/*',
                    'Accept-Language: en-US,en;q=0.9',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ]
            ]);
            
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $error = curl_error($ch);
            curl_close($ch);
            
            error_log("iCal fetch: HTTP Code: {$httpCode}, Content-Type: {$contentType}");
            
            if ($content === false || !empty($error)) {
                error_log("cURL error: {$error}");
                return false;
            }
            
            if ($httpCode !== 200) {
                error_log("HTTP error: {$httpCode}");
                return false;
            }
            
            return $content;
        }
        
        // Fallback to file_get_contents with enhanced context
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept: text/calendar,application/calendar,text/plain,*/*',
                    'Accept-Language: en-US,en;q=0.9',
                    'Cache-Control: no-cache'
                ],
                'timeout' => 30,
                'follow_location' => true,
                'max_redirects' => 5
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        
        if ($content === false) {
            error_log("file_get_contents failed for URL: {$url}");
        }
        
        return $content;
    }
    
    /**
     * Validate that content is valid iCal format
     * 
     * @param string $content The content to validate
     * @return bool True if valid iCal, false otherwise
     */
    private function isValidIcalContent($content) {
        if (empty($content)) {
            return false;
        }
        
        // Check for HTML content (common when URLs redirect to download pages)
        if (preg_match('/^\s*<!DOCTYPE|<html|<head|<body/i', $content)) {
            error_log("Content appears to be HTML, not iCal");
            return false;
        }
        
        // Check for essential iCal markers
        $hasBeginVcalendar = stripos($content, 'BEGIN:VCALENDAR') !== false;
        $hasEndVcalendar = stripos($content, 'END:VCALENDAR') !== false;
        
        if (!$hasBeginVcalendar || !$hasEndVcalendar) {
            error_log("Content missing required iCal markers (BEGIN:VCALENDAR/END:VCALENDAR)");
            return false;
        }
        
        return true;
    }
    
    /**
     * Parse iCal data and store events
     * 
     * @param string $icalData The iCal data content
     * @param int $calendar_id Calendar ID
     * @return array Array of parsed events
     */
    private function parseIcalData($icalData, $calendar_id) {
        $events = [];
        $lines = explode("\n", str_replace("\r\n", "\n", $icalData));
        $currentEvent = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Handle line folding (long lines that continue on next line starting with space)
            if (isset($nextLine) && (substr($line, 0, 1) === ' ' || substr($line, 0, 1) === "\t")) {
                $nextLine .= substr($line, 1);
                continue;
            }
            
            if (isset($nextLine)) {
                $this->processIcalLine($nextLine, $currentEvent, $calendar_id, $events);
                unset($nextLine);
            }
            
            $nextLine = $line;
        }
        
        // Process the last line
        if (isset($nextLine)) {
            $this->processIcalLine($nextLine, $currentEvent, $calendar_id, $events);
        }
        
        return $events;
    }
    
    /**
     * Process a single iCal line
     * 
     * @param string $line The line to process
     * @param array &$currentEvent Current event being processed
     * @param int $calendar_id Calendar ID
     * @param array &$events Array of all events
     */
    private function processIcalLine($line, &$currentEvent, $calendar_id, &$events) {
        if (empty($line)) return;
        
        // Start of event
        if ($line === 'BEGIN:VEVENT') {
            $currentEvent = [
                'calendar_id' => $calendar_id,
                'event_uid' => '',
                'title' => '',
                'description' => '',
                'location' => '',
                'start_time' => null,
                'end_time' => null,
                'all_day' => 0
            ];
            return;
        }
        
        // End of event
        if ($line === 'END:VEVENT' && $currentEvent) {
            if (!empty($currentEvent['event_uid']) && $currentEvent['start_time']) {
                // Save event to database
                $this->saveCalendarEvent($currentEvent);
                $events[] = $currentEvent;
            }
            $currentEvent = null;
            return;
        }
        
        // Process event properties
        if ($currentEvent && strpos($line, ':') !== false) {
            $colonPos = strpos($line, ':');
            $property = substr($line, 0, $colonPos);
            $value = substr($line, $colonPos + 1);
            
            // Handle properties with parameters (e.g., DTSTART;TZID=...)
            $semicolonPos = strpos($property, ';');
            if ($semicolonPos !== false) {
                $propertyName = substr($property, 0, $semicolonPos);
                $parameters = substr($property, $semicolonPos + 1);
            } else {
                $propertyName = $property;
                $parameters = '';
            }
            
            switch ($propertyName) {
                case 'UID':
                    $currentEvent['event_uid'] = $value;
                    break;
                case 'SUMMARY':
                    $currentEvent['title'] = $this->unescapeIcalText($value);
                    break;
                case 'DESCRIPTION':
                    $currentEvent['description'] = $this->unescapeIcalText($value);
                    break;
                case 'LOCATION':
                    $currentEvent['location'] = $this->unescapeIcalText($value);
                    break;
                case 'DTSTART':
                    $currentEvent['start_time'] = $this->parseIcalDateTime($value, $parameters);
                    // Check if it's an all-day event (date only, no time)
                    if (strlen($value) === 8 && !strpos($value, 'T')) {
                        $currentEvent['all_day'] = 1;
                    }
                    break;
                case 'DTEND':
                    $currentEvent['end_time'] = $this->parseIcalDateTime($value, $parameters);
                    break;
            }
        }
    }
    
    /**
     * Parse iCal date/time format
     * 
     * @param string $value The date/time value
     * @param string $parameters Any parameters (like TZID)
     * @return string Formatted date/time for database
     */
    private function parseIcalDateTime($value, $parameters = '') {
        try {
            // Remove any timezone info from the value itself
            $value = str_replace(['Z'], '', $value);
            
            // Handle different date formats
            if (strlen($value) === 8) {
                // Date only: YYYYMMDD
                $datetime = DateTime::createFromFormat('Ymd', $value);
                if ($datetime) {
                    return $datetime->format('Y-m-d H:i:s');
                }
            } else if (strlen($value) === 15) {
                // DateTime: YYYYMMDDTHHMMSS
                $datetime = DateTime::createFromFormat('Ymd\THis', $value);
                if ($datetime) {
                    return $datetime->format('Y-m-d H:i:s');
                }
            }
            
            // Fallback: try to parse as-is
            $datetime = new DateTime($value);
            return $datetime->format('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            error_log("Error parsing iCal date: {$value} - " . $e->getMessage());
            return date('Y-m-d H:i:s'); // Fallback to current time
        }
    }
    
    /**
     * Unescape iCal text values
     * 
     * @param string $text The text to unescape
     * @return string Unescaped text
     */
    private function unescapeIcalText($text) {
        $text = str_replace(['\\n', '\\N'], "\n", $text);
        $text = str_replace(['\\,', '\\;', '\\\\'], [',', ';', '\\'], $text);
        return trim($text);
    }
    
    /**
     * Save calendar event to database
     * 
     * @param array $eventData Event data array
     * @return bool Success status
     */
    private function saveCalendarEvent($eventData) {
        try {
            $query = "INSERT INTO calendar_events (calendar_id, event_uid, title, description, location, start_time, end_time, all_day) 
                     VALUES (:calendar_id, :event_uid, :title, :description, :location, :start_time, :end_time, :all_day)";
            
            $params = [
                ':calendar_id' => $eventData['calendar_id'],
                ':event_uid' => $eventData['event_uid'],
                ':title' => $eventData['title'],
                ':description' => $eventData['description'],
                ':location' => $eventData['location'],
                ':start_time' => $eventData['start_time'],
                ':end_time' => $eventData['end_time'] ?? $eventData['start_time'],
                ':all_day' => $eventData['all_day']
            ];
            
            $this->db->insert($query, $params);
            return true;
            
        } catch (Exception $e) {
            error_log("Error saving calendar event: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear all events for a calendar
     * 
     * @param int $calendar_id Calendar ID
     * @return bool True on success, false on failure
     */
    private function clearCalendarEvents($calendar_id) {
        $query = "DELETE FROM calendar_events WHERE calendar_id = :calendar_id";
        try {
            $this->db->remove($query, [':calendar_id' => $calendar_id]);
            return true;
        } catch (Exception $e) {
            error_log("Error clearing calendar events: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update the last synced time for a calendar
     * 
     * @param int $calendar_id Calendar ID
     * @return bool True on success, false on failure
     */
    private function updateLastSynced($calendar_id) {
        try {
            $query = "UPDATE external_calendars SET last_synced = GETDATE() WHERE id = :id";
            $this->db->update($query, [':id' => $calendar_id]);
            return true;
        } catch (Exception $e) {
            error_log("Error updating last_synced for calendar {$calendar_id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all events from external calendars for a user
     * 
     * @param int $user_id User ID
     * @return array Events
     */
    public function getCalendarEvents($user_id) {
        try {
            $query = "SELECT ce.*, ec.name as calendar_name, ec.color as calendar_color 
                     FROM calendar_events ce
                     JOIN external_calendars ec ON ce.calendar_id = ec.id
                     WHERE ec.user_id = :user_id AND ec.active = 1
                     ORDER BY ce.start_time";
            
            $events = $this->db->select($query, [':user_id' => $user_id]);
            
            // Debug: Log the raw events from database
            error_log("getCalendarEvents: Found " . count($events) . " events for user " . $user_id);
            if (!empty($events)) {
                error_log("First event raw: " . print_r($events[0], true));
            }
            
            // Format dates for FullCalendar compatibility
            foreach ($events as &$event) {
                // Ensure start_time is in proper format
                if (!empty($event['start_time'])) {
                    $event['start_time'] = date('Y-m-d\TH:i:s', strtotime($event['start_time']));
                }
                
                // Ensure end_time is in proper format
                if (!empty($event['end_time'])) {
                    $event['end_time'] = date('Y-m-d\TH:i:s', strtotime($event['end_time']));
                }
                
                // For all-day events, use date format without time
                if (!empty($event['all_day']) && $event['all_day']) {
                    $event['start_time'] = date('Y-m-d', strtotime($event['start_time']));
                    if (!empty($event['end_time'])) {
                        $event['end_time'] = date('Y-m-d', strtotime($event['end_time']));
                    }
                }
            }
            
            error_log("getCalendarEvents: Returning " . count($events) . " formatted events");
            return $events;
            
        } catch (Exception $e) {
            error_log("Error in getCalendarEvents: " . $e->getMessage());
            return [];
        }
    }
} 