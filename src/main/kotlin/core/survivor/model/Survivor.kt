package core.model.game.data

import core.model.game.data.SurvivorAppearance.Companion.toHumanAppearance
import core.survivor.model.injury.Injury
import core.survivor.model.injury.InjuryList
import dev.deadzone.core.model.game.data.TimerData
import common.UUID
import kotlinx.serialization.Serializable

@Serializable
data class Survivor(
    val id: String = UUID.new(),
    val title: String,
    val firstName: String = "",
    val lastName: String = "DZ",
    val gender: String,
    val portrait: String? = null,
    val classId: String,
    val morale: Map<String, Double> = emptyMap(),
    val injuries: List<Injury> = emptyList(),
    val level: Int = 0,
    val xp: Int = 0,
    val missionId: String? = null,
    val assignmentId: String? = null,
    val reassignTimer: TimerData? = null,
    val appearance: HumanAppearance? = null, // HumanAppearance > SurvivorAppearance
    val scale: Double = 1.22,
    val voice: String,
    val accessories: Map<String, String> = emptyMap(),  // key is parsed to int, string is accessory id
    val maxClothingAccessories: Int = 1
)