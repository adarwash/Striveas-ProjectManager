<?php

/**
 * EmailThreadSplitter
 * 
 * Splits a raw email body (HTML or plain text) into distinct message segments
 * by detecting email header blocks (From:, Date:, To:, Subject:).
 */
class EmailThreadSplitter {
    
    /**
     * Split email body into segments based on header blocks.
     * 
     * @param string $body Raw email body (HTML or plain text)
     * @param string $format 'html' or 'text'
     * @return array Array of segments, each with ['headers' => [...], 'body' => '...']
     */
    public static function split(string $body, string $format = 'html'): array {
        if ($format === 'html') {
            return self::splitHtml($body);
        } else {
            return self::splitText($body);
        }
    }
    
    /**
     * Split plain text email by header blocks.
     */
    private static function splitText(string $text): array {
        $segments = [];
        $lines = explode("\n", $text);
        
        $currentSegment = ['headers' => [], 'body' => ''];
        $inHeaders = false;
        $headerBuffer = [];
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);
            
            // Check if this line starts a header block
            if (preg_match('/^(From|Date|To|Subject|Sent|Cc|Bcc):\s*/i', $trimmed)) {
                // If we already have a header buffer, check if we have all required headers
                if (!empty($headerBuffer)) {
                    $hasFrom = false;
                    $hasDate = false;
                    $hasTo = false;
                    $hasSubject = false;
                    
                    foreach ($headerBuffer as $h) {
                        if (preg_match('/^From:/i', $h)) $hasFrom = true;
                        if (preg_match('/^Date:|^Sent:/i', $h)) $hasDate = true;
                        if (preg_match('/^To:/i', $h)) $hasTo = true;
                        if (preg_match('/^Subject:/i', $h)) $hasSubject = true;
                    }
                    
                    // If we have at least From + Date (or From + To), start new segment
                    if (($hasFrom && $hasDate) || ($hasFrom && $hasTo)) {
                        // Save current segment if it has content
                        if (!empty($currentSegment['body']) || !empty($currentSegment['headers'])) {
                            $segments[] = $currentSegment;
                        }
                        
                        // Start new segment with buffered headers
                        $currentSegment = [
                            'headers' => self::parseHeaders(implode("\n", $headerBuffer)),
                            'body' => ''
                        ];
                        $headerBuffer = [];
                        $inHeaders = false;
                    }
                }
                
                $headerBuffer[] = $line;
                $inHeaders = true;
                continue;
            }
            
            // If we're in a header block and line is continuation or blank
            if ($inHeaders) {
                if ($trimmed === '' || preg_match('/^\s+/', $line)) {
                    // Blank line might end header block
                    if ($trimmed === '') {
                        $inHeaders = false;
                    } else {
                        // Continuation line (indented)
                        $headerBuffer[count($headerBuffer) - 1] .= ' ' . $trimmed;
                    }
                    continue;
                } else {
                    // Non-header line, end header block
                    $inHeaders = false;
                }
            }
            
            // Regular body line
            $currentSegment['body'] .= $line . "\n";
        }
        
        // Save final segment
        if (!empty($currentSegment['body']) || !empty($currentSegment['headers'])) {
            $segments[] = $currentSegment;
        }
        
        return $segments;
    }
    
    /**
     * Split HTML email by header blocks.
     */
    private static function splitHtml(string $html): array {
        // Common header block patterns (Outlook, Gmail, etc.)
        $patterns = [
            // Outlook style: <b>From:</b> ... <b>Sent:</b> ... <b>To:</b> ... <b>Subject:</b>
            '#<div[^>]*>\s*<b>\s*(From|Sent|Date):\s*</b>.*?<b>\s*(To|Date|Sent):\s*</b>.*?<b>\s*Subject:\s*</b>.*?</div>#is',
            // Plain style with <strong> instead of <b>
            '#<div[^>]*>\s*<strong>\s*(From|Sent|Date):\s*</strong>.*?<strong>\s*(To|Date|Sent):\s*</strong>.*?<strong>\s*Subject:\s*</strong>.*?</div>#is',
            // Outlook mobile/OWA style
            '#<div[^>]*class="[^"]*ms-outlook-mobile-reference-message[^"]*"[^>]*>\s*<b>\s*(From|Sent):\s*</b>.*?<b>\s*(Date|Sent):\s*</b>.*?<b>\s*Subject:\s*</b>#is',
        ];
        
        $headerMatches = [];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $headerMatches[] = [
                        'text' => $match[0],
                        'pos' => $match[1]
                    ];
                }
            }
        }
        
        // Sort by position
        usort($headerMatches, function($a, $b) {
            return $a['pos'] - $b['pos'];
        });
        
        if (empty($headerMatches)) {
            // No header blocks found, return as single segment
            return [[
                'headers' => [],
                'body' => $html
            ]];
        }
        
        $segments = [];
        $lastPos = 0;
        
        foreach ($headerMatches as $i => $headerMatch) {
            $pos = $headerMatch['pos'];
            
            // Body before this header block
            if ($pos > $lastPos) {
                $bodyBefore = substr($html, $lastPos, $pos - $lastPos);
                $bodyBeforeClean = trim(strip_tags($bodyBefore));
                
                if ($bodyBeforeClean !== '') {
                    $segments[] = [
                        'headers' => [],
                        'body' => $bodyBefore
                    ];
                }
            }
            
            // Determine where this segment's body starts (after header block)
            $headerEndPos = $pos + strlen($headerMatch['text']);
            
            // Determine where this segment's body ends (at next header or end of email)
            $nextPos = isset($headerMatches[$i + 1]) ? $headerMatches[$i + 1]['pos'] : strlen($html);
            
            $bodyContent = substr($html, $headerEndPos, $nextPos - $headerEndPos);
            
            $segments[] = [
                'headers' => self::parseHtmlHeaders($headerMatch['text']),
                'body' => $bodyContent
            ];
            
            $lastPos = $nextPos;
        }
        
        return $segments;
    }
    
    /**
     * Parse plain text headers into array.
     */
    private static function parseHeaders(string $headerText): array {
        $headers = [];
        $lines = explode("\n", $headerText);
        
        foreach ($lines as $line) {
            if (preg_match('/^(From|Date|Sent|To|Subject|Cc|Bcc):\s*(.+)$/i', trim($line), $m)) {
                $key = strtolower($m[1]);
                $value = trim($m[2]);
                
                // Normalize "Sent" to "date"
                if ($key === 'sent') {
                    $key = 'date';
                }
                
                $headers[$key] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Parse HTML header block into array.
     */
    private static function parseHtmlHeaders(string $html): array {
        $text = strip_tags($html);
        return self::parseHeaders($text);
    }
}
