package api.handler

import api.bigdb.BigDBConverter
import api.message.db.LoadIndexRangeArgs
import api.message.db.LoadObjectsOutput
import api.protocol.pioFraming
import context.ServerContext
import common.logInput
import io.ktor.server.request.receiveChannel
import io.ktor.server.response.respondBytes
import io.ktor.server.routing.RoutingContext
import io.ktor.utils.io.toByteArray
import io.ktor.http.HttpStatusCode
import io.ktor.server.response.respond
import kotlinx.serialization.ExperimentalSerializationApi
import kotlinx.serialization.decodeFromByteArray
import kotlinx.serialization.encodeToByteArray
import kotlinx.serialization.protobuf.ProtoBuf
import common.Logger
import common.LogConfigAPIError

@OptIn(ExperimentalSerializationApi::class)
suspend fun RoutingContext.loadIndexRange(serverContext: ServerContext) {
    val body = try {
        call.receiveChannel().toByteArray()
    } catch (e: Exception) {
        call.respond(HttpStatusCode.BadRequest, "invalid_body")
        return
    }

    val args = try {
        ProtoBuf.decodeFromByteArray<LoadIndexRangeArgs>(body)
    } catch (e: Exception) {
        call.respond(HttpStatusCode.BadRequest, "invalid_payload")
        return
    }

    logInput(args, disableLogging = true)

    val objects = when (args.table) {
        "PlayerSummary" -> {
            // Query PlayerSummary data from PlayerSummaryService
            Logger.info { "Loading PlayerSummary with index: ${args.index}, limit: ${args.limit}" }
            
            val summaries = serverContext.playerSummaryService.queryByIndex(
                indexName = args.index,
                startValue = null,  // We'll use simple sorting instead of range filtering
                stopValue = null,
                limit = args.limit
            )
            
            Logger.info { "Found ${summaries.size} PlayerSummary records" }
            
            // Convert PlayerSummary objects to BigDB objects
            summaries.map { summary ->
                BigDBConverter.toBigDBObject(key = summary.key, obj = summary)
            }
        }
        else -> {
            Logger.warn(LogConfigAPIError) { "Unimplemented table for LoadIndexRange: ${args.table}" }
            emptyList()
        }
    }

    val outputBytes = try {
        ProtoBuf.encodeToByteArray(
            LoadObjectsOutput(objects = objects)
        )
    } catch (e: Exception) {
        call.respond(HttpStatusCode.InternalServerError, "encode_error")
        return
    }

    call.respondBytes(outputBytes.pioFraming())
}
