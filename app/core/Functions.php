<?php
/**
 * Helper Class
 * 
 * Beta Version 1.2.5
 * 
 * Improvements:
 *  1) getfile() and getfiles() now return strings (instead of echo), allowing more flexible usage.
 *  2) secureString() accepts an optional $key parameter and handles errors gracefully.
 *  3) timeAgo() handles future timestamps and invalid inputs.
 *  4) csv_dump() returns a boolean, handles empty/invalid data, and checks file operations.
 *  5) getOS() handles missing or empty user-agent strings.
 *  6) ldap() logs connection/bind/search failures instead of silently failing.
 *  7) Additional minor checks, validations, and improvements to code clarity.
 */

/**
 * Sanitize user input to prevent XSS and other security issues
 *
 * @param mixed $input The input to sanitize
 * @return mixed The sanitized input
 */
function sanitize_input($input) {
    if (is_array($input)) {
        // Recursively sanitize arrays
        foreach ($input as $key => $value) {
            $input[$key] = sanitize_input($value);
        }
        return $input;
    } elseif (is_string($input)) {
        // Trim and sanitize strings
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    } else {
        // Return other types as is
        return $input;
    }
}

/**
 * Check if the current user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

/**
 * Check if the current user is an admin
 * @return bool True if user is an admin, false otherwise
 */
function isAdmin() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if the current URL path matches the given path
 * @param string $path The path to check against
 * @return bool True if the URL matches the path, false otherwise
 */
function urlIs($path) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $requestUri = strtok($requestUri, '?');
    
    // Check for exact match
    if ($path === $requestUri) {
        return true;
    }
    
    // Check for wildcard match (e.g., '/projects*')
    if (substr($path, -1) === '*') {
        $basePath = rtrim(substr($path, 0, -1), '/');
        return strpos($requestUri, $basePath) === 0;
    }
    
    return false;
}

/**
 * Redirect to a URL
 * @param string $url The URL to redirect to
 * @return void
 */
function redirect($url) {
    // Ensure the URL starts with a slash for relative URLs
    if (substr($url, 0, 1) !== '/' && substr($url, 0, 4) !== 'http') {
        $url = '/' . $url;
    }
    
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit;
    }
    echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '" /></noscript>';
    exit;
}

class Helper
{
    /**
     * Display PHP errors on the page.
     */
    public static function phperror(): void
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }

    /**
     * Retrieve the current page name without the file extension.
     *
     * @return string The lowercase name of the current page (e.g., 'index' for 'index.php').
     */
    public static function pageName(): string
    {
        // Get the current script name
        $scriptName = $_SERVER['PHP_SELF'] ?? '';

        // Extract the base name of the script (e.g., 'index.php')
        $baseName = basename($scriptName);

        // Remove the file extension and convert to lowercase
        $pageName = strtolower(pathinfo($baseName, PATHINFO_FILENAME));

        // Default to 'index' if no page name is determined
        return $pageName ?: 'index';
    }

    /**
     * Return a single <link> or <script> tag (for CSS or JS).
     *
     * @param string $filename
     * @param string $path
     * @param string $type     (css, js)
     * @param string $version
     * @return string
     */
    public static function getfile(string $filename, string $path, string $type, string $version = ""): string
    {
        $type = strtolower($type);
        $version = $version !== "" ? '?' . htmlspecialchars($version) : '';
        // Sanitize inputs
        $file = rtrim($path, '/') . '/' . htmlspecialchars($filename);

        if ($type === 'css') {
            return '<link rel="stylesheet" type="text/css" href="' . $file . $version . '">';
        } elseif ($type === 'js' || $type === 'javascript') {
            return '<script src="' . $file . $version . '"></script>';
        }

        return '';
    }

    /**
     * Echo all CSS or JS tags in a given path.
     *
     * @param string $path
     * @param string $type
     * @param string $version
     * @return void
     */
    public static function getfiles(string $path, string $type, string $version = ""): void
    {
        $type = strtolower($type);
        $version = $version !== "" ? '?' . htmlspecialchars($version) : '';

        if ($type === 'css') {
            foreach (glob(rtrim($path, '/') . '/*.css') as $filename) {
                $file = '/' . ltrim($filename, '/');
                echo '<link rel="stylesheet" type="text/css" href="' . $file . $version . '">';
            }
        } elseif ($type === 'js' || $type === 'javascript') {
            foreach (glob(rtrim($path, '/') . '/*.js') as $filename) {
                $file = '/' . ltrim($filename, '/');
                echo '<script src="' . $file . $version . '"></script>';
            }
        }
    }


    /**
     * Generate a random string.
     *
     * @param int    $length
     * @param string $letterOnly ('y' => letters only, 'n' => letters + digits + symbols)
     * @return string
     */
    public static function random_string(int $length = 10, string $letterOnly = 'n'): string
    {
        if ($letterOnly === 'y') {
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $symbols           = '!@%()$#';
        $symbolsLength     = strlen($symbols);
        $charactersLength  = strlen($characters);
        $randomString      = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        if ($letterOnly !== 'y') {
            $randomString .= $symbols[rand(0, $symbolsLength - 1)];
        }

        return str_shuffle($randomString);
    }

    /**
     * Redirect to a page using headers or JS as fallback.
     *
     * @param string $url
     */
    public static function pageRedirect(string $url): void
    {
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit;
        }
        echo '<script type="text/javascript">window.location.href="' . $url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '" /></noscript>';
        exit;
    }

    /**
     * Encrypt or decrypt a string using AES-256-CBC.
     *
     * @param string      $mode   ('e' => encrypt, 'd' => decrypt)
     * @param string      $string The input string
     * @param string|null $key    Optional custom key (base64-encoded). If omitted, default is used.
     * 
     * @return string|bool Encrypted/Decrypted string, or false on failure.
     */
    public static function secureString(string $mode, string $string, string $key = null)
    {
        // Default encryption key (base64-encoded)
        if (!$key) {
            $key = 'CPLci2/nPPnGHKDdSdIzKet8MmXhjGU1AtkfXZZdU33dKlrnLGaixEgUIEgY8yc0QmiHLW1OshQGZSAShcWvxw==';
        }
        $encryption_key = base64_decode($key);

        if ($mode === 'e') {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted = openssl_encrypt($string, 'aes-256-cbc', $encryption_key, 0, $iv);
            return base64_encode($encrypted . '::' . $iv);
        }

        if ($mode === 'd') {
            if (strpos($string, '::') === false) {
                return false; // Invalid format
            }
            list($encrypted_data, $iv) = explode('::', base64_decode($string), 2);
            return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
        }

        return false; // Invalid mode
    }

    /**
     * Convert array data into a CSV file.
     *
     * @param array  $data
     * @param string $file_name
     * @return bool  Returns true on success, false on failure or invalid data
     */
    public static function csv_dump(array $data, string $file_name): bool
    {
        if (empty($data)) {
            return false;
        }
        $createFile = @fopen($file_name, "w+");
        if (!$createFile) {
            return false;
        }

        $header = false;
        foreach ($data as $row) {
            if (!$header) {
                if (!empty($row)) {
                    fputcsv($createFile, array_keys($row));
                }
                $header = true;
            }
            fputcsv($createFile, $row);
        }
        fclose($createFile);
        return true;
    }

    /**
     * Strip back HTML chars to original. Replaces encoded tags with actual HTML tags.
     *
     * @param string $text
     * @return string
     */
    public static function stripHTMLChars(string $text): string
    {
        $text = htmlspecialchars($text, ENT_NOQUOTES, "UTF-8");
        $text = preg_replace("/=/", "=\"\"", $text);
        $text = preg_replace("/&quot;/", "&quot;\"", $text);
        $tags = "/&lt;(\/|)(\w*)(\ |)(\w*)([\\\=]*)(?|(\")\"&quot;\"|)(?|(.*)?&quot;(\")|)([\ ]?)(\/|)&gt;/i";
        $replacement = "<$1$2$3$4$5$6$7$8$9$10>";
        $text = preg_replace($tags, $replacement, $text);
        $text = preg_replace("/=\"\"/", "=", $text);
        return $text;
    }

    /**
     * Check if an IP is public or internal.
     *
     * @param string $ip
     * @return bool
     */
    public static function isIPPublic(string $ip = ""): bool
    {
        if ($ip === "") {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        $check = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
        return ($check !== false);
    }

    /**
     * Return a human-readable "time ago" string from a past timestamp.
     *
     * @param int $time (Unix timestamp)
     * @return string
     */
    public static function timeAgo(int $time): string
    {
        if ($time <= 0) {
            return "-";
        }

        $diff = time() - $time;
        if ($diff < 0) {
            // If timestamp is in the future
            return "In the future";
        }

        $tokens = [
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
            1        => 'second'
        ];

        foreach ($tokens as $unit => $text) {
            if ($diff >= $unit) {
                $numberOfUnits = floor($diff / $unit);
                return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
            }
        }

        return "Just now"; // If somehow less than 1 second
    }

    /**
     * Get the base site URL (http/https + host).
     *
     * @return string
     */
    public static function siteURL(): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . "://" . $host;
    }

    /**
     * Check if the request is Ajax (XMLHttpRequest).
     *
     * @return bool
     */
    public static function isAjaxRequest(): bool
    {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
        );
    }

    /**
     * Get the name of the page from an AJAX request (from URI).
     *
     * @return string
     */
    public static function ajaxPageName(): string
    {
        $page_name = $_SERVER['REQUEST_URI'] ?? '';
        $parts = explode('/', $page_name);
        // Return second segment if it exists
        return $parts[1] ?? '';
    }

    /**
     * Connect to an LDAP server and perform a user lookup.
     *
     * @param string $server
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $ou
     * @return array|null
     */
    public static function ldap(string $server, string $username, string $password, string $domain, string $ou): ?array
    {
        $adServer = "ldap://" . $server;
        $ldap = ldap_connect($adServer);

        if (!$ldap) {
            error_log("LDAP connection failed to {$adServer}");
            return null;
        }

        $ldaprdn = $username . '@' . $domain;
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $ldaprdn, $password);
        if (!$bind) {
            error_log("LDAP bind failed for user {$ldaprdn}");
            return null;
        }

        $filter = "(sAMAccountName={$username})";
        $result = @ldap_search($ldap, $ou, $filter);
        if (!$result) {
            error_log("LDAP search failed");
            return null;
        }

        $info = @ldap_get_entries($ldap, $result);
        ldap_close($ldap);

        return $info ?: null;
    }

    /**
     * Search LDAP for a specified user, separate from a normal bind.
     *
     * @param string $server
     * @param string $username
     * @param string $password
     * @param string $domain
     * @param string $ou
     * @param string $user
     * @return array|null
     */
    public static function ldapSearch(string $server, string $username, string $password, string $domain, string $ou, string $user = ""): ?array
    {
        $adServer = "ldap://" . $server;
        $ldap = ldap_connect($adServer);
        if (!$ldap) {
            error_log("LDAP connection failed to {$adServer}");
            return null;
        }

        $ldaprdn = $username . '@' . $domain;
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $ldaprdn, $password);
        if (!$bind) {
            error_log("LDAP bind failed for user {$ldaprdn}");
            return null;
        }

        $filter = "(sAMAccountName={$user})";
        $result = @ldap_search($ldap, $ou, $filter);
        if (!$result) {
            error_log("LDAP search failed");
            return null;
        }

        $info = @ldap_get_entries($ldap, $result);
        ldap_close($ldap);

        return $info ?: null;
    }

    /**
     * Get the client's address (IP or hostname).
     *
     * This method retrieves the client's IP address from the HTTP headers or `REMOTE_ADDR`.
     * It validates the IP address and supports IPv4, IPv6, and hostname resolution.
     *
     * @param string $hostname Specify 'hostname' to resolve the IP address to a hostname (default).
     *                         Any other value returns the IP address directly.
     * @param bool   $ipv6     Whether to allow IPv6 addresses (default: true).
     * @param bool   $ipv4     Whether to allow IPv4 addresses (default: true).
     * @return string The client's IP address or hostname. Returns '0.0.0.0' if no valid IP is found.
     */

    public static function getClientAddress(
        string $hostname = 'hostname', 
        bool $ipv6 = true, 
        bool $ipv4 = true
    ): string {
        $ip = '';
    
        // Prioritize potential client IPs
        $potentialIps = [
            $_SERVER['HTTP_CLIENT_IP'] ?? '',
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
        ];
    
        foreach ($potentialIps as $potentialIp) {
            // If HTTP_X_FORWARDED_FOR is present, use the first IP
            if (strpos($potentialIp, ',') !== false) {
                $potentialIp = explode(',', $potentialIp)[0];
            }
    
            $potentialIp = trim($potentialIp);
    
            // Validate IP based on specified preferences
            if ($ipv6 && filter_var($potentialIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $ip = $potentialIp;
                break;
            }
    
            if ($ipv4 && filter_var($potentialIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ip = $potentialIp;
                break;
            }
        }
    
        // Resolve hostname if requested and IP is valid
        if ($hostname === 'hostname' && $ip) {
            $resolvedHostname = gethostbyaddr($ip);
            if ($resolvedHostname && $resolvedHostname !== $ip) {
                return $resolvedHostname;
            }
        }
    
        return $ip ?: '0.0.0.0'; // Default fallback for invalid/no IP
    }
    

    /**
     * Execute a command via SSH (requires PHP's ssh2 extension).
     *
     * @param string $address
     * @param string $username
     * @param string $password
     * @param string $command
     * @return string
     */
    public static function ssh(string $address, string $username, string $password, string $command): string
    {
        // Check if the port is open
        $connection = @fsockopen($address, 22);
        if (!is_resource($connection)) {
            // Port 22 is closed or host is unreachable
            return '';
        }
        fclose($connection);

        $conn = ssh2_connect($address, 22);
        if (!$conn) {
            return '';
        }

        if (!@ssh2_auth_password($conn, $username, $password)) {
            return '';
        }

        $stream = ssh2_exec($conn, $command);
        if (!$stream) {
            return '';
        }

        stream_set_blocking($stream, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);

        return stream_get_contents($stream_out);
    }

    /**
     * Get the OS from a user agent string (fallback: 'Unknown').
     *
     * @return string
     */
    public static function getOS(): string
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!$user_agent) {
            return "Unknown";
        }

        $os_array = [
            '/windows nt 10/i'     => 'Windows 10',
            '/windows nt 6.3/i'    => 'Windows 8.1',
            '/windows nt 6.2/i'    => 'Windows 8',
            '/windows nt 6.1/i'    => 'Windows 7',
            '/windows nt 6.0/i'    => 'Windows Vista',
            '/windows nt 5.2/i'    => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'    => 'Windows XP',
            '/windows xp/i'        => 'Windows XP',
            '/macintosh|mac os x/i'=> 'Mac OS X',
            '/mac_powerpc/i'       => 'Mac OS 9',
            '/linux/i'             => 'Linux',
            '/ubuntu/i'            => 'Ubuntu',
            '/iphone/i'            => 'iPhone',
            '/ipod/i'              => 'iPod',
            '/ipad/i'              => 'iPad',
            '/android/i'           => 'Android',
            '/blackberry/i'        => 'BlackBerry',
            '/webos/i'             => 'Mobile',
            '/X11; CrOS armv7l/'   => 'Pi'
        ];

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                return $value;
            }
        }
        return "Unknown";
    }

    /**
     * Check if a user is likely accessing via a proxy (by looking at proxy headers).
     *
     * @return bool
     */
    public static function isProxy(): bool
    {
        $test_HTTP_proxy_headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR_IP',
            'X-PROXY-ID',
            'MT-PROXY-ID',
            'X-TINYPROXY',
            'X_FORWARDED_FOR',
            'FORWARDED_FOR',
            'X_FORWARDED',
            'FORWARDED',
            'CLIENT-IP',
            'CLIENT_IP',
            'PROXY-AGENT',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'FORWARDED_FOR_IP',
            'HTTP_PROXY_CONNECTION',
            'HTTP_VIA',
            'VIA',
            'Proxy-Connection'
        ];

        foreach ($test_HTTP_proxy_headers as $header) {
            if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
                return true;
            }
        }
        return false;
    }

    /**
     * var_dump in a pre-block for readability.
     *
     * @param mixed $v
     */
    public static function vdump($v): void
    {
        echo '<pre>';
        var_dump($v);
        echo '</pre>';
    }

    /**
     * Generate a basic Chart.js chart. Returns the entire <canvas> + <script> as a string.
     *
     * @param array       $data   [ 'Label1' => 10, 'Label2' => 20 ]
     * @param string      $id     DOM ID for the canvas
     * @param string      $type   'bar', 'line', etc.
     * @param array|null  $colors Array of RGBA colors for each data point
     * @param bool        $grid   Show grid lines
     * @param bool        $y      Show Y-axis
     * @param bool        $x      Show X-axis
     * @return string
     */
    public static function chartJS(
        array $data,
        string $id = 'Chart',
        string $type = 'bar',
        array $colors = null,
        bool $grid = false,
        bool $y = true,
        bool $x = true
    ): string {
        $labels = json_encode(array_keys($data));
        $dataValues = array_values($data);
        $dataJson = json_encode($dataValues);

        $defaultColors = [
            'rgba(255, 0, 0, 0.2)',
            'rgba(0, 128, 0, 0.2)',
            'rgba(0, 0, 255, 0.2)',
            'rgba(255, 255, 0, 0.2)',
            'rgba(255, 105, 180, 0.2)',
            'rgba(128, 0, 128, 0.2)'
        ];

        if ($colors === null) {
            $colors = [];
            for ($i = 0; $i < count($dataValues); $i++) {
                $colors[] = $defaultColors[$i % count($defaultColors)];
            }
        }
        $colorsJson = json_encode($colors);

        $gridConfig = $grid
            ? "grid: { display: true },"
            : "grid: { display: false },";

        $yDisplay = $y ? 'true' : 'false';
        $xDisplay = $x ? 'true' : 'false';

        return <<<CHARTHTML
<div>
    <canvas id="$id"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('$id').getContext('2d');
        var $id = new Chart(ctx, {
            type: '$type',
            data: {
                labels: $labels,
                datasets: [{
                    label: '# of Votes',
                    data: $dataJson,
                    backgroundColor: $colorsJson,
                    borderColor: [
                        '#ff6384',
                        'rgba(0, 128, 0, 1)',
                        '#36a2eb',
                        'rgba(255, 255, 0, 1)',
                        'rgba(255, 105, 180, 1)',
                        'rgba(128, 0, 128, 1)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        display: $yDisplay,
                        $gridConfig
                    },
                    x: {
                        display: $xDisplay,
                        $gridConfig
                    }
                }
            }
        });
    </script>
</div>
CHARTHTML;
    }

    /**
     * Move specific array values to the top (supports flat or associative arrays).
     *
     * @param array  $array
     * @param array  $values
     * @param string $sortBy ('value' or 'key')
     * @return array|false
     */
    public static function moveToTop(array $array, array $values, string $sortBy = 'value')
    {
        if (empty($array)) {
            return false;
        }

        $isFlatArray = array_keys($array) === range(0, count($array) - 1);

        $matchingItems = [];
        $nonMatchingItems = [];

        if ($isFlatArray) {
            // For a flat array
            foreach ($array as $index => $item) {
                if (in_array($item, $values, true)) {
                    $matchingItems[$index] = $item;
                } else {
                    $nonMatchingItems[$index] = $item;
                }
            }
        } else {
            // For associative arrays
            if ($sortBy === 'key') {
                // If you needed to match by a subarray key, you'd do it here.
                // For now, replicate the 'value' approach for demonstration.
                foreach ($array as $index => $item) {
                    if (in_array($item, $values, true)) {
                        $matchingItems[$index] = $item;
                    } else {
                        $nonMatchingItems[$index] = $item;
                    }
                }
            } else {
                // Default 'value' approach
                foreach ($array as $index => $item) {
                    if (in_array($item, $values, true)) {
                        $matchingItems[$index] = $item;
                    } else {
                        $nonMatchingItems[$index] = $item;
                    }
                }
            }
        }

        // For associative arrays, we can preserve keys using '+'
        if (!$isFlatArray) {
            return $matchingItems + $nonMatchingItems;
        }

        // For flat arrays
        return $matchingItems + $nonMatchingItems;
    }

    /**
     * Create a GUID/UUID. If com_create_guid not available, emulate it.
     *
     * @return string
     */
    public static function create_guid(): string
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        }

        mt_srand((double) microtime() * 10000);
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen
              . substr($charid, 8, 4) . $hyphen
              . substr($charid, 12, 4) . $hyphen
              . substr($charid, 16, 4) . $hyphen
              . substr($charid, 20, 12);

        return $uuid;
    }

    /**
     * Return font-awesome icons. Example usage: Helper::icons('check').
     *
     * @param string $icons
     * @return string
     */
    public static function icons(string $icons = ''): string
    {
        if ($icons === '') {
            error_log('No icon was selected', 0);
            return '';
        }

        if ($icons === 'check') {
            return '<i class="fa-duotone fa-circle-check fa-xl" style="--fa-primary-color: #24a333; --fa-secondary-color: #24a333;"></i>';
        }

        if ($icons === 'xmark') {
            return '<i class="fa-duotone fa-circle-xmark fa-xl" style="--fa-primary-color: #ec3257; --fa-secondary-color: #ec3257;"></i>';
        }

        // Fallback icon or empty
        return '';
    }

    /**
     * Private constructor to prevent instantiation since all methods are static.
     */
    private function __construct()
    {
        // Prevent direct instantiation.
    }
}
