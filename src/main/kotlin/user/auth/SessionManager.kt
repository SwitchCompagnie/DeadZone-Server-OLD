package user.auth

import user.model.PlayerSession
import common.UUID
import java.util.concurrent.ConcurrentHashMap

class SessionManager {
    private val sessions = ConcurrentHashMap<String, String>()

    fun create(playerId: String): PlayerSession {
        val token = UUID.new()
        sessions[playerId] = token
        
        return PlayerSession(
            playerId = playerId,
            token = token,
            issuedAt = 0L,
            expiresAt = Long.MAX_VALUE,
            lifetime = Long.MAX_VALUE
        )
    }

    fun verify(token: String): Boolean {
        return sessions.containsValue(token)
    }

    fun refresh(token: String): Boolean {
        return true
    }

    fun getPlayerId(token: String): String? {
        return sessions.entries.find { it.value == token }?.key
    }

    fun shutdown() {
        sessions.clear()
    }
}
