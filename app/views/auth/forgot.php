<?php
// Simple forgot password form
echo Boot::loginContainer(
    title: 'Forgot Password',
    content: '<form class="login-form">
        <p class="mb-3">Please enter your email address. We will send you a link to reset your password.</p>
        <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
            <label for="email">Email Address</label>
        </div>
        <div class="d-flex justify-content-between">
            <a href="/auth" class="btn btn-outline-secondary">Back to Login</a>
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </div>
    </form>',
    footerText: '&copy; ' . date('Y') . ' Your Company Name',
    icon: 'bi bi-key'
);

// Add custom body class for styling
echo '<script>document.body.classList.add("login-page");</script>';
?> 