package data.collection

import core.data.GameDefinition
import core.items.ItemFactory
import core.items.model.Item
import core.items.model.combineItems
import kotlinx.serialization.Serializable

/**
 * Inventory table
 */
@Serializable
data class Inventory(
    val playerId: String, // reference to UserDocument
    val inventory: List<Item> = emptyList(),
    val schematics: ByteArray = byteArrayOf(),  // see line 643 of Inventory.as
) {
    companion object {
        fun newgame(pid: String): Inventory {
            // Start with basic items only - no cheat weapons
            val items = listOf(
                Item(type = "pocketKnife"),
                Item(type = "lawson22")
            )
            return Inventory(
                playerId = pid,
                inventory = items,
                schematics = byteArrayOf()
            )
        }
    }

    override fun equals(other: Any?): Boolean {
        if (this === other) return true
        if (javaClass != other?.javaClass) return false

        other as Inventory

        if (inventory != other.inventory) return false
        if (!schematics.contentEquals(other.schematics)) return false

        return true
    }

    override fun hashCode(): Int {
        var result = inventory.hashCode()
        result = 31 * result + schematics.contentHashCode()
        return result
    }
}

/**
 * Combine two inventory semantically (according to the game definition).
 */
fun Inventory.combineItems(other: Inventory, gameDefinition: GameDefinition): Inventory {
    return this.copy(inventory = this.inventory.combineItems(other.inventory, gameDefinition))
}
