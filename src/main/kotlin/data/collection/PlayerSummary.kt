package data.collection

import kotlinx.serialization.Serializable

/**
 * Player summary data for BigDB PlayerSummary table.
 * Used for leaderboards and bounty listings in the game client.
 */
@Serializable
data class PlayerSummary(
    val key: String,  // Player ID
    val nickname: String = "",
    val level: Int = 1,
    val allianceId: String? = null,
    val allianceTag: String? = null,
    val allianceName: String? = null,
    val bounty: Int = 0,  // Current bounty on this player
    val bountyDate: Long = 0,  // Timestamp when bounty was set
    val bountyEarnings: Int = 0,  // Total bounty collected by this player
    val bountyCollectCount: Int = 0,  // Number of bounties collected
    val bountyAllTime: Int = 0,  // Lifetime bounty total
    val bountyAllTimeCount: Int = 0,  // Lifetime bounty count
    val lastLogin: Long = 0,
    val online: Boolean = false,
    val onlineTimestamp: Long = 0
)
