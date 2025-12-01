package api.handler

import api.message.db.BigDBObject
import api.message.db.LoadObjectsArgs
import api.message.db.LoadObjectsOutput
import api.protocol.pioFraming
import context.ServerContext
import context.getPlayerContextOrNull
import data.collection.NeighborHistory
import data.collection.PlayerStates
import dev.deadzone.core.LazyDataUpdater
import common.LogConfigAPIError
import common.LogConfigSocketToClient
import common.Logger
import common.logInput
import common.logOutput
import io.ktor.http.*
import io.ktor.server.request.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import io.ktor.utils.io.*
import kotlinx.serialization.ExperimentalSerializationApi
import kotlinx.serialization.decodeFromByteArray
import kotlinx.serialization.encodeToByteArray
import kotlinx.serialization.protobuf.ProtoBuf
import kotlin.math.max

@OptIn(ExperimentalSerializationApi::class)
suspend fun RoutingContext.loadObjects(serverContext: ServerContext) {
    val startTime = System.currentTimeMillis()
    
    val body = try {
        call.receiveChannel().toByteArray()
    } catch (e: Exception) {
        Logger.error { "LoadObjects: invalid_body" }
        call.respond(HttpStatusCode.BadRequest, "invalid_body")
        return
    }

    val loadObjectsArgs = try {
        ProtoBuf.decodeFromByteArray<LoadObjectsArgs>(body)
    } catch (e: Exception) {
        Logger.error { "LoadObjects: invalid_payload - ${e.message}" }
        call.respond(HttpStatusCode.BadRequest, "invalid_payload")
        return
    }

    val requestedTables = loadObjectsArgs.objectIds.map { "${it.table}:${it.keys}" }
    Logger.info { "LoadObjects: Received request for $requestedTables" }
    logInput(loadObjectsArgs, disableLogging = true)

    val dbObjects = mutableListOf<BigDBObject>()

    try {
        for (objId in loadObjectsArgs.objectIds) {
            val playerId = objId.keys.firstOrNull() ?: continue
            if (playerId.endsWith("-2")) {
                Logger.debug { "LoadObjects: Skipping -2 key: $playerId for table ${objId.table}" }
                continue
            }

            val profile = serverContext.playerAccountRepository.getProfileOfPlayerId(playerId).getOrNull()
            if (profile == null) {
                Logger.warn { "LoadObjects: Profile not found for $playerId" }
                continue
            }
            val lastLogin = profile.lastLogin

            val playerObjects = serverContext.db.loadPlayerObjects(playerId)
            if (playerObjects == null) {
                Logger.warn { "LoadObjects: PlayerObjects not found for $playerId" }
                continue
            }
            val neighborHistory = serverContext.db.loadNeighborHistory(playerId)
            val inventory = serverContext.db.loadInventory(playerId)

            Logger.debug { "LoadObjects: Processing ${objId.table} for $playerId" }
            val obj: BigDBObject? = try {
                when (objId.table) {
                    "PlayerSummary" -> {
                        // Load PlayerSummary from PlayerSummaryService
                        val summary = serverContext.playerSummaryService.getOrCreate(playerId)
                        api.bigdb.BigDBConverter.toBigDBObject(key = playerId, obj = summary)
                    }
                    
                    "PlayerObjects" -> {
                        val updatedBuildings = LazyDataUpdater.removeBuildingTimerIfDone(playerObjects.buildings)
                        val updatedResources = LazyDataUpdater.depleteResources(lastLogin, playerObjects.resources)
                        val updatedSurvivors = playerObjects.survivors

                        val ctx = serverContext.getPlayerContextOrNull(playerId)
                        if (ctx != null) {
                            runCatching { ctx.services.compound.updateAllBuildings(updatedBuildings) }
                                .onFailure { Logger.warn { "Failed to update buildings for $playerId: ${it.message}" } }
                            runCatching { ctx.services.compound.updateResource { updatedResources } }
                                .onFailure { Logger.warn { "Failed to update resources for $playerId: ${it.message}" } }
                            runCatching { ctx.services.survivor.updateSurvivors(updatedSurvivors) }
                                .onFailure { Logger.warn { "Failed to update survivors for $playerId: ${it.message}" } }
                        } else {
                            runCatching {
                                val updatedPlayerObjects = playerObjects.copy(
                                    buildings = updatedBuildings,
                                    resources = updatedResources,
                                    survivors = updatedSurvivors
                                )
                                serverContext.db.updatePlayerObjectsJson(playerId, updatedPlayerObjects)
                            }.onFailure {
                                Logger.error(LogConfigSocketToClient) { "Failed to persist updates for $playerId: ${it.message}" }
                            }
                        }

                        LoadObjectsOutput.fromData(
                            playerObjects.copy(
                                buildings = updatedBuildings,
                                resources = updatedResources,
                                survivors = updatedSurvivors
                            ),
                            key = playerId
                        )
                    }

                    "NeighborHistory" -> neighborHistory?.let {
                        LoadObjectsOutput.fromData(NeighborHistory(playerId = playerId, map = it.map), key = playerId)
                    }

                    "Inventory" -> {
                        if (inventory != null) {
                            Logger.debug { "LoadObjects: Inventory has ${inventory.inventory.size} items" }
                            LoadObjectsOutput.fromData(inventory, key = playerId)
                        } else {
                            Logger.debug { "LoadObjects: Inventory is null for $playerId" }
                            null
                        }
                    }

                    "PlayerStates" -> {
                        // Create PlayerStates with current online status
                        val isOnline = serverContext.onlinePlayerRegistry.isOnline(playerId)
                        val playerStates = PlayerStates(
                            key = playerId,
                            online = isOnline,
                            onlineTimestamp = if (isOnline) System.currentTimeMillis() else 0,
                            underAttack = false,
                            protected = false,
                            banned = false
                        )
                        LoadObjectsOutput.fromData(playerStates, key = playerId)
                    }

                    else -> {
                        Logger.error(LogConfigAPIError) { "Unimplemented table for ${objId.table}" }
                        null
                    }
                }
            } catch (e: Exception) {
                Logger.error(LogConfigAPIError) { "Error processing ${objId.table} for $playerId: ${e.message}" }
                null
            }

            if (obj != null) {
                Logger.debug { "LoadObjects: Successfully created BigDBObject for ${objId.table}" }
                dbObjects.add(obj)
            } else {
                Logger.warn { "LoadObjects: Failed to create BigDBObject for ${objId.table}" }
            }
        }
    } catch (e: Exception) {
        Logger.error(LogConfigAPIError) { "Critical error in loadObjects: ${e.message}" }
        e.printStackTrace()
    }

    val elapsedMs = System.currentTimeMillis() - startTime
    Logger.info { "LoadObjects: Returning ${dbObjects.size} objects for $requestedTables in ${elapsedMs}ms" }
    val encoded = ProtoBuf.encodeToByteArray(LoadObjectsOutput(objects = dbObjects))
    logOutput(encoded, disableLogging = true)
    call.respondBytes(encoded.pioFraming())
}