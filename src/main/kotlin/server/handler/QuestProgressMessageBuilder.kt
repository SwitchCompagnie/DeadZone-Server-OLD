package server.handler

import context.ServerContext
import core.quests.AchievementService
import core.quests.QuestSystem
import common.LogConfigSocketToClient
import common.Logger
import kotlinx.serialization.encodeToString
import kotlinx.serialization.json.Json
import server.core.Connection
import server.messaging.NetworkMessage
import server.protocol.PIOSerializer

/**
 * Utility object to build and send quest progress messages.
 * This is used both by QuestProgressHandler (when client requests)
 * and JoinHandler (proactive send after GAME_READY).
 */
object QuestProgressMessageBuilder {
    
    /**
     * Builds and sends a quest progress message to the given connection.
     * This ensures the client receives quest progress even if it doesn't request it
     * (e.g., when QuestSystem._initialized is already true on reconnect).
     */
    suspend fun buildAndSend(serverContext: ServerContext, connection: Connection) {
        val playerId = connection.playerId
        if (playerId == null) {
            Logger.warn(LogConfigSocketToClient) { "QUEST_PROGRESS: No playerId in connection" }
            val emptyMessage = listOf(NetworkMessage.QUEST_PROGRESS, """{"complete": null, "progress": null}""")
            connection.sendRaw(PIOSerializer.serialize(emptyMessage), enableLogging = false)
            return
        }

        val initialPlayerObjects = serverContext.db.loadPlayerObjects(playerId)
        if (initialPlayerObjects == null) {
            Logger.warn(LogConfigSocketToClient) { "QUEST_PROGRESS: PlayerObjects not found for playerId=$playerId" }
            val emptyMessage = listOf(NetworkMessage.QUEST_PROGRESS, """{"complete": null, "progress": null}""")
            connection.sendRaw(PIOSerializer.serialize(emptyMessage), enableLogging = false)
            return
        }

        val hasItemGoals = core.data.GameDefinition.questsById.values.any { quest ->
            quest.goals.any { it.type == core.data.resources.GoalType.ITEM }
        } || core.data.GameDefinition.achievementsById.values.any { achievement ->
            achievement.goals.any { it.type == core.data.resources.GoalType.ITEM }
        }
        val inventory = if (hasItemGoals) serverContext.db.loadInventory(playerId) else null

        val (playerObjectsAfterAchievements, newlyCompletedAchievements) = AchievementService.checkAndCompleteAchievements(
            initialPlayerObjects,
            inventory,
            serverContext
        )
        
        if (newlyCompletedAchievements.isNotEmpty()) {
            serverContext.db.updatePlayerObjectsJson(playerId, playerObjectsAfterAchievements)
            Logger.info(LogConfigSocketToClient) { 
                "QUEST_PROGRESS: Auto-completed ${newlyCompletedAchievements.size} achievements for player $playerId: ${newlyCompletedAchievements.keys}" 
            }
        }

        var playerObjects = playerObjectsAfterAchievements
        var questsCompleted = false
        for ((questId, questDef) in core.data.GameDefinition.questsById) {
            if (QuestSystem.isQuestCompleted(questId, playerObjects)) {
                continue
            }
            
            if (!QuestSystem.checkPrerequisites(questDef, playerObjects)) {
                continue
            }
            
            val progress = QuestSystem.checkQuestObjectives(questDef, playerObjects, inventory)
            if (progress.isCompleted) {
                playerObjects = QuestSystem.markQuestCompleted(questId, playerObjects)
                questsCompleted = true
                Logger.info(LogConfigSocketToClient) { "QUEST_PROGRESS: Auto-completed quest $questId for player $playerId" }
            }
        }
        
        val finalPlayerObjects = if (questsCompleted) {
            serverContext.db.updatePlayerObjectsJson(playerId, playerObjects)
            playerObjects
        } else {
            playerObjects
        }

        val completedMap = mutableMapOf<String, RewardData?>()
        
        for ((achievementId, completionData) in newlyCompletedAchievements) {
            val xp = if (completionData.rewards.xp > 0) completionData.rewards.xp else null
            
            val resources = mutableMapOf<String, Int>()
            for (item in completionData.rewards.items) {
                if (item.type == core.quests.RewardItemType.RESOURCE) {
                    resources[item.id] = item.quantity
                }
            }
            
            val items = completionData.rewards.items.filter { it.type == core.quests.RewardItemType.ITEM }
            val itemsList = if (items.isNotEmpty()) {
                items.map { ItemReward(id = it.id, qty = it.quantity) }
            } else null
            
            completedMap[achievementId] = if (xp != null || resources.isNotEmpty() || itemsList != null) {
                RewardData(
                    xp = xp,
                    res = if (resources.isNotEmpty()) resources else null,
                    items = itemsList
                )
            } else null
        }
        
        for ((questId, _) in core.data.GameDefinition.questsById) {
            if (QuestSystem.isQuestCompleted(questId, finalPlayerObjects) && !completedMap.containsKey(questId)) {
                completedMap[questId] = null
            }
        }
        
        for ((achievementId, _) in core.data.GameDefinition.achievementsById) {
            if (QuestSystem.isQuestCompleted(achievementId, finalPlayerObjects) && !completedMap.containsKey(achievementId)) {
                completedMap[achievementId] = null
            }
        }

        val questProgressMap = mutableMapOf<String, Map<String, Int>>()
        val availableQuests = QuestSystem.getAvailableQuestsWithProgress(finalPlayerObjects, inventory)

        for ((questId, progress) in availableQuests) {
            if (!progress.isCompleted) {
                val objectivesData = progress.objectives.entries.mapIndexed { index, (_, objProgress) ->
                    index.toString() to objProgress.current
                }.toMap()
                questProgressMap[questId] = objectivesData
            }
        }
        
        val achievementProgress = AchievementService.getAchievementProgress(finalPlayerObjects, inventory)
        for ((achievementId, progress) in achievementProgress) {
            if (!progress.isCompleted) {
                val objectivesData = progress.objectives.entries.mapIndexed { index, (_, objProgress) ->
                    index.toString() to objProgress.current
                }.toMap()
                questProgressMap[achievementId] = objectivesData
            }
        }

        val response = QuestProgressResponse(
            complete = if (completedMap.isEmpty()) null else completedMap,
            progress = if (questProgressMap.isEmpty()) null else questProgressMap
        )

        val json = Json.encodeToString(response)
        val message = listOf(NetworkMessage.QUEST_PROGRESS, json)
        connection.sendRaw(PIOSerializer.serialize(message), enableLogging = false)

        Logger.debug(LogConfigSocketToClient) { "QUEST_PROGRESS: Sent progress for player $playerId - ${completedMap.size} completed, ${questProgressMap.size} in progress" }
    }
}
