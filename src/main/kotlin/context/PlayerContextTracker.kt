package context

import core.compound.CompoundRepositoryMaria
import core.compound.CompoundService
import core.items.BatchRecycleJobRepositoryMaria
import core.items.BatchRecycleJobService
import core.items.InventoryRepositoryMaria
import core.items.InventoryService
import core.metadata.PlayerObjectsMetadataRepositoryMaria
import core.metadata.PlayerObjectsMetadataService
import core.survivor.SurvivorRepositoryMaria
import core.survivor.SurvivorService
import data.db.BigDB
import data.db.BigDBMariaImpl
import io.ktor.util.date.*
import server.core.Connection
import java.util.concurrent.ConcurrentHashMap
import common.Logger

class PlayerContextTracker {
    val players = ConcurrentHashMap<String, PlayerContext>()
    
    suspend fun createContext(playerId: String, connection: Connection, db: BigDB) {
        // Clean up existing context if player is reconnecting
        val existingContext = players.remove(playerId)
        if (existingContext != null) {
            Logger.info { "Cleaning up existing context for reconnecting player: $playerId" }
            try {
                // Only shutdown the old connection, do NOT call leaveAllRooms
                // because the new connection has already joined the room in JoinHandler
                // before createContext is called. Room.addPlayer replaces the old connection
                // with the new one, so the player is still in the room with the new connection.
                existingContext.connection.shutdown()
            } catch (e: Exception) {
                Logger.warn { "Error shutting down old connection for $playerId: ${e.message}" }
            }
        }

        Logger.info { "Creating context for playerId=$playerId" }
        
        val playerAccount = db.loadPlayerAccount(playerId)
        if (playerAccount == null) {
            Logger.error { "Cannot create context: PlayerAccount not found for playerId=$playerId" }
            throw IllegalStateException("PlayerAccount not found for playerId=$playerId")
        }

        val services = try {
            initializeServices(playerId, db)
        } catch (e: Exception) {
            Logger.error { "Failed to initialize services for playerId=$playerId: ${e.message}" }
            throw e
        }
        
        val context = PlayerContext(
            playerId = playerId,
            connection = connection,
            onlineSince = getTimeMillis(),
            playerAccount = playerAccount,
            services = services
        )
        players[playerId] = context
        Logger.info { "Context created successfully for playerId=$playerId" }
    }
    
    private suspend fun initializeServices(playerId: String, db: BigDB): PlayerServices {
        // Récupérer la database Exposed depuis BigDB
        val database = (db as BigDBMariaImpl).database
        
        val playerAccount = db.loadPlayerAccount(playerId)
        if (playerAccount == null) {
            Logger.error { "PlayerAccount for playerId=$playerId is null - data may not be synced yet" }
            throw IllegalStateException("PlayerAccount not found for playerId=$playerId")
        }
        
        val playerObjects = db.loadPlayerObjects(playerId)
        if (playerObjects == null) {
            Logger.error { "PlayerObjects for playerId=$playerId is null - data may not be synced yet" }
            throw IllegalStateException("PlayerObjects not found for playerId=$playerId")
        }

        val survivorLeaderId = playerObjects.playerSurvivor
        if (survivorLeaderId == null) {
            Logger.error { "playerSurvivor is null for playerId=$playerId" }
            throw IllegalStateException("playerSurvivor not found for playerId=$playerId")
        }

        val survivor = SurvivorService(
            survivorLeaderId = survivorLeaderId,
            survivorRepository = SurvivorRepositoryMaria(database)
        )
        
        val inventory = InventoryService(inventoryRepository = InventoryRepositoryMaria(database))
        val compound = CompoundService(compoundRepository = CompoundRepositoryMaria(database))
        val playerObjectMetadata = PlayerObjectsMetadataService(
            playerObjectsMetadataRepository = PlayerObjectsMetadataRepositoryMaria(database)
        )
        val batchRecycleJob = BatchRecycleJobService(
            batchRecycleJobRepository = BatchRecycleJobRepositoryMaria(database)
        )
        
        // Initialize services - log warnings but don't fail context creation
        // Services will work with default values if init fails
        survivor.init(playerId).onFailure {
            Logger.warn { "Survivor service init warning for playerId=$playerId: ${it.message}" }
        }
        inventory.init(playerId).onFailure {
            Logger.warn { "Inventory service init warning for playerId=$playerId: ${it.message}" }
        }
        compound.init(playerId).onFailure {
            Logger.warn { "Compound service init warning for playerId=$playerId: ${it.message}" }
        }
        playerObjectMetadata.init(playerId).onFailure {
            Logger.warn { "PlayerObjectMetadata service init warning for playerId=$playerId: ${it.message}" }
        }
        batchRecycleJob.init(playerId).onFailure {
            Logger.warn { "BatchRecycleJob service init warning for playerId=$playerId: ${it.message}" }
        }
        
        Logger.info { "Services initialized for playerId=$playerId" }
        
        return PlayerServices(
            survivor = survivor,
            compound = compound,
            inventory = inventory,
            playerObjectMetadata = playerObjectMetadata,
            batchRecycleJob = batchRecycleJob
        )
    }
    
    fun getContext(playerId: String): PlayerContext? {
        return players[playerId]
    }
    
    fun removePlayer(playerId: String) {
        players.remove(playerId)
    }

    /**
     * Remove player context only if the connection matches
     * Used to avoid removing a new context during cleanup of an old connection
     */
    fun removePlayerIfConnection(playerId: String, connectionId: String) {
        val context = players[playerId]
        if (context != null && context.connection.connectionId == connectionId) {
            players.remove(playerId)
        }
    }
    
    fun shutdown() {
        players.values.forEach {
            it.connection.shutdown()
        }
        players.clear()
    }
}
