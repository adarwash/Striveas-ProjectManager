// Theme Settings Management
document.addEventListener('DOMContentLoaded', function() {
    // Get theme elements
    const lightTheme = document.getElementById('lightTheme');
    const darkTheme = document.getElementById('darkTheme');
    const systemTheme = document.getElementById('systemTheme');
    const fontSizeSelect = document.querySelector('#theme select');
    const saveThemeButton = document.querySelector('#theme button');
    
    // Load saved settings
    loadThemeSettings();
    
    // Save theme settings when button is clicked
    saveThemeButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Determine selected theme
        let selectedTheme = 'light';
        if (darkTheme.checked) {
            selectedTheme = 'dark';
        } else if (systemTheme.checked) {
            selectedTheme = 'system';
        }
        
        // Get selected font size
        const selectedFontSize = fontSizeSelect.value;
        
        // Save settings to localStorage
        const themeSettings = {
            theme: selectedTheme,
            fontSize: selectedFontSize
        };
        
        localStorage.setItem('projectTrackerTheme', JSON.stringify(themeSettings));
        
        // Apply settings
        applyThemeSettings(themeSettings);
        
        // Show success message
        showToast('Theme settings saved successfully!');
    });
    
    // Function to load saved theme settings
    function loadThemeSettings() {
        const savedSettings = localStorage.getItem('projectTrackerTheme');
        
        if (savedSettings) {
            const settings = JSON.parse(savedSettings);
            
            // Set theme radio buttons
            if (settings.theme === 'dark') {
                darkTheme.checked = true;
            } else if (settings.theme === 'system') {
                systemTheme.checked = true;
            } else {
                lightTheme.checked = true;
            }
            
            // Set font size
            if (settings.fontSize) {
                fontSizeSelect.value = settings.fontSize;
            }
            
            // Apply settings
            applyThemeSettings(settings);
        }
    }
    
    // Function to apply theme settings
    function applyThemeSettings(settings) {
        // Apply theme
        let theme = settings.theme;
        
        // If system theme, check system preference
        if (theme === 'system') {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                theme = 'dark';
            } else {
                theme = 'light';
            }
        }
        
        // Apply dark or light theme
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        } else {
            document.body.classList.remove('dark-theme');
            document.documentElement.setAttribute('data-bs-theme', 'light');
        }
        
        // Apply font size
        document.documentElement.style.fontSize = getFontSizeValue(settings.fontSize);
    }
    
    // Function to convert font size name to CSS value
    function getFontSizeValue(size) {
        switch(size) {
            case 'Small':
                return '14px';
            case 'Large':
                return '18px';
            case 'Medium':
            default:
                return '16px';
        }
    }
    
    // Function to show a toast message
    function showToast(message) {
        // Check if Bootstrap's toast component is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            // Create toast element
            const toastEl = document.createElement('div');
            toastEl.className = 'toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            
            const toastBody = document.createElement('div');
            toastBody.className = 'toast-body d-flex';
            toastBody.innerHTML = `
                <div class="me-auto">${message}</div>
                <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
            `;
            
            toastEl.appendChild(toastBody);
            document.body.appendChild(toastEl);
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            
            // Remove the toast element when hidden
            toastEl.addEventListener('hidden.bs.toast', function() {
                document.body.removeChild(toastEl);
            });
        } else {
            // Fallback if Bootstrap toast isn't available
            alert(message);
        }
    }
    
    // Listen for system theme changes
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
            loadThemeSettings();
        });
    }
}); 