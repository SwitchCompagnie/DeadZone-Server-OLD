package core.model.game.data

import kotlinx.serialization.Serializable

@Serializable
data class SurvivorLoadoutEntry(
    val weapon: String,  // weapon id
    val gear1: String,  // gear id (passive)
    val gear2: String,  // gear id (active)
    val gear2_qty: Int = 1  // quantity for active gear (grenades, medkits, etc.)
)
