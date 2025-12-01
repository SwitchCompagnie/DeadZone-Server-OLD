package data.collection

import kotlinx.serialization.Serializable

/**
 * Player state data for BigDB PlayerStates table.
 * Used for real-time player status like online/offline, protection, etc.
 */
@Serializable
data class PlayerStates(
    val key: String,  // Player ID
    val online: Boolean = false,
    val onlineTimestamp: Long = 0,
    val underAttack: Boolean = false,
    val protected: Boolean = false,
    val protected_start: Long? = null,
    val protected_length: Int? = null,
    val banned: Boolean = false,
    val raidLockout: Long? = null
)
