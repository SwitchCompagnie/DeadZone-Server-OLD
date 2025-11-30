package api.routes

import context.ServerContext
import common.Logger
import io.ktor.http.*
import io.ktor.server.request.*
import io.ktor.server.response.*
import io.ktor.server.routing.*

fun Route.authRoutes(serverContext: ServerContext) {

    post("/api/login") {
        val data = call.receive<Map<String, String?>>()
        val username = data["username"]
        val password = data["password"]

        if (username.isNullOrBlank() || password.isNullOrBlank()) {
            call.respond(HttpStatusCode.BadRequest, mapOf("reason" to "Missing credentials"))
            return@post
        }

        val session = serverContext.authProvider.login(username, password)
        if (session != null) {
            call.respond(HttpStatusCode.OK, mapOf("playerId" to session.playerId, "token" to session.token))
        } else {
            call.respond(HttpStatusCode.Unauthorized, mapOf("reason" to "Invalid credentials"))
        }
    }

    post("/api/register") {
        val data = call.receive<Map<String, String?>>()
        val username = data["username"]
        val password = data["password"]
        val email = data["email"]
        val countryCode = data["countryCode"]

        if (username.isNullOrBlank() || password.isNullOrBlank()) {
            call.respond(HttpStatusCode.BadRequest, mapOf("reason" to "Missing credentials"))
            return@post
        }

        if (serverContext.authProvider.doesUserExist(username)) {
            call.respond(HttpStatusCode.Conflict, mapOf("reason" to "Username already exists"))
            return@post
        }

        try {
            val session = serverContext.authProvider.register(username, password, email, countryCode)
            call.respond(HttpStatusCode.Created, mapOf("playerId" to session.playerId, "token" to session.token))
        } catch (e: Exception) {
            Logger.error { "Registration failed for $username: ${e.message}" }
            call.respond(HttpStatusCode.InternalServerError, mapOf("reason" to "Registration failed"))
        }
    }

    post("/api/auth") {
        val data = call.receive<Map<String, String?>>()
        val username = data["username"]
        val password = data["password"]
        val email = data["email"]
        val countryCode = data["countryCode"]

        if (username.isNullOrBlank() || password.isNullOrBlank()) {
            call.respond(HttpStatusCode.BadRequest, mapOf("reason" to "Missing credentials"))
            return@post
        }

        val exists = serverContext.authProvider.doesUserExist(username)
        val session = if (exists) {
            serverContext.authProvider.login(username, password)
        } else {
            try {
                serverContext.authProvider.register(username, password, email, countryCode)
            } catch (e: Exception) {
                Logger.error { "Auto-registration failed for $username: ${e.message}" }
                null
            }
        }

        if (session != null) {
            call.respond(HttpStatusCode.OK, mapOf("playerId" to session.playerId, "token" to session.token, "isNew" to (!exists).toString()))
        } else {
            call.respond(HttpStatusCode.Unauthorized, mapOf("reason" to "Invalid credentials"))
        }
    }

    get("/api/userexist") {
        val username = call.parameters["username"]
        if (username.isNullOrBlank()) {
            call.respondText("no", status = HttpStatusCode.BadRequest)
            return@get
        }

        try {
            val exists = serverContext.authProvider.doesUserExist(username)
            call.respondText(if (exists) "yes" else "no")
        } catch (e: Exception) {
            Logger.error { "User check failed for $username: ${e.message}" }
            call.respond(HttpStatusCode.InternalServerError, mapOf("reason" to "Database error"))
        }
    }

    get("/keepalive") {
        val token = call.parameters["token"]
        if (token.isNullOrBlank()) {
            call.respond(HttpStatusCode.BadRequest, "Missing token")
            return@get
        }
        if (serverContext.sessionManager.refresh(token)) {
            call.respond(HttpStatusCode.OK)
        } else {
            call.respond(HttpStatusCode.Unauthorized, "Session expired")
        }
    }

    post("/api/update-user-info") {
        val data = call.receive<Map<String, String?>>()
        val username = data["username"]
        val email = data["email"]
        val countryCode = data["countryCode"]

        if (username.isNullOrBlank()) {
            call.respond(HttpStatusCode.BadRequest, mapOf("reason" to "Missing username"))
            return@post
        }

        try {
            val userDoc = serverContext.playerAccountRepository.getUserDocByUsername(username).getOrNull()
            if (userDoc == null) {
                call.respond(HttpStatusCode.NotFound, mapOf("reason" to "User not found"))
                return@post
            }

            val updatedAccount = userDoc.copy(
                email = email ?: userDoc.email,
                countryCode = countryCode ?: userDoc.countryCode
            )

            serverContext.playerAccountRepository.updatePlayerAccount(userDoc.playerId, updatedAccount).getOrThrow()
            call.respond(HttpStatusCode.OK, mapOf("success" to "true"))
        } catch (e: Exception) {
            Logger.error { "Update failed for $username: ${e.message}" }
            call.respond(HttpStatusCode.InternalServerError, mapOf("reason" to "Update failed"))
        }
    }
}
