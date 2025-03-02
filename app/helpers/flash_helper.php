<?php

/**
 * Create a flash message
 *
 * @param string $name The name of the flash message
 * @param string $message The message content
 * @param string $class Optional CSS class for styling (default: alert-success)
 * @return void
 */
function flash($name = '', $message = '', $class = 'alert-success') {
    // Nothing passed, return all flash messages
    if (empty($name)) {
        return $_SESSION['flash_messages'] ?? [];
    }
    
    // Message passed, create flash message
    if (!empty($message) && !empty($name)) {
        // Remove existing message with same name
        if (isset($_SESSION['flash_messages'][$name])) {
            unset($_SESSION['flash_messages'][$name]);
        }
        
        // Add the message to the session
        $_SESSION['flash_messages'][$name] = [
            'message' => $message,
            'class' => $class
        ];
    } 
    // No message passed, display flash message
    elseif (!empty($name) && isset($_SESSION['flash_messages'][$name])) {
        // Get the flash message
        $flash_message = $_SESSION['flash_messages'][$name];
        
        // Delete the flash message
        unset($_SESSION['flash_messages'][$name]);
        
        // Display the flash message
        echo '<div class="alert ' . $flash_message['class'] . ' alert-dismissible fade show" role="alert">' .
             $flash_message['message'] .
             '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
             '</div>';
    }
} 