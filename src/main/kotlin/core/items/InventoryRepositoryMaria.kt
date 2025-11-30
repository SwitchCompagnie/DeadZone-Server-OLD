package core.items

import core.items.model.Item
import data.collection.Inventory
import data.db.PlayerAccounts
import data.db.suspendedTransactionResult
import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.selectAll
import org.jetbrains.exposed.sql.update
import common.JSON
import kotlin.io.encoding.Base64
import kotlin.io.encoding.ExperimentalEncodingApi

class InventoryRepositoryMaria(private val database: Database) : InventoryRepository {
    override suspend fun getInventory(playerId: String): Result<Inventory> {
        return database.suspendedTransactionResult {
            PlayerAccounts
                .selectAll()
                .where { PlayerAccounts.playerId eq playerId }
                .singleOrNull()
                ?.let { row ->
                    PlayerAccounts.rowToInventory(playerId, row)
                } ?: throw NoSuchElementException("getInventory: No Inventory found with id=$playerId")
        }
    }

    @OptIn(ExperimentalEncodingApi::class)
    override suspend fun updateInventory(
        playerId: String,
        updatedInventory: List<Item>
    ): Result<Unit> {
        return database.suspendedTransactionResult {
            val currentData = PlayerAccounts
                .selectAll()
                .where { PlayerAccounts.playerId eq playerId }
                .singleOrNull()
                ?.let { row ->
                    PlayerAccounts.rowToInventory(playerId, row)
                } ?: throw NoSuchElementException("updateInventory: No Inventory found with id=$playerId")

            val updatedData = currentData.copy(inventory = updatedInventory)

            val rowsUpdated = PlayerAccounts.update({ PlayerAccounts.playerId eq playerId }) {
                it[inventory] = JSON.encode(updatedData.inventory)
                it[schematics] = Base64.encode(updatedData.schematics)
            }
            if (rowsUpdated == 0) {
                throw IllegalStateException("Failed to update inventory in updateInventory for playerId=$playerId")
            }
        }
    }

    @OptIn(ExperimentalEncodingApi::class)
    override suspend fun updateSchematics(
        playerId: String,
        updatedSchematics: ByteArray
    ): Result<Unit> {
        return database.suspendedTransactionResult {
            val currentData = PlayerAccounts
                .selectAll()
                .where { PlayerAccounts.playerId eq playerId }
                .singleOrNull()
                ?.let { row ->
                    PlayerAccounts.rowToInventory(playerId, row)
                } ?: throw NoSuchElementException("updateSchematics: No Inventory found with id=$playerId")

            val updatedData = currentData.copy(schematics = updatedSchematics)

            val rowsUpdated = PlayerAccounts.update({ PlayerAccounts.playerId eq playerId }) {
                it[inventory] = JSON.encode(updatedData.inventory)
                it[schematics] = Base64.encode(updatedData.schematics)
            }
            if (rowsUpdated == 0) {
                throw IllegalStateException("Failed to update inventory in updateSchematics for playerId=$playerId")
            }
        }
    }
}
