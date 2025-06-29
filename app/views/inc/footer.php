    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Close alerts after 5 seconds
        // Check if flashMessages hasn't been defined yet to avoid duplication
        if (typeof flashMessagesInitialized === 'undefined') {
            const notePageFlashMessages = document.querySelectorAll('.flash-message');
            notePageFlashMessages.forEach(message => {
                setTimeout(() => {
                    const closeButton = message.querySelector('.btn-close');
                    if (closeButton) {
                        closeButton.click();
                    }
                }, 5000);
            });
            // Mark as initialized
            var flashMessagesInitialized = true;
        }
    </script>
</body>
</html>
<?php
// This file is intentionally left empty
// The actual footer is included via the default layout
?> 