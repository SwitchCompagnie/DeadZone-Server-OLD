package core.playersummary

import common.LogConfigSocketToClient
import common.Logger
import data.collection.PlayerSummary
import data.db.BigDB
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import java.util.concurrent.ConcurrentHashMap

/**
 * Service for managing PlayerSummary data used by BigDB loadIndexRange queries.
 * Handles bounty leaderboards and player listings.
 */
class PlayerSummaryService(private val db: BigDB) {
    
    private val cache = ConcurrentHashMap<String, PlayerSummary>()
    private val mutex = Mutex()
    
    /**
     * Get or create a player summary
     */
    suspend fun getOrCreate(playerId: String): PlayerSummary = mutex.withLock {
        cache.getOrPut(playerId) {
            // Load from database or create new
            val playerObjects = db.loadPlayerObjects(playerId)
            if (playerObjects != null) {
                // Find the player's main survivor (the one with classId == PLAYER)
                val playerSurvivor = playerObjects.survivors.firstOrNull { 
                    it.classId == "player" || it.id == playerObjects.playerSurvivor
                }
                val level = playerSurvivor?.level ?: 1
                
                PlayerSummary(
                    key = playerId,
                    nickname = playerObjects.nickname ?: "Unknown",
                    level = level,
                    allianceId = playerObjects.allianceId,
                    allianceTag = playerObjects.allianceTag,
                    allianceName = null,  // Would need to load from alliance data
                    bounty = playerObjects.bountyCap,
                    bountyDate = playerObjects.lastLogout ?: 0,
                    lastLogin = playerObjects.lastLogout ?: System.currentTimeMillis(),
                    online = false,
                    onlineTimestamp = 0
                )
            } else {
                PlayerSummary(key = playerId)
            }
        }
    }
    
    /**
     * Update player summary data
     */
    suspend fun update(playerId: String, updater: (PlayerSummary) -> PlayerSummary) = mutex.withLock {
        val current = getOrCreate(playerId)
        val updated = updater(current)
        cache[playerId] = updated
        Logger.info(LogConfigSocketToClient) {
            "Updated PlayerSummary for $playerId: bounty=${updated.bounty}, bountyEarnings=${updated.bountyEarnings}"
        }
    }
    
    /**
     * Update bounty cap for a player
     */
    suspend fun updateBounty(playerId: String, bounty: Int, bountyDate: Long) {
        update(playerId) { summary ->
            summary.copy(bounty = bounty, bountyDate = bountyDate)
        }
    }
    
    /**
     * Update bounty earnings when a player collects a bounty
     */
    suspend fun addBountyEarnings(playerId: String, amount: Int) {
        update(playerId) { summary ->
            summary.copy(
                bountyEarnings = summary.bountyEarnings + amount,
                bountyCollectCount = summary.bountyCollectCount + 1,
                bountyAllTime = summary.bountyAllTime + amount,
                bountyAllTimeCount = summary.bountyAllTimeCount + 1
            )
        }
    }
    
    /**
     * Initialize player summary from PlayerObjects
     */
    suspend fun initializeFromPlayerObjects(playerId: String) {
        val playerObjects = db.loadPlayerObjects(playerId) ?: return
        
        // Find the player's main survivor
        val playerSurvivor = playerObjects.survivors.firstOrNull { 
            it.classId == "player" || it.id == playerObjects.playerSurvivor
        }
        val level = playerSurvivor?.level ?: 1
        
        update(playerId) { _ ->
            PlayerSummary(
                key = playerId,
                nickname = playerObjects.nickname ?: "Unknown",
                level = level,
                allianceId = playerObjects.allianceId,
                allianceTag = playerObjects.allianceTag,
                bounty = playerObjects.bountyCap,
                bountyDate = playerObjects.lastLogout ?: 0,
                lastLogin = playerObjects.lastLogout ?: System.currentTimeMillis(),
                online = true,
                onlineTimestamp = System.currentTimeMillis()
            )
        }
    }
    
    /**
     * Mark player as online
     */
    suspend fun setOnline(playerId: String, online: Boolean) {
        update(playerId) { summary ->
            summary.copy(
                online = online,
                onlineTimestamp = if (online) System.currentTimeMillis() else summary.onlineTimestamp
            )
        }
    }
    
    /**
     * Get all player summaries (for index range queries)
     */
    suspend fun getAll(): List<PlayerSummary> = mutex.withLock {
        cache.values.toList()
    }
    
    /**
     * Query player summaries by index
     */
    suspend fun queryByIndex(
        indexName: String,
        startValue: Any?,
        stopValue: Any?,
        limit: Int
    ): List<PlayerSummary> = mutex.withLock {
        val allSummaries = cache.values.toList()
        
        // Sort based on index
        val sorted = when (indexName) {
            "BountiesByBounty" -> allSummaries.sortedByDescending { it.bounty }
            "BountiesByExpiration", "BountiesByDate" -> allSummaries.sortedBy { it.bountyDate }
            "BountiesByLevel" -> allSummaries.sortedByDescending { it.level }
            "BestBountyHunters" -> allSummaries.sortedByDescending { it.bountyEarnings }
            "BountiesByAllTime" -> allSummaries.sortedByDescending { it.bountyAllTime }
            else -> {
                Logger.warn(LogConfigSocketToClient) { "Unknown index: $indexName" }
                allSummaries
            }
        }
        
        // Apply limit
        sorted.take(if (limit > 0) limit else sorted.size)
    }
}
