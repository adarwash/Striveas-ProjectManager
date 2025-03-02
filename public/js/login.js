/**
 * Login Page JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add password toggle functionality
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        // Create toggle button
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'password-toggle';
        toggleButton.innerHTML = '<i class="bi bi-eye"></i>';
        
        // Insert toggle button after password input
        const passwordContainer = passwordInput.parentNode;
        passwordContainer.style.position = 'relative';
        passwordContainer.appendChild(toggleButton);
        
        // Add toggle functionality
        toggleButton.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    }
    
    // Form validation
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            
            // Simple validation
            if (usernameInput && usernameInput.value.trim() === '') {
                event.preventDefault();
                alert('Please enter your username');
                usernameInput.focus();
                return false;
            }
            
            if (passwordInput && passwordInput.value.trim() === '') {
                event.preventDefault();
                alert('Please enter your password');
                passwordInput.focus();
                return false;
            }
            
            return true;
        });
    }
}); 