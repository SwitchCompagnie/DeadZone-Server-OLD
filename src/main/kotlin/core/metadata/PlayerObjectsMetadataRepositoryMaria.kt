package core.metadata

import data.collection.PlayerObjects
import data.db.PlayerAccounts
import data.db.suspendedTransactionResult
import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.selectAll
import org.jetbrains.exposed.sql.update

class PlayerObjectsMetadataRepositoryMaria(private val database: Database) : PlayerObjectsMetadataRepository {
    companion object {
        const val MAX_CHAT_CONTACTS = 50
        const val MAX_CHAT_BLOCKS = 50
    }
    private suspend fun <T> getPlayerObjectsData(playerId: String, transform: (PlayerObjects) -> T): Result<T> {
        return database.suspendedTransactionResult {
            PlayerAccounts
                .selectAll()
                .where { PlayerAccounts.playerId eq playerId }
                .singleOrNull()
                ?.let { row ->
                    val playerObjects = PlayerAccounts.rowToPlayerObjects(playerId, row)
                    transform(playerObjects)
                } ?: throw NoSuchElementException("getPlayerObjectsData: No PlayerObjects found with id=$playerId")
        }
    }

    private suspend fun updatePlayerObjectsData(
        playerId: String,
        updateAction: (PlayerObjects) -> PlayerObjects
    ): Result<Unit> {
        return database.suspendedTransactionResult {
            val currentRow = PlayerAccounts
                .selectAll()
                .where { PlayerAccounts.playerId eq playerId }
                .singleOrNull()
                ?: throw NoSuchElementException("updatePlayerObjectsData: No PlayerObjects found with id=$playerId")

            val currentData = PlayerAccounts.rowToPlayerObjects(playerId, currentRow)
            val updatedData = updateAction(currentData)

            val rowsUpdated = PlayerAccounts.update({ PlayerAccounts.playerId eq playerId }) {
                PlayerAccounts.setPlayerObjectsColumns(it, updatedData)
            }
            if (rowsUpdated == 0) {
                throw IllegalStateException("Failed to update player objects data for playerId=$playerId")
            }
        }
    }

    override suspend fun getPlayerFlags(playerId: String): Result<ByteArray> {
        return getPlayerObjectsData(playerId) { it.flags }
    }

    override suspend fun updatePlayerFlags(playerId: String, flags: ByteArray): Result<Unit> {
        return updatePlayerObjectsData(playerId) { it.copy(flags = flags) }
    }

    override suspend fun getPlayerNickname(playerId: String): Result<String?> {
        return getPlayerObjectsData(playerId) { it.nickname }
    }

    override suspend fun updatePlayerNickname(playerId: String, nickname: String): Result<Unit> {
        return updatePlayerObjectsData(playerId) { it.copy(nickname = nickname) }
    }

    override suspend fun clearNotifications(playerId: String): Result<Unit> {
        // Notifications not stored in PlayerObjects - no-op
        return Result.success(Unit)
    }

    // Chat contacts and blocks implementation - not stored in PlayerObjects, return stubs
    override suspend fun getChatContacts(playerId: String): Result<List<String>> {
        return Result.success(emptyList())
    }

    override suspend fun addChatContact(playerId: String, nickname: String): Result<Boolean> {
        return Result.success(false)
    }

    override suspend fun removeChatContact(playerId: String, nickname: String): Result<Unit> {
        return Result.success(Unit)
    }

    override suspend fun removeAllChatContacts(playerId: String): Result<Unit> {
        return Result.success(Unit)
    }

    override suspend fun getChatBlocks(playerId: String): Result<List<String>> {
        return Result.success(emptyList())
    }

    override suspend fun addChatBlock(playerId: String, nickname: String): Result<Boolean> {
        return Result.success(false)
    }

    override suspend fun removeChatBlock(playerId: String, nickname: String): Result<Unit> {
        return Result.success(Unit)
    }

    override suspend fun removeAllChatBlocks(playerId: String): Result<Unit> {
        return Result.success(Unit)
    }
}
