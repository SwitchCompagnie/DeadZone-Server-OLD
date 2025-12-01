// Polyfill for older browsers
if (!Array.prototype.some) {
    Array.prototype.some = function(fn) {
        for (var i = 0; i < this.length; i++) {
            if (fn(this[i])) return true;
        }
        return false;
    };
}

if (!String.prototype.includes) {
    String.prototype.includes = function(search, start) {
        if (typeof start !== 'number') start = 0;
        return start + search.length <= this.length && this.indexOf(search, start) !== -1;
    };
}

// Configuration
const BASE_URL = window.API_BASE_URL || 'https://serverlet.deadzonegame.net';
const MAINTENANCE_API = '/api/maintenance/status';
const DEBOUNCE_DELAY = 500;
const USERNAME_REGEX = /^[a-zA-Z0-9]+$/;
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const MIN_LENGTH = 6;
const BADWORDS = ["dick"];

// Validation state
const validationState = {
    login: { username: false, password: false },
    register: { username: false, email: false, password: false }
};
let isMaintenanceMode = false;

// Debounce timers (separate per field to prevent conflicts)
const timers = {};

/**
 * Debounce utility for field validation
 */
function debounce(key, fn, delay = DEBOUNCE_DELAY) {
    clearTimeout(timers[key]);
    timers[key] = setTimeout(fn, delay);
}

/**
 * Show validation message in info div
 */
function showFieldMessage(selector, message, color) {
    $(selector).text(message).css("color", color);
}

/**
 * Check maintenance mode status
 */
function checkMaintenanceMode() {
    const handleResponse = (data) => {
        isMaintenanceMode = data.maintenance || false;
        if (isMaintenanceMode) disableLoginDuringMaintenance(data);
        return data;
    };

    const handleError = (error) => {
        console.error("Failed to check maintenance status:", error);
        return { maintenance: false };
    };

    if (typeof fetch === 'undefined') {
        return $.ajax({ url: MAINTENANCE_API, method: 'GET', dataType: 'json' })
            .then(handleResponse).catch(handleError);
    }

    return fetch(MAINTENANCE_API)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(handleResponse).catch(handleError);
}

/**
 * Disable UI during maintenance
 */
function disableLoginDuringMaintenance(data) {
    $("#login-button, #register-button")
        .prop("disabled", true)
        .removeClass("bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600")
        .addClass("bg-gray-600 cursor-not-allowed opacity-50 pointer-events-none");

    $(".social-btn").addClass("opacity-40 cursor-not-allowed pointer-events-none grayscale");

    $("#login-username, #login-password, #register-username, #register-email, #register-password")
        .prop("disabled", true).addClass("opacity-50 cursor-not-allowed bg-gray-900/50");

    const message = data.message || 'The system is currently under maintenance.';
    const eta = data.eta || '00:00';

    $(".login-info, .register-info").html(
        `<div class="text-orange-500 text-center p-4 bg-orange-900/30 border border-orange-500 rounded-lg mt-4">
            <i class="fa-solid fa-wrench mr-2"></i><strong>Maintenance Mode Active</strong><br>
            ${message}<br><small>ETA: ${eta} local time</small>
        </div>`
    );
}

/**
 * Validate username format (shared logic)
 */
function validateUsernameFormat(username) {
    if (username.length < MIN_LENGTH || !USERNAME_REGEX.test(username)) {
        return { valid: false, message: "Username must be at least 6 characters. Only letters and digits allowed." };
    }
    return { valid: true };
}

/**
 * Validate password (shared logic)
 */
function validatePassword(password, infoSelector, stateKey) {
    const valid = password.length >= MIN_LENGTH;
    validationState[stateKey].password = valid;
    showFieldMessage(infoSelector, 
        valid ? "Password is valid." : "Password must be at least 6 characters.",
        valid ? "green" : "red"
    );
}

/**
 * Handle form submission
 */
async function handleFormSubmit(form, formType, buttonSelector, infoSelector) {
    if (isMaintenanceMode) {
        $(infoSelector).html(
            `<div class="text-orange-500 text-center">
                <i class="fa-solid fa-wrench mr-2"></i>
                ${formType === 'login' ? 'Login' : 'Registration'} is disabled during maintenance mode.
            </div>`
        );
        return;
    }

    const state = validationState[formType];
    const allValid = formType === 'login' 
        ? state.username && state.password
        : state.username && state.email && state.password;

    if (!allValid) return;

    const btn = $(buttonSelector);
    const originalContent = btn.html();
    btn.html('<i class="fa-solid fa-circle-notch fa-spin"></i>').prop("disabled", true);

    try {
        const response = await fetch($(form).attr('action'), {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            window.token = data.token;
            window.location.href = data.redirect;
        } else {
            btn.html(originalContent).prop("disabled", false);
            const errorMsg = data.errors 
                ? Object.values(data.errors).flat().join(', ')
                : `${formType === 'login' ? 'Login' : 'Registration'} failed. Please try again.`;
            showFieldMessage(infoSelector, errorMsg, "red");
        }
    } catch (error) {
        console.error(`${formType} error:`, error);
        btn.html(originalContent).prop("disabled", false);
        showFieldMessage(infoSelector, `Unexpected error during ${formType}`, "red");
    }
}

// Login validation
function validateLoginUsername(username) {
    const result = validateUsernameFormat(username);
    validationState.login.username = result.valid;
    showFieldMessage(".login-username-info",
        result.valid ? "Username is valid." : result.message,
        result.valid ? "green" : "red"
    );
    return result.valid;
}

// Register validation
function validateRegisterUsername(username) {
    const result = validateUsernameFormat(username);
    if (!result.valid) {
        validationState.register.username = false;
        showFieldMessage(".register-username-info", result.message, "red");
        return false;
    }

    if (BADWORDS.some(bad => username.toLowerCase().includes(bad))) {
        validationState.register.username = false;
        showFieldMessage(".register-username-info", "Possible badword detected. Please choose another name.", "orange");
        return false;
    }

    showFieldMessage(".register-username-info", "Checking availability...", "orange");
    return true;
}

function checkUsernameAvailability(username) {
    fetch(`${BASE_URL}/api/userexist?username=${encodeURIComponent(username)}`)
        .then(response => {
            // Handle the response - API returns plain text "yes" or "no"
            return response.text().then(text => {
                if (!response.ok) {
                    // Try to parse as JSON for error details
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.reason || 'Server error');
                    } catch (parseError) {
                        // Not JSON, use status code based message
                        throw new Error(response.status === 400 ? 'Invalid request' : 'Server error');
                    }
                }
                return text;
            });
        })
        .then(result => {
            if (result === "yes") {
                $(".register-username-info").html(
                    '<span style="color:#f59e0b">Username already taken. ' +
                    '<a href="#" onclick="document.querySelector(\'[x-data]\')._x_dataStack[0].isLogin = true; return false;" ' +
                    'style="color:#ef4444; text-decoration: underline;">Login instead?</a></span>'
                );
                validationState.register.username = false;
            } else {
                showFieldMessage(".register-username-info", "Username is available!", "green");
                validationState.register.username = true;
            }
        })
        .catch(error => {
            console.error("Username check error:", error.message);
            showFieldMessage(".register-username-info", "Error checking username: " + error.message, "red");
            validationState.register.username = false;
        });
}

function validateRegisterEmail(email) {
    const infoDiv = $(".register-email-info");

    if (!email || email.length === 0) {
        validationState.register.email = false;
        showFieldMessage(".register-email-info", "Email is required.", "red");
        return false;
    }

    if (!EMAIL_REGEX.test(email)) {
        validationState.register.email = false;
        showFieldMessage(".register-email-info", "Please enter a valid email address.", "red");
        return false;
    }

    validationState.register.email = true;
    showFieldMessage(".register-email-info", "Email is valid.", "green");
    return true;
}

// Initialize on document ready
$(document).ready(function() {
    checkMaintenanceMode();

    // Login form events
    $("#login-username").on("input", function() {
        const value = $(this).val();
        $(".login-username-info").text("");
        debounce('loginUsername', () => validateLoginUsername(value));
    });

    $("#login-password").on("input", function() {
        const value = $(this).val();
        $(".login-password-info").text("");
        debounce('loginPassword', () => validatePassword(value, ".login-password-info", "login"));
    });

    // Register form events
    $("#register-username").on("input", function() {
        const value = $(this).val();
        $(".register-username-info").text("");
        debounce('registerUsername', () => {
            if (validateRegisterUsername(value)) {
                debounce('usernameCheck', () => checkUsernameAvailability(value));
            }
        });
    });

    $("#register-email").on("input", function() {
        const value = $(this).val();
        $(".register-email-info").text("");
        debounce('registerEmail', () => validateRegisterEmail(value));
    });

    $("#register-password").on("input", function() {
        const value = $(this).val();
        $(".register-password-info").text("");
        debounce('registerPassword', () => validatePassword(value, ".register-password-info", "register"));
    });

    // Form submissions
    $("#login-form").submit(function(event) {
        event.preventDefault();
        handleFormSubmit(this, 'login', "#login-button", ".login-info");
    });

    $("#register-form").submit(function(event) {
        event.preventDefault();
        handleFormSubmit(this, 'register', "#register-button", ".register-info");
    });
});