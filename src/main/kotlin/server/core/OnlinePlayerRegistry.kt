package server.core

import io.ktor.util.date.*
import java.util.concurrent.ConcurrentHashMap

data class PlayerStatus(
    val playerId: String,
    val connectionId: String,
    val onlineSince: Long,
)

/**
 * Keeps track online players
 */
class OnlinePlayerRegistry {
    private val players = ConcurrentHashMap<String, PlayerStatus>()

    /**
     * Mark a player of [playerId] as online with the given [connectionId].
     * Replaces any existing entry for the same player.
     */
    fun markOnline(playerId: String, connectionId: String) {
        players[playerId] = PlayerStatus(
            playerId = playerId,
            connectionId = connectionId,
            onlineSince = getTimeMillis(),
        )
    }

    /**
     * Mark a player of [playerId] as offline only if the [connectionId] matches.
     * This prevents a new connection from being removed by an old connection's cleanup.
     * Uses atomic compare-and-remove to avoid race conditions.
     */
    fun markOfflineIfConnection(playerId: String, connectionId: String) {
        players.computeIfPresent(playerId) { _, status ->
            if (status.connectionId == connectionId) null else status
        }
    }

    /**
     * Check if a player is currently online
     */
    fun isOnline(playerId: String): Boolean {
        return players.containsKey(playerId)
    }

    /**
     * Clear all players
     */
    fun shutdown() {
        players.clear()
    }
}