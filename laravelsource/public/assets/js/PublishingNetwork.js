/**

 * PublishingNetwork JavaScript Bridge

 *

 * This file provides the bridge between the Flash/AS3 client and the web authentication system.

 * It handles authentication dialogs and communicates with the Flash client via ExternalInterface.

 *

 * Authentication Flow:

 * 1. Flash client calls PublishingNetwork.dialog("login", dialogArgs, callback)

 * 2. This script retrieves the token from window.token (set by Laravel after successful login)

 * 3. The callback is invoked with {userToken: token} or {error: errorMessage}

 * 4. Flash client uses the userToken to authenticate with the game server

 */

 

(function () {

    'use strict';

 

    /**

     * Sets the user ID for analytics/tracking purposes

     * @param {Object} params - Parameters containing user information

     */

    function setUserId(params) {

        console.log("[PublishingNetwork] setUserId called with:", params);

 

        // If you have analytics integration (e.g., Google Analytics), set it here

        if (window.gtag && params.userId) {

            gtag('config', 'GA_MEASUREMENT_ID', {

                'user_id': params.userId

            });

        }

 

        // PlayerIO uses this for tracking

        if (params.userId && window.ExternalInterface) {

            console.log("[PublishingNetwork] User ID set:", params.userId);

        }

    }

 

    /**

     * Main PublishingNetwork API

     */

    window.PublishingNetwork = {

        /**

         * Show a dialog to the user.

         * Called by the Flash client via ExternalInterface.

         *

         * @param {string} dialogName - The name of the dialog to show (e.g., "login")

         * @param {object} dialogArgs - Arguments passed to the dialog (includes __apitoken__)

         * @param {function} callback - Callback function to invoke with the result

         */

        dialog: function (dialogName, dialogArgs, callback) {

            console.log("[PublishingNetwork] dialog called:", {

                dialogName: dialogName,

                dialogArgs: dialogArgs,

                hasCallback: typeof callback === 'function'

            });

 

            // Validate callback

            if (typeof callback !== 'function') {

                console.error("[PublishingNetwork] Invalid callback provided");

                return;

            }

 

            let result = null;

 

            // Handle different dialog types

            switch (dialogName) {

                case "login": {

                    // Check if we have a valid token from the Laravel authentication

                    const token = window.token || window.gameToken;

 

                    if (!token || typeof token !== 'string' || token.trim().length === 0) {

                        console.error("[PublishingNetwork] No valid token found. User must login first.");

                        result = {

                            error: "Authentication required. Please login to continue."

                        };

                        callback(result);

 

                        // Redirect to login page after a short delay

                        setTimeout(function() {

                            window.location.href = "/login";

                        }, 2000);

                        return;

                    }

 

                    // Verify token format (should be a UUID or similar)

                    if (token.length < 10) {

                        console.error("[PublishingNetwork] Invalid token format");

                        result = {

                            error: "Invalid authentication token. Please re-login."

                        };

                        callback(result);

                        return;

                    }

 

                    console.log("[PublishingNetwork] Login successful, token found");

 

                    // Return the userToken to the Flash client

                    result = {

                        userToken: token

                    };

 

                    // Set user ID for tracking if available

                    if (dialogArgs && dialogArgs.userId) {

                        setUserId({ userId: dialogArgs.userId });

                    }

 

                    callback(result);

                    break;

                }

 

                case "payment":

                case "share":

                case "invite":

                    // These dialog types are not implemented in this version

                    console.warn("[PublishingNetwork] Dialog type not implemented:", dialogName);

                    result = {

                        error: "This feature is not yet available."

                    };

                    callback(result);

                    break;

 

                default:

                    console.error("[PublishingNetwork] Unknown dialog name:", dialogName);

                    result = {

                        error: "Unknown dialog type: " + dialogName

                    };

                    callback(result);

                    break;

            }

        },

 

        /**

         * Get the current authentication status

         * @returns {boolean} True if user is authenticated

         */

        isAuthenticated: function() {

            const token = window.token || window.gameToken;

            return !!(token && typeof token === 'string' && token.trim().length > 0);

        },

 

        /**

         * Get the current token

         * @returns {string|null} The current token or null

         */

        getToken: function() {

            return window.token || window.gameToken || null;

        }

    };

 

    /**

     * Process queued calls that were made before PublishingNetwork was loaded

     * This is used by the Flash client when it tries to call dialog() before this script loads

     */

    if (window.PublishingNetwork_WaitingCalls && Array.isArray(window.PublishingNetwork_WaitingCalls)) {

        console.log("[PublishingNetwork] Processing", window.PublishingNetwork_WaitingCalls.length, "queued calls");

 

        window.PublishingNetwork_WaitingCalls.forEach(function(call) {

            if (!Array.isArray(call) || call.length < 2) {

                console.error("[PublishingNetwork] Invalid queued call format:", call);

                return;

            }

 

            const methodName = call[0];

            const args = call.slice(1);

 

            if (methodName === 'dialog' && window.PublishingNetwork.dialog) {

                // Call format: ['dialog', dialogName, dialogArgs, callback]

                window.PublishingNetwork.dialog.apply(window.PublishingNetwork, args);

            } else {

                console.warn("[PublishingNetwork] Unknown queued method:", methodName);

            }

        });

 

        // Clear the queue

        window.PublishingNetwork_WaitingCalls = [];

    } else {

        // Initialize empty queue if it doesn't exist

        window.PublishingNetwork_WaitingCalls = [];

    }

 

    console.log("[PublishingNetwork] Initialized successfully");

 

    // Log authentication status on load

    if (window.PublishingNetwork.isAuthenticated()) {

        console.log("[PublishingNetwork] User is authenticated");

    } else {

        console.warn("[PublishingNetwork] User is not authenticated - token missing");

    }

 

})();