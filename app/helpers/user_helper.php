<?php
/**
 * User related helper functions
 */

/**
 * Generate an avatar URL for a user
 * 
 * @param string $name The user's name to generate an avatar for
 * @return string The URL to the avatar image
 */
function getAvatarUrl($name) {
    // Check if the User model has a method for this (for backward compatibility)
    global $userModel;
    if (isset($userModel) && method_exists($userModel, 'getDefaultProfilePicture')) {
        return $userModel->getDefaultProfilePicture($name);
    }
    
    // Default implementation using UI Avatars service
    $name = urlencode($name);
    return "https://ui-avatars.com/api/?name={$name}&background=random&size=256";
}

/**
 * Format a date using system settings
 * 
 * @param string $date The date string to format
 * @return string The formatted date
 */
function formatDate($date) {
    if (empty($date)) {
        return 'N/A';
    }
    
    // Convert to datetime object
    $dateObj = new DateTime($date);
    
    // Format using system settings if available, otherwise use default
    global $settingsModel;
    $format = 'M j, Y'; // Default format
    
    if (isset($settingsModel)) {
        $settings = $settingsModel->getSystemSettings();
        if (isset($settings['default_date_format'])) {
            $format = $settings['default_date_format'];
        }
    }
    
    return $dateObj->format($format);
}

/**
 * Format a datetime with time
 * 
 * @param string $datetime The datetime string to format
 * @return string The formatted datetime
 */
function formatDateTime($datetime) {
    if (empty($datetime)) {
        return 'N/A';
    }
    
    // Convert to datetime object
    $dateObj = new DateTime($datetime);
    
    // Format using system settings if available, otherwise use default
    global $settingsModel;
    $format = 'M j, Y g:i A'; // Default format
    
    if (isset($settingsModel)) {
        $settings = $settingsModel->getSystemSettings();
        if (isset($settings['default_date_format'])) {
            $dateFormat = $settings['default_date_format'];
            $format = $dateFormat . ' g:i A';
        }
    }
    
    return $dateObj->format($format);
}

/**
 * Generate a consistent color from a string (name)
 * 
 * @param string $string The string to generate a color from
 * @return string A hex color code
 */
function generateColorFromString($string) {
    // Generate a hash of the string
    $hash = md5($string);
    
    // Use first 6 characters for the color
    $hex = substr($hash, 0, 6);
    
    // Make sure the color isn't too light (for visibility on white backgrounds)
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness if too light
    if (($r + $g + $b) > 550) {
        $r = $r * 0.8;
        $g = $g * 0.8;
        $b = $b * 0.8;
    }
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
} 