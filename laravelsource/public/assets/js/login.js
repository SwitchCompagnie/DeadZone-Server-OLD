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
        if (typeof start !== 'number') {
            start = 0;
        }
        if (start + search.length > this.length) {
            return false;
        } else {
            return this.indexOf(search, start) !== -1;
        }
    };
}

const BASE_URL = 'http://127.0.0.1:8080';
const MAINTENANCE_API = '/api/maintenance/status';
let debounceTimeout;
let usernameTimer;
let TOKEN_REFRESH_INTERVAL = 50 * 60 * 1000;

// Login form validation states
let isLoginUsernameValid = false;
let isLoginPasswordValid = false;

// Register form validation states
let isRegisterUsernameValid = false;
let isRegisterEmailValid = false;
let isRegisterPasswordValid = false;

let isMaintenanceMode = false;

function checkMaintenanceMode() {
    // Fallback for browsers without fetch
    if (typeof fetch === 'undefined') {
        return $.ajax({
            url: MAINTENANCE_API,
            method: 'GET',
            dataType: 'json'
        }).then(function(data) {
            isMaintenanceMode = data.maintenance || false;
            if (isMaintenanceMode) {
                disableLoginDuringMaintenance(data);
            }
            return data;
        }).catch(function(error) {
            console.error("Failed to check maintenance status:", error);
            return { maintenance: false };
        });
    }

    return fetch(MAINTENANCE_API)
        .then(function(response) { return response.ok ? response.json() : Promise.reject(); })
        .then(function(data) {
            isMaintenanceMode = data.maintenance || false;
            if (isMaintenanceMode) {
                disableLoginDuringMaintenance(data);
            }
            return data;
        })
        .catch(function(error) {
            console.error("Failed to check maintenance status:", error);
            return { maintenance: false };
        });
}

function disableLoginDuringMaintenance(data) {
    // Disable login button with enhanced gray styling
    $("#login-button, #register-button")
        .prop("disabled", true)
        .removeClass("bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600")
        .addClass("bg-gray-600 cursor-not-allowed opacity-50 pointer-events-none transition-all duration-300");

    // Disable all social login buttons
    $(".social-btn").each(function() {
        $(this).addClass("opacity-40 cursor-not-allowed pointer-events-none grayscale transition-all duration-300");
    });

    // Disable form inputs
    $("#login-username, #login-password, #register-username, #register-email, #register-password")
        .prop("disabled", true)
        .addClass("opacity-50 cursor-not-allowed bg-gray-900/50");

    // Show maintenance message
    const message = data.message || 'The system is currently under maintenance.';
    const eta = data.eta || '00:00';

    $(".login-info, .register-info").html(
        `<div class="text-orange-500 text-center p-4 bg-orange-900/30 border border-orange-500 rounded-lg mt-4">
            <i class="fa-solid fa-wrench mr-2"></i>
            <strong>Maintenance Mode Active</strong><br>
            ${message}<br>
            <small>ETA: ${eta} local time</small>
        </div>`
    );
}

$(document).ready(function () {
    // Check maintenance mode on page load
    checkMaintenanceMode();

    // Login form validation
    $("#login-username").on("input", function () {
        const value = $(this).val();
        clearTimeout(debounceTimeout);
        $(".login-username-info").text("");
        debounceTimeout = setTimeout(() => {
            validateLoginUsername(value);
        }, 500);
    });

    $("#login-password").on("input", function () {
        const value = $(this).val();
        clearTimeout(debounceTimeout);
        $(".login-password-info").text("");
        debounceTimeout = setTimeout(() => validateLoginPassword(value), 500);
    });

    // Register form validation
    $("#register-username").on("input", function () {
        const value = $(this).val();
        clearTimeout(debounceTimeout);
        $(".register-username-info").text("");
        debounceTimeout = setTimeout(() => {
            if (validateRegisterUsername(value)) {
                clearTimeout(usernameTimer);
                usernameTimer = setTimeout(() => checkUsernameAvailability(value), 500);
            }
        }, 500);
    });

    $("#register-email").on("input", function () {
        const value = $(this).val();
        clearTimeout(debounceTimeout);
        $(".register-email-info").text("");
        debounceTimeout = setTimeout(() => validateRegisterEmail(value), 500);
    });

    $("#register-password").on("input", function () {
        const value = $(this).val();
        clearTimeout(debounceTimeout);
        $(".register-password-info").text("");
        debounceTimeout = setTimeout(() => validateRegisterPassword(value), 500);
    });

    // Login form submit
    $("#login-form").submit(async function (event) {
        event.preventDefault();
        if (isMaintenanceMode) {
            $(".login-info").html(
                `<div class="text-orange-500 text-center">
                    <i class="fa-solid fa-wrench mr-2"></i>
                    Login is disabled during maintenance mode.
                </div>`
            );
            return;
        }
        if (!isLoginUsernameValid || !isLoginPasswordValid) return;
        const btn = $("#login-button");
        const originalContent = btn.html();
        btn.html('<i class="fa-solid fa-circle-notch fa-spin"></i>').prop("disabled", true);
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch($(this).attr('action'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                window.token = data.token;
                window.location.href = data.redirect;
            } else {
                btn.html(originalContent).prop("disabled", false);
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat();
                    $(".login-info").text(errorMessages.join(', ')).css("color", "red");
                } else {
                    $(".login-info").text("Login failed. Please try again.").css("color", "red");
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            btn.html(originalContent).prop("disabled", false);
            $(".login-info").text("Unexpected error during login").css("color", "red");
        }
    });

    // Register form submit
    $("#register-form").submit(async function (event) {
        event.preventDefault();
        if (isMaintenanceMode) {
            $(".register-info").html(
                `<div class="text-orange-500 text-center">
                    <i class="fa-solid fa-wrench mr-2"></i>
                    Registration is disabled during maintenance mode.
                </div>`
            );
            return;
        }
        if (!isRegisterUsernameValid || !isRegisterEmailValid || !isRegisterPasswordValid) return;
        const btn = $("#register-button");
        const originalContent = btn.html();
        btn.html('<i class="fa-solid fa-circle-notch fa-spin"></i>').prop("disabled", true);
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch($(this).attr('action'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                window.token = data.token;
                window.location.href = data.redirect;
            } else {
                btn.html(originalContent).prop("disabled", false);
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat();
                    $(".register-info").text(errorMessages.join(', ')).css("color", "red");
                } else {
                    $(".register-info").text("Registration failed. Please try again.").css("color", "red");
                }
            }
        } catch (error) {
            console.error('Register error:', error);
            btn.html(originalContent).prop("disabled", false);
            $(".register-info").text("Unexpected error during registration").css("color", "red");
        }
    });
});

// Login form validation functions
function validateLoginUsername(username) {
    const usernameRegex = /^[a-zA-Z0-9]+$/;
    const infoDiv = $(".login-username-info");
    if (username.length < 6 || !usernameRegex.test(username)) {
        infoDiv.text("Username must be at least 6 characters. Only letters and digits allowed.").css("color", "red");
        isLoginUsernameValid = false;
        return false;
    }
    infoDiv.text("Username is valid.").css("color", "green");
    isLoginUsernameValid = true;
    return true;
}

function validateLoginPassword(password) {
    const infoDiv = $(".login-password-info");
    if (password.length >= 6) {
        infoDiv.text("Password is valid.").css("color", "green");
        isLoginPasswordValid = true;
    } else {
        infoDiv.text("Password must be at least 6 characters.").css("color", "red");
        isLoginPasswordValid = false;
    }
}

// Register form validation functions
function validateRegisterUsername(username) {
    const usernameRegex = /^[a-zA-Z0-9]+$/;
    const badwords = ["dick"];
    const infoDiv = $(".register-username-info");
    if (username.length < 6 || !usernameRegex.test(username)) {
        infoDiv.text("Username must be at least 6 characters. Only letters and digits allowed.").css("color", "red");
        isRegisterUsernameValid = false;
        return false;
    }
    if (badwords.some(bad => username.toLowerCase().includes(bad))) {
        infoDiv.text("Possible badword detected. Please choose another name.").css("color", "orange");
        isRegisterUsernameValid = false;
        return false;
    }
    infoDiv.text("Checking availability...").css("color", "orange");
    return true;
}

function checkUsernameAvailability(username) {
    fetch(`${BASE_URL}/api/userexist?username=${encodeURIComponent(username)}`)
        .then(async response => {
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.reason || "User check failed");
            }
            return response.text();
        })
        .then(result => {
            const infoDiv = $(".register-username-info");
            if (result === "yes") {
                infoDiv.html('<span style="color:#f59e0b">Username already taken. <a href="#" onclick="document.querySelector(\'[x-data]\')._x_dataStack[0].isLogin = true; return false;" style="color:#ef4444; text-decoration: underline;">Login instead?</a></span>');
                isRegisterUsernameValid = false;
            } else {
                infoDiv.text("Username is available!").css("color", "green");
                isRegisterUsernameValid = true;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            $(".register-username-info").text("Error checking username").css("color", "red");
            isRegisterUsernameValid = false;
        });
}

function validateRegisterEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const infoDiv = $(".register-email-info");

    if (!email || email.length === 0) {
        infoDiv.text("Email is required.").css("color", "red");
        isRegisterEmailValid = false;
        return false;
    }

    if (!emailRegex.test(email)) {
        infoDiv.text("Please enter a valid email address.").css("color", "red");
        isRegisterEmailValid = false;
        return false;
    }

    infoDiv.text("Email is valid.").css("color", "green");
    isRegisterEmailValid = true;
    return true;
}

function validateRegisterPassword(password) {
    const infoDiv = $(".register-password-info");
    if (password.length >= 6) {
        infoDiv.text("Password is valid.").css("color", "green");
        isRegisterPasswordValid = true;
    } else {
        infoDiv.text("Password must be at least 6 characters.").css("color", "red");
        isRegisterPasswordValid = false;
    }
}