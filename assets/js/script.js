// =============================================
// 🌿 FORM VALIDATION HELPERS
// =============================================

/**
 * Validate a form field
 */
function validateField(input, message) {
    if (!input.value.trim()) {
        showError(message || 'This field is required');
        input.focus();
        input.style.borderColor = '#E53935';
        return false;
    }
    input.style.borderColor = '#43A047';
    return true;
}

/**
 * Validate numeric input (height or weight)
 */
function validateNumeric(input, label, min, max) {
    const value = parseFloat(input.value);
    if (isNaN(value) || value <= 0) {
        showError('Please enter a valid ' + label);
        input.focus();
        input.style.borderColor = '#E53935';
        return false;
    }
    if (min && value < min) {
        showError(label + ' must be at least ' + min);
        input.focus();
        input.style.borderColor = '#E53935';
        return false;
    }
    if (max && value > max) {
        showError(label + ' must be less than ' + max);
        input.focus();
        input.style.borderColor = '#E53935';
        return false;
    }
    input.style.borderColor = '#43A047';
    return true;
}

/**
 * Show loading state on a button
 */
function setLoading(button, loading = true) {
    if (!button) return;
    
    if (loading) {
        button.disabled = true;
        button._originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    } else {
        button.disabled = false;
        if (button._originalText) {
            button.innerHTML = button._originalText;
        }
    }
}