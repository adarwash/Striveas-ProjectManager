    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container-fluid px-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; <?= date('Y') ?> <?= SITENAME ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        Secured with Microsoft 365
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-hide alerts -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Add loading states to buttons (but exclude AJAX forms)
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('button[type="submit"], .btn-loading');
            buttons.forEach(button => {
                // Skip buttons that are part of AJAX forms
                if (button.closest('#customer-reply-form')) return;
                
                button.addEventListener('click', function() {
                    if (!this.disabled) {
                        const originalText = this.innerHTML;
                        this.disabled = true;
                        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...';
                        
                        // Re-enable after 10 seconds as fallback
                        setTimeout(() => {
                            this.disabled = false;
                            this.innerHTML = originalText;
                        }, 10000);
                    }
                });
            });
            
            // Handle customer reply form with AJAX
            const replyForm = document.getElementById('customer-reply-form');
            if (replyForm) {
                console.log('Reply form found, setting up AJAX handler');
                replyForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    console.log('Reply form submitted');
                    
                    const btn = this.querySelector('#reply-btn');
                    const btnText = btn.querySelector('.btn-text');
                    const textarea = this.querySelector('textarea[name="content"]');
                    const csrf = this.querySelector('input[name="csrf_token"]').value;
                    const content = textarea.value.trim();
                    
                    if (!content) {
                        alert('Please enter a message');
                        return;
                    }
                    
                    // Show loading state
                    btn.disabled = true;
                    btnText.textContent = 'Sending...';
                    
                    try {
                        console.log('Sending AJAX request to:', this.action);
                        const response = await fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Accept': 'application/json'
                            },
                            body: new URLSearchParams({
                                'content': content,
                                'csrf_token': csrf
                            })
                        });
                        
                        console.log('Response status:', response.status);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        
                        const result = await response.json();
                        console.log('Response data:', result);
                        
                        if (result.success) {
                            // Clear the form and reload
                            textarea.value = '';
                            window.location.reload();
                        } else {
                            alert(result.error || 'Failed to post reply');
                        }
                    } catch (error) {
                        console.error('AJAX error:', error);
                        alert('Failed to post reply. Please try again.');
                    } finally {
                        // Reset button state
                        btn.disabled = false;
                        btnText.textContent = 'Send';
                    }
                });
            } else {
                console.log('Reply form not found');
            }
        });
    </script>
</body>
</html>
