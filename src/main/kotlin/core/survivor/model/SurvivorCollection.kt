package core.model.game.data

import kotlinx.serialization.Serializable
import core.model.game.data.Survivor

@Serializable
data class SurvivorCollection(
    val list: List<Survivor> = listOf()
)
