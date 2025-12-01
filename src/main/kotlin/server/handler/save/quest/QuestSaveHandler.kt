package server.handler.save.quest

import common.LogConfigSocketError
import common.LogConfigSocketToClient
import common.Logger
import context.requirePlayerContext
import core.survivor.XpLevelService
import kotlinx.serialization.json.buildJsonObject
import kotlinx.serialization.json.put
import kotlinx.serialization.json.putJsonArray
import kotlinx.serialization.json.putJsonObject
import server.handler.buildMsg
import server.handler.save.SaveHandlerContext
import server.handler.save.SaveSubHandler
import server.messaging.SaveDataMethod
import server.protocol.PIOSerializer

class QuestSaveHandler : SaveSubHandler {
    override val supportedTypes: Set<String> = SaveDataMethod.QUEST_SAVES

    override suspend fun handle(ctx: SaveHandlerContext) = with(ctx) {
        val playerId = connection.playerId ?: run {
            Logger.error(LogConfigSocketToClient) { "No playerId in connection" }
            return
        }

        when (type) {
            SaveDataMethod.QUEST_TRACK -> {
                Logger.info(LogConfigSocketToClient) { "QUEST_TRACK: playerId=$playerId" }

                val questId = data["id"] as? String ?: run {
                    Logger.error(LogConfigSocketToClient) { "QUEST_TRACK: Missing quest id" }
                    return
                }

                val playerObjects = serverContext.db.loadPlayerObjects(playerId) ?: run {
                    Logger.error(LogConfigSocketToClient) { "QUEST_TRACK: PlayerObjects not found playerId=$playerId" }
                    return
                }

                val currentTracked = playerObjects.questsTracked?.split(",")?.filter { it.isNotBlank() }?.toMutableList() ?: mutableListOf()

                if (!currentTracked.contains(questId)) {
                    currentTracked.add(questId)
                    val updatedPlayerObjects = playerObjects.copy(questsTracked = currentTracked.joinToString(","))
                    serverContext.db.updatePlayerObjectsJson(playerId, updatedPlayerObjects)
                    Logger.info(LogConfigSocketToClient) { "QUEST_TRACK: Tracked quest $questId playerId=$playerId" }
                } else {
                    Logger.info(LogConfigSocketToClient) { "QUEST_TRACK: Quest $questId already tracked playerId=$playerId" }
                }

                val responseElement = buildJsonObject { put("success", true) }
                send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
            }

            SaveDataMethod.QUEST_UNTRACK -> {
                Logger.info(LogConfigSocketToClient) { "QUEST_UNTRACK: playerId=$playerId" }

                val questId = data["id"] as? String ?: run {
                    Logger.error(LogConfigSocketToClient) { "QUEST_UNTRACK: Missing quest id" }
                    return
                }

                val playerObjects = serverContext.db.loadPlayerObjects(playerId) ?: run {
                    Logger.error(LogConfigSocketToClient) { "QUEST_UNTRACK: PlayerObjects not found playerId=$playerId" }
                    return
                }

                val currentTracked = playerObjects.questsTracked?.split(",")?.filter { it.isNotBlank() }?.toMutableList() ?: mutableListOf()

                if (currentTracked.remove(questId)) {
                    val updatedPlayerObjects = playerObjects.copy(
                        questsTracked = if (currentTracked.isEmpty()) null else currentTracked.joinToString(",")
                    )
                    serverContext.db.updatePlayerObjectsJson(playerId, updatedPlayerObjects)
                    Logger.info(LogConfigSocketToClient) { "QUEST_UNTRACK: Untracked quest $questId playerId=$playerId" }
                } else {
                    Logger.info(LogConfigSocketToClient) { "QUEST_UNTRACK: Quest $questId was not tracked playerId=$playerId" }
                }

                val responseElement = buildJsonObject { put("success", true) }
                send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
            }

            SaveDataMethod.QUEST_COLLECT -> {
                Logger.info(LogConfigSocketToClient) { "QUEST_COLLECT: playerId=$playerId" }

                val questId = data["id"] as? String ?: run {
                    Logger.error(LogConfigSocketToClient) { "QUEST_COLLECT: Missing quest id" }
                    return
                }

                val playerObjects = serverContext.db.loadPlayerObjects(playerId) ?: run {
                    Logger.error(LogConfigSocketToClient) { "QUEST_COLLECT: PlayerObjects not found playerId=$playerId" }
                    return
                }

                val questDef = core.data.GameDefinition.findQuestOrAchievement(questId)
                if (questDef == null) {
                    Logger.error(LogConfigSocketToClient) { "QUEST_COLLECT: Quest not found: $questId" }
                    val responseElement = buildJsonObject {
                        put("success", false)
                        put("error", "Quest not found")
                    }
                    send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
                    return
                }

                var updatedPlayerObjects = playerObjects
                if (!core.quests.QuestSystem.isQuestCompleted(questId, playerObjects)) {
                    val progress = core.quests.QuestSystem.checkQuestObjectives(questDef, playerObjects)
                    if (progress.isCompleted) {
                        updatedPlayerObjects = core.quests.QuestSystem.markQuestCompleted(questId, updatedPlayerObjects)
                        Logger.info(LogConfigSocketToClient) { "QUEST_COLLECT: Auto-completed quest $questId playerId=$playerId" }
                    } else {
                        Logger.warn(LogConfigSocketToClient) { "QUEST_COLLECT: Quest $questId not completed playerId=$playerId" }
                        val responseElement = buildJsonObject {
                            put("success", false)
                            put("error", "Quest not completed")
                        }
                        send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
                        return
                    }
                }

                val isAlreadyCollected = core.quests.QuestSystem.isQuestCollected(questId, updatedPlayerObjects)
                if (isAlreadyCollected) {
                    Logger.warn(LogConfigSocketToClient) { "QUEST_COLLECT: Quest $questId already collected playerId=$playerId" }
                }

                val playerSurvivor = updatedPlayerObjects.survivors.find { it.id == updatedPlayerObjects.playerSurvivor }
                val playerLevel = playerSurvivor?.level ?: 1

                val rewards = if (!isAlreadyCollected) {
                    core.quests.QuestSystem.calculateRewards(questDef, playerLevel)
                } else {
                    core.quests.QuestRewardResult(xp = 0, items = emptyList())
                }

                if (rewards.xp > 0 && playerSurvivor != null) {
                    try {
                        val svc = serverContext.requirePlayerContext(playerId).services
                        val (updatedLeader, updatedPlayerObjectsAfterXp) = XpLevelService.addXpToLeader(
                            survivor = playerSurvivor,
                            playerObjects = updatedPlayerObjects,
                            earnedXp = rewards.xp
                        )
                        val updateResult = svc.survivor.updateSurvivor(playerSurvivor.id) { _ -> updatedLeader }
                        if (updateResult.isFailure) {
                            Logger.error(LogConfigSocketError) {
                                "QUEST_COLLECT: Failed to update leader XP playerId=$playerId: ${updateResult.exceptionOrNull()?.message}"
                            }
                        }
                        updatedPlayerObjects = updatedPlayerObjectsAfterXp
                        Logger.info(LogConfigSocketToClient) {
                            "QUEST_COLLECT: Added ${rewards.xp} XP. Level: ${playerSurvivor.level}->${updatedLeader.level}, " +
                            "XP: ${playerSurvivor.xp}->${updatedLeader.xp}, LevelPts: ${updatedPlayerObjects.levelPts}"
                        }
                    } catch (e: Exception) {
                        Logger.error(LogConfigSocketError) { "QUEST_COLLECT: Failed to apply XP playerId=$playerId: ${e.message}" }
                    }
                }

                var updatedResources = updatedPlayerObjects.resources
                val resourceRewards = mutableMapOf<String, Int>()
                val itemRewards = mutableListOf<Map<String, Any>>()

                for (item in rewards.items) {
                    when (item.type) {
                        core.quests.RewardItemType.RESOURCE -> {
                            resourceRewards[item.id] = item.quantity
                            when (item.id) {
                                "wood" -> updatedResources = updatedResources.copy(wood = updatedResources.wood + item.quantity)
                                "metal" -> updatedResources = updatedResources.copy(metal = updatedResources.metal + item.quantity)
                                "cloth" -> updatedResources = updatedResources.copy(cloth = updatedResources.cloth + item.quantity)
                                "food" -> updatedResources = updatedResources.copy(food = updatedResources.food + item.quantity)
                                "water" -> updatedResources = updatedResources.copy(water = updatedResources.water + item.quantity)
                                "ammunition" -> updatedResources = updatedResources.copy(ammunition = updatedResources.ammunition + item.quantity)
                                "cash" -> updatedResources = updatedResources.copy(cash = updatedResources.cash + item.quantity)
                            }
                        }
                        core.quests.RewardItemType.ITEM -> {
                            try {
                                val services = serverContext.requirePlayerContext(playerId).services
                                val itemId = "${item.id}_${System.currentTimeMillis()}"
                                val newItem = core.items.model.Item(
                                    id = itemId,
                                    type = item.id,
                                    qty = item.quantity.toUInt(),
                                    specData = null,
                                    new = true
                                )
                                services.inventory.updateInventory { items -> items + newItem }
                                itemRewards.add(mapOf(
                                    "id" to itemId,
                                    "type" to item.id,
                                    "qty" to item.quantity,
                                    "new" to true
                                ))
                                Logger.info(LogConfigSocketToClient) { "QUEST_COLLECT: Added item ${item.id} x${item.quantity} playerId=$playerId" }
                            } catch (e: Exception) {
                                Logger.error(LogConfigSocketToClient) { "QUEST_COLLECT: Failed to add item ${item.id}: ${e.message}" }
                            }
                        }
                    }
                }
                updatedPlayerObjects = updatedPlayerObjects.copy(resources = updatedResources)

                updatedPlayerObjects = core.quests.QuestSystem.markQuestCollected(questId, updatedPlayerObjects)
                serverContext.db.updatePlayerObjectsJson(playerId, updatedPlayerObjects)

                Logger.info(LogConfigSocketToClient) { "QUEST_COLLECT: Collected quest $questId playerId=$playerId. XP: ${rewards.xp}, Items: ${rewards.items.size}" }

                val responseElement = buildJsonObject {
                    put("success", true)
                    put("xp", rewards.xp)
                    put("levelPts", updatedPlayerObjects.levelPts.toInt())
                    if (resourceRewards.isNotEmpty()) {
                        putJsonObject("res") {
                            resourceRewards.forEach { (resourceId, amount) ->
                                put(resourceId, amount)
                            }
                        }
                    }
                    if (itemRewards.isNotEmpty()) {
                        putJsonArray("items") {
                            itemRewards.forEach { item ->
                                add(buildJsonObject {
                                    put("id", item["id"] as String)
                                    put("type", item["type"] as String)
                                    put("qty", item["qty"] as Int)
                                    put("new", item["new"] as Boolean)
                                })
                            }
                        }
                    }
                }
                send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
            }

            SaveDataMethod.GLOBAL_QUEST_COLLECT -> {
                Logger.info(LogConfigSocketToClient) { "GLOBAL_QUEST_COLLECT: playerId=$playerId" }

                val questId = data["id"] as? String ?: run {
                    Logger.error(LogConfigSocketToClient) { "GLOBAL_QUEST_COLLECT: Missing quest id" }
                    return
                }

                val playerObjects = serverContext.db.loadPlayerObjects(playerId) ?: run {
                    Logger.error(LogConfigSocketToClient) { "GLOBAL_QUEST_COLLECT: PlayerObjects not found playerId=$playerId" }
                    return
                }

                val questDef = core.data.GameDefinition.findQuestOrAchievement(questId)
                if (questDef == null) {
                    Logger.error(LogConfigSocketToClient) { "GLOBAL_QUEST_COLLECT: Quest not found: $questId" }
                    val responseElement = buildJsonObject {
                        put("success", false)
                        put("error", "Quest not found")
                    }
                    send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
                    return
                }

                var updatedPlayerObjects = playerObjects
                val playerSurvivor = updatedPlayerObjects.survivors.find { it.id == updatedPlayerObjects.playerSurvivor }
                val playerLevel = playerSurvivor?.level ?: 1

                val rewards = core.quests.QuestSystem.calculateRewards(questDef, playerLevel)

                if (rewards.xp > 0 && playerSurvivor != null) {
                    try {
                        val svc = serverContext.requirePlayerContext(playerId).services
                        val (updatedLeader, updatedPlayerObjectsAfterXp) = XpLevelService.addXpToLeader(
                            survivor = playerSurvivor,
                            playerObjects = updatedPlayerObjects,
                            earnedXp = rewards.xp
                        )
                        val updateResult = svc.survivor.updateSurvivor(playerSurvivor.id) { _ -> updatedLeader }
                        if (updateResult.isFailure) {
                            Logger.error(LogConfigSocketError) {
                                "GLOBAL_QUEST_COLLECT: Failed to update leader XP playerId=$playerId: ${updateResult.exceptionOrNull()?.message}"
                            }
                        }
                        updatedPlayerObjects = updatedPlayerObjectsAfterXp
                        Logger.info(LogConfigSocketToClient) {
                            "GLOBAL_QUEST_COLLECT: Added ${rewards.xp} XP. Level: ${playerSurvivor.level}->${updatedLeader.level}, " +
                            "XP: ${playerSurvivor.xp}->${updatedLeader.xp}"
                        }
                    } catch (e: Exception) {
                        Logger.error(LogConfigSocketError) { "GLOBAL_QUEST_COLLECT: Failed to apply XP playerId=$playerId: ${e.message}" }
                    }
                }

                var updatedResources = updatedPlayerObjects.resources
                val resourceRewards = mutableMapOf<String, Int>()
                val itemRewards = mutableListOf<Map<String, Any>>()

                for (item in rewards.items) {
                    when (item.type) {
                        core.quests.RewardItemType.RESOURCE -> {
                            resourceRewards[item.id] = item.quantity
                            when (item.id) {
                                "wood" -> updatedResources = updatedResources.copy(wood = updatedResources.wood + item.quantity)
                                "metal" -> updatedResources = updatedResources.copy(metal = updatedResources.metal + item.quantity)
                                "cloth" -> updatedResources = updatedResources.copy(cloth = updatedResources.cloth + item.quantity)
                                "food" -> updatedResources = updatedResources.copy(food = updatedResources.food + item.quantity)
                                "water" -> updatedResources = updatedResources.copy(water = updatedResources.water + item.quantity)
                                "ammunition" -> updatedResources = updatedResources.copy(ammunition = updatedResources.ammunition + item.quantity)
                                "cash" -> updatedResources = updatedResources.copy(cash = updatedResources.cash + item.quantity)
                            }
                        }
                        core.quests.RewardItemType.ITEM -> {
                            try {
                                val services = serverContext.requirePlayerContext(playerId).services
                                val itemId = "${item.id}_${System.currentTimeMillis()}"
                                val newItem = core.items.model.Item(
                                    id = itemId,
                                    type = item.id,
                                    qty = item.quantity.toUInt(),
                                    specData = null,
                                    new = true
                                )
                                services.inventory.updateInventory { items -> items + newItem }
                                itemRewards.add(mapOf(
                                    "id" to itemId,
                                    "type" to item.id,
                                    "qty" to item.quantity,
                                    "new" to true
                                ))
                                Logger.info(LogConfigSocketToClient) { "GLOBAL_QUEST_COLLECT: Added item ${item.id} x${item.quantity} playerId=$playerId" }
                            } catch (e: Exception) {
                                Logger.error(LogConfigSocketToClient) { "GLOBAL_QUEST_COLLECT: Failed to add item ${item.id}: ${e.message}" }
                            }
                        }
                    }
                }
                updatedPlayerObjects = updatedPlayerObjects.copy(resources = updatedResources)
                serverContext.db.updatePlayerObjectsJson(playerId, updatedPlayerObjects)

                Logger.info(LogConfigSocketToClient) { "GLOBAL_QUEST_COLLECT: Collected global quest $questId playerId=$playerId. XP: ${rewards.xp}, Items: ${rewards.items.size}" }

                val responseElement = buildJsonObject {
                    put("success", true)
                    put("xp", rewards.xp)
                    if (resourceRewards.isNotEmpty()) {
                        putJsonObject("res") {
                            resourceRewards.forEach { (resourceId, amount) ->
                                put(resourceId, amount)
                            }
                        }
                    }
                    if (itemRewards.isNotEmpty()) {
                        putJsonArray("items") {
                            itemRewards.forEach { item ->
                                add(buildJsonObject {
                                    put("id", item["id"] as String)
                                    put("type", item["type"] as String)
                                    put("qty", item["qty"] as Int)
                                    put("new", item["new"] as Boolean)
                                })
                            }
                        }
                    }
                }
                send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
            }

            SaveDataMethod.REPEAT_ACHIEVEMENT -> {
                Logger.info(LogConfigSocketToClient) { "REPEAT_ACHIEVEMENT: playerId=$playerId" }

                val achievementId = data["id"] as? String ?: run {
                    Logger.error(LogConfigSocketToClient) { "REPEAT_ACHIEVEMENT: Missing achievement id" }
                    return
                }

                val value = (data["val"] as? Number)?.toDouble() ?: 0.0
                val xpEarned = (data["xp"] as? Number)?.toInt() ?: 0

                val playerObjects = serverContext.db.loadPlayerObjects(playerId) ?: run {
                    Logger.error(LogConfigSocketToClient) { "REPEAT_ACHIEVEMENT: PlayerObjects not found playerId=$playerId" }
                    val responseElement = buildJsonObject { put("success", false) }
                    send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
                    return
                }

                if (xpEarned > 0) {
                    try {
                        val svc = serverContext.requirePlayerContext(playerId).services
                        val playerSurvivor = playerObjects.survivors.find { it.id == playerObjects.playerSurvivor }

                        if (playerSurvivor != null) {
                            val (updatedLeader, updatedPlayerObjectsAfterXp) = XpLevelService.addXpToLeader(
                                survivor = playerSurvivor,
                                playerObjects = playerObjects,
                                earnedXp = xpEarned
                            )
                            val updateResult = svc.survivor.updateSurvivor(playerSurvivor.id) { _ -> updatedLeader }
                            if (updateResult.isFailure) {
                                Logger.error(LogConfigSocketError) {
                                    "REPEAT_ACHIEVEMENT: Failed to update leader XP playerId=$playerId: ${updateResult.exceptionOrNull()?.message}"
                                }
                                val responseElement = buildJsonObject { put("success", false) }
                                send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
                                return
                            }
                            serverContext.db.updatePlayerObjectsJson(playerId, updatedPlayerObjectsAfterXp)
                            Logger.info(LogConfigSocketToClient) {
                                "REPEAT_ACHIEVEMENT: Added $xpEarned XP achievement=$achievementId. " +
                                "Level: ${playerSurvivor.level}->${updatedLeader.level}, XP: ${playerSurvivor.xp}->${updatedLeader.xp}"
                            }
                        } else {
                            Logger.warn(LogConfigSocketToClient) { "REPEAT_ACHIEVEMENT: Player survivor not found playerId=$playerId" }
                        }
                    } catch (e: Exception) {
                        Logger.error(LogConfigSocketError) { "REPEAT_ACHIEVEMENT: Failed to apply XP playerId=$playerId: ${e.message}" }
                        val responseElement = buildJsonObject { put("success", false) }
                        send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
                        return
                    }
                }

                val responseElement = buildJsonObject {
                    put("success", true)
                    put("val", value)
                    put("xp", xpEarned)
                }
                send(PIOSerializer.serialize(buildMsg(saveId, responseElement.toString())))
            }

            SaveDataMethod.QUEST_DAILY_DECLINE -> {
                Logger.warn(LogConfigSocketToClient) { "QUEST_DAILY_DECLINE: Not implemented" }
            }

            SaveDataMethod.QUEST_DAILY_ACCEPT -> {
                Logger.warn(LogConfigSocketToClient) { "QUEST_DAILY_ACCEPT: Not implemented" }
            }
        }
    }
}
