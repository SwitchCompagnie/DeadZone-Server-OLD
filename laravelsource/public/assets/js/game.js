var flashVersion = "11.3.300.271";

var messages = [];

var unloadMessage = "";

var mt = false;

var mtPST = "00:00";

const BASE_URL = 'http://127.0.0.1:8080';

const STATUS_API = BASE_URL + '/api/status';

const STATUS_URL = 'https://status.deadzonegame.net';

const MAINTENANCE_API = '/api/maintenance/status';

const SESSION_REFRESH_INTERVAL = 50 * 60 * 1000; // 50 minutes

const SESSION_REFRESH_RETRY_DELAY = 5 * 60 * 1000; // 5 minutes retry on failure

 

// Session refresh state

let sessionRefreshInterval = null;

let sessionRefreshRetryTimeout = null;

let consecutiveRefreshFailures = 0;

const MAX_CONSECUTIVE_FAILURES = 3;

 

/**

 * Check maintenance mode status

 */

function checkMaintenanceMode() {

    return fetch(MAINTENANCE_API)

        .then(response => {

            if (!response.ok) {

                throw new Error('Maintenance API returned ' + response.status);

            }

            return response.json();

        })

        .then(data => {

            mt = data.maintenance || false;

            mtPST = data.eta || "00:00";

            return data;

        })

        .catch(error => {

            console.error("[Game] Failed to check maintenance status:", error);

            // Don't block game start on maintenance check failure

            return { maintenance: false, eta: "00:00" };

        });

}

 

/**

 * Update server status indicator

 */

function updateServerStatus() {

    const statusElement = $(".server-status");

    statusElement.html(`<a href="${STATUS_URL}" target="_blank">Server Status: Checking...</a>`);

 

    fetch(STATUS_API)

        .then(response => {

            if (!response.ok) throw new Error('Status API error');

            return response.json();

        })

        .then(data => {

            const status = data.status && data.status.toLowerCase() === "online" ? "Online" : "Offline";

            const color = status === "Online" ? "text-green-500" : "text-red-500";

            statusElement.html(`<a href="${STATUS_URL}" target="_blank" class="${color}">Server Status: ${status}</a>`);

        })

        .catch(error => {

            console.error("[Game] Failed to fetch server status:", error);

            statusElement.html(`<a href="${STATUS_URL}" target="_blank" class="text-red-500">Server Status: Offline</a>`);

        });

}

 

/**

 * Validate token format

 * @param {string} token

 * @returns {boolean}

 */

function validateToken(token) {

    if (!token || typeof token !== 'string') {

        console.error("[Game] Invalid token: not a string");

        return false;

    }

 

    const trimmedToken = token.trim();

    if (trimmedToken.length === 0) {

        console.error("[Game] Invalid token: empty string");

        return false;

    }

 

    // Token should be at least 20 characters (UUID is 36)

    if (trimmedToken.length < 20) {

        console.error("[Game] Invalid token: too short (length: " + trimmedToken.length + ")");

        return false;

    }

 

    // Optional: Validate UUID format (8-4-4-4-12)

    const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

    if (!uuidRegex.test(trimmedToken)) {

        console.warn("[Game] Token does not match UUID format (this may be ok)");

    }

 

    return true;

}

 

/**

 * Verify the session token is valid before starting the game

 * @returns {Promise<boolean>} true if session is valid, false otherwise

 */

function verifySession() {

    if (!window.token) {

        console.error("[Game] Cannot verify session: no token");

        return Promise.resolve(false);

    }

 

    console.log("[Game] Verifying session...");

 

    return fetch(`${BASE_URL}/keepalive?token=${encodeURIComponent(window.token)}`, {

        method: 'GET',

        headers: {

            'X-Requested-With': 'XMLHttpRequest'

        }

    })

        .then(response => {

            if (response.status === 401) {

                console.error("[Game] Session invalid (401)");

                return false;

            }

            if (!response.ok) {

                console.error("[Game] Session verification failed: " + response.status);

                return false;

            }

            console.log("[Game] Session verified successfully");

            return true;

        })

        .catch(error => {

            console.error("[Game] Session verification request failed:", error);

            return false;

        });

}

 

/**

 * Refresh the session to prevent expiration

 */

function refreshSession() {

    if (!window.token) {

        console.error("[Game] Cannot refresh session: no token");

        stopSessionRefresh();

        return;

    }

 

    console.log("[Game] Refreshing session...");

 

    fetch(`${BASE_URL}/keepalive?token=${encodeURIComponent(window.token)}`, {

        method: 'GET',

        headers: {

            'X-Requested-With': 'XMLHttpRequest'

        }

    })

        .then(response => {

            if (response.status === 401) {

                console.error("[Game] Session expired (401)");

                $(".server-status")

                    .text("Session expired, please re-login")

                    .css("color", "red");

 

                stopSessionRefresh();

 

                // Redirect to login after 3 seconds

                setTimeout(function() {

                    window.location.href = "/login?reason=session_expired";

                }, 3000);

 

                return;

            }

 

            if (!response.ok) {

                throw new Error('Keepalive returned ' + response.status);

            }

 

            // Reset failure counter on success

            consecutiveRefreshFailures = 0;

            console.log("[Game] Session refreshed successfully");

        })

        .catch(error => {

            console.error("[Game] Keepalive request failed:", error);

            consecutiveRefreshFailures++;

 

            if (consecutiveRefreshFailures >= MAX_CONSECUTIVE_FAILURES) {

                console.error("[Game] Too many consecutive refresh failures, stopping refresh");

                $(".server-status")

                    .text("Connection lost, please refresh the page")

                    .css("color", "red");

                stopSessionRefresh();

            } else {

                // Retry sooner on failure

                console.log(`[Game] Will retry in ${SESSION_REFRESH_RETRY_DELAY / 1000}s (failure ${consecutiveRefreshFailures}/${MAX_CONSECUTIVE_FAILURES})`);

                sessionRefreshRetryTimeout = setTimeout(refreshSession, SESSION_REFRESH_RETRY_DELAY);

            }

        });

}

 

/**

 * Start automatic session refresh

 */

function startSessionRefresh() {

    console.log("[Game] Starting automatic session refresh");

    consecutiveRefreshFailures = 0;

 

    // Clear any existing intervals/timeouts

    stopSessionRefresh();

 

    // Start the refresh interval

    sessionRefreshInterval = setInterval(refreshSession, SESSION_REFRESH_INTERVAL);

 

    // Also do an immediate refresh to verify the session

    refreshSession();

}

 

/**

 * Stop automatic session refresh

 */

function stopSessionRefresh() {

    if (sessionRefreshInterval) {

        clearInterval(sessionRefreshInterval);

        sessionRefreshInterval = null;

    }

    if (sessionRefreshRetryTimeout) {

        clearTimeout(sessionRefreshRetryTimeout);

        sessionRefreshRetryTimeout = null;

    }

}

 

/**

 * Show the game screen (hide loading, show game container)

 */

function showGameScreen() {

    var a = swfobject.getFlashPlayerVersion();

    $("#noflash-reqVersion").html(flashVersion);

    $("#noflash-currentVersion").html(a.major + "." + a.minor + "." + a.release);

 

    if (screen.availWidth <= 1250) {

        $("#nav").css("left", "220px");

    }

}

 

/**

 * Start the game with the provided token

 * @param {string} token - Authentication token

 */

function startGame(token) {

    if (!validateToken(token)) {

        console.error("[Game] Cannot start game with invalid token");

        showError("Authentication Error", "Invalid authentication token. Please <a href='/login'>re-login</a>.");

        return;

    }

 

    console.log("[Game] Starting game with token:", token.substring(0, 8) + "...");

 

    $("#loading").css("display", "block");

 

    const flashVars = {

        path: "/game/",

        service: "pio",

        affiliate: getParameterByName("a"),

        useSSL: 0,

        gameId: "laststand-deadzone",

        connectionId: "public",

        clientAPI: "javascript",

        playerInsightSegments: [],

        playCodes: [],

        userToken: token,

        clientInfo: {

            platform: navigator.platform,

            userAgent: navigator.userAgent

        }

    };

 

    const params = {

        allowScriptAccess: "always",

        allowFullScreen: "true",

        allowFullScreenInteractive: "true",

        allowNetworking: "all",

        menu: "false",

        scale: "noScale",

        salign: "tl",

        wmode: "direct",

        bgColor: "#000000"

    };

 

    const attributes = {

        id: "game",

        name: "game"

    };

 

    $("#game-wrapper").height("0px");

    embedSWF("/game/preloader.swf", flashVars, params, attributes);

}

 

/**

 * Embed the SWF file

 */

function embedSWF(swfURL, flashVars, params, attributes) {

    swfobject.embedSWF(

        BASE_URL + swfURL,

        "game-container",

        "100%",

        "100%",

        flashVersion,

        "swf/expressinstall.swf",

        flashVars,

        params,

        attributes,

        function(e) {

            if (!e.success) {

                console.error("[Game] Failed to embed SWF");

                showNoFlash();

            } else {

                console.log("[Game] SWF embedded successfully");

                setMouseWheelState(false);

            }

        }

    );

}

 

/**

 * Show "No Flash" error

 */

function showNoFlash() {

    $("#loading").remove();

    $("#noflash").css("display", "block");

    $("#game-wrapper").height("100%");

    $("#user-id").html("");

}

 

/**

 * Show maintenance screen

 */

function showMaintenanceScreen() {

    var maintenanceMessage = "The Last Stand: Dead Zone is down for scheduled maintenance. ETA " + mtPST + " local time.";

    addMessage("maintenance", maintenanceMessage);

    showError(

        "Scheduled Maintenance",

        "The Last Stand: Dead Zone is down for scheduled maintenance.<br/><strong>ETA " + mtPST + " local time</strong><br/><br/>Please check back later."

    );

}

 

/**

 * Show generic error

 */

function showError(title, message) {

    $("#loading").remove();

    $("#generic-error").css("display", "block");

    $("#generic-error").html("<p><h2>" + title + "</h2></p><p>" + message + "</p>");

    $("#user-id").html("");

}

 

/**

 * Kill the game (show timeout message)

 */

function killGame() {

    $("#game, #game-container, #loading").remove();

    $("#content").prepend(

        "<div id='messagebox'>" +

        "<div class='header'>Are you there?</div>" +

        "<div class='msg'>You've left your compound unattended for some time. Are you still playing?</div>" +

        "<div class='btn' onclick='refresh()'>BACK TO THE DEAD ZONE</div>" +

        "</div>"

    );

}

 

/**

 * Called when preloader is ready

 */

function onPreloaderReady() {

    console.log("[Game] Preloader ready");

    $("#loading").remove();

    $("#game-wrapper").height("100%");

}

 

/**

 * Handle Flash hide event

 */

function onFlashHide(c) {

    if (c.state == "opened") {

        var b = document.getElementById("game").getScreenshot();

        if (b != null) {

            $("#content").append(

                "<img id='screenshot' style='position:absolute; top:120px; width:960px; height:804px;' " +

                "src='data:image/jpeg;base64," + b + "'/>"

            );

        }

    } else {

        $("#screenshot").remove();

    }

}

 

/**

 * Refresh the page

 */

function refresh() {

    location.reload();

}

 

/**

 * Add a message to the header

 */

function addMessage(h, f, g, b) {

    var e = $('<div class="header-message-bar"></div>');

    e.data("id", h);

 

    if (g) {

        e.append($('<div class="close"></div>').click(() =>

            e.stop(true, true).animate({ height: "toggle" }, 250)

        ));

    }

 

    if (b) {

        e.append($('<div class="loader"></div>'));

    }

 

    var d = $('<div class="header-message">' + f + "</div>");

    e.append(d);

    $("#warning-container").append(e);

    e.height("0px").animate({ height: "30px" }, 250);

    messages.push(e);

}

 

/**

 * Remove a message from the header

 */

function removeMessage(c) {

    for (var a = messages.length - 1; a >= 0; a--) {

        if (messages[a].data("id") == c) {

            messages[a].stop(true).animate({ height: "toggle" }, 250);

            messages.splice(a, 1);

        }

    }

}

 

/**

 * Update navigation class

 */

function updateNavClass(a) {

    $("#nav-ul")[0].className = a;

}

 

/**

 * Redeem code dialog state

 */

var requestCodeRedeemInterval;

var waitingForCodeRedeem = false;

 

function openRedeemCodeDialogue() {

    updateNavClass("code");

    if (mt || waitingForCodeRedeem) {

        return;

    }

 

    var a = function () {

        try {

            document.getElementById("game").openRedeemCode();

            removeMessage("openingCodeRedeem");

            updateNavClass(null);

            return true;

        } catch (b) {}

        return false;

    };

 

    if (!a()) {

        addMessage(

            "openingCodeRedeem",

            "Please wait while the game loads...",

            false,

            true

        );

        waitingForCodeRedeem = true;

        requestCodeRedeemInterval = setInterval(function () {

            if (a()) {

                waitingForCodeRedeem = false;

                clearInterval(requestCodeRedeemInterval);

            }

        }, 1000);

    }

}

 

/**

 * Get More dialog state

 */

var requestGetMoreInterval;

var waitingForGetMore = false;

 

function openGetMoreDialogue() {

    updateNavClass("get-more");

    if (mt || waitingForGetMore) {

        return;

    }

 

    var a = function () {

        try {

            if (document.getElementById("game").openGetMore()) {

                removeMessage("openingFuel");

                updateNavClass(null);

                return true;

            }

        } catch (b) {}

        return false;

    };

 

    if (!a()) {

        addMessage(

            "openingFuel",

            "Opening Fuel Store, please wait while the game loads...",

            false,

            true

        );

        waitingForGetMore = true;

        requestGetMoreInterval = setInterval(function () {

            if (a()) {

                waitingForGetMore = false;

                clearInterval(requestGetMoreInterval);

            }

        }, 1000);

    }

}

 

/**

 * Set mouse wheel state

 */

function setMouseWheelState(a) {

    if (a) {

        document.onmousewheel = null;

        if (document.addEventListener) {

            document.removeEventListener("DOMMouseScroll", preventWheel, false);

        }

    } else {

        document.onmousewheel = preventWheel;

        if (document.addEventListener) {

            document.addEventListener("DOMMouseScroll", preventWheel, false);

        }

    }

}

 

/**

 * Prevent wheel scrolling

 */

function preventWheel(a) {

    if (!a) a = window.event;

    if (a.preventDefault) a.preventDefault();

    else a.returnValue = false;

}

 

/**

 * Get URL parameter by name

 */

function getParameterByName(b) {

    b = b.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

    var a = "[\\?&]" + b + "=([^&#]*)";

    var d = new RegExp(a);

    var c = d.exec(window.location.search);

    return c == null ? "" : decodeURIComponent(c[1].replace(/\+/g, " "));

}

 

/**

 * Initialize the game on page load

 */

$(document).ready(function () {

    console.log("[Game] Page loaded, initializing...");

 

    // Update server status

    updateServerStatus();

    setInterval(updateServerStatus, 60000);

 

    // Auto-hide flash messages

    setTimeout(function() {

        $('.flash-message').fadeOut('slow');

    }, 5000);

 

    // Get token from backend (set in game.blade.php) or URL param (fallback)

    window.token = window.gameToken || new URLSearchParams(window.location.search).get("token");

 

    if (!window.token) {

        console.error("[Game] No token found. Redirecting to login...");

        showError(

            "Authentication Required",

            "You must be logged in to play. Redirecting to login page..."

        );

        setTimeout(function() {

            window.location.href = "/login?reason=no_token";

        }, 2000);

        return;

    }

 

    // Validate token format

    if (!validateToken(window.token)) {

        console.error("[Game] Invalid token format. Redirecting to login...");

        showError(

            "Invalid Session",

            "Your session is invalid. Please login again..."

        );

        setTimeout(function() {

            window.location.href = "/login?reason=invalid_token";

        }, 2000);

        return;

    }

 

    console.log("[Game] Token validated successfully");

 

    // Verify session with server before starting the game

    verifySession().then(isValid => {

        if (!isValid) {

            console.error("[Game] Session verification failed. Redirecting to login...");

            showError(

                "Session Expired",

                "Your session has expired. Please login again..."

            );

            setTimeout(function() {

                window.location.href = "/login?reason=session_expired";

            }, 2000);

            return;

        }

 

        // Check maintenance mode before starting the game

        checkMaintenanceMode().then(maintenanceData => {

            if (mt) {

                console.log("[Game] Maintenance mode active");

                showMaintenanceScreen();

            } else {

                console.log("[Game] Starting game...");

                startGame(window.token);

                startSessionRefresh();

                showGameScreen();

            }

        });

    });

 

    // Handle page unload

    window.addEventListener('beforeunload', function() {

        stopSessionRefresh();

    });

});