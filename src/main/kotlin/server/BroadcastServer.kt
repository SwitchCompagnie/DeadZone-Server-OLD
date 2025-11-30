package server.core

import server.broadcast.BroadcastMessage
import context.ServerContext
import io.ktor.network.selector.*
import io.ktor.network.sockets.*
import io.ktor.utils.io.*
import kotlinx.coroutines.*
import server.core.Server
import common.Emoji
import common.Logger
import java.io.IOException
import java.util.*
import java.util.concurrent.ConcurrentHashMap

data class BroadcastServerConfig(
    val host: String = "0.0.0.0",
    val port: Int = 2121,
)

data class BroadcastClient(
    val clientId: UUID,
    val remoteAddress: String,
    val job: Job,
    val output: ByteWriteChannel
)

open class BroadcastServer(private val config: BroadcastServerConfig) : Server {
    override val name: String = "BroadcastServer"

    private lateinit var broadcastServerScope: CoroutineScope
    private val selectorManager = SelectorManager(Dispatchers.IO)
    private var serverSocket: ServerSocket? = null
    private var serverJob: Job? = null
    private val clients = ConcurrentHashMap<UUID, BroadcastClient>()
    private var running = false

    override fun isRunning(): Boolean = running

    open fun getClientCount(): Int = clients.size

    override suspend fun initialize(scope: CoroutineScope, context: ServerContext) {
        broadcastServerScope = CoroutineScope(scope.coroutineContext + SupervisorJob() + Dispatchers.IO)
    }

    override suspend fun start() {
        if (running) {
            Logger.warn("Broadcast server is already running")
            return
        }
        running = true

        serverJob = broadcastServerScope.launch(Dispatchers.IO + SupervisorJob()) {
            try {
                val socket = aSocket(selectorManager).tcp().bind(config.host, config.port)
                serverSocket = socket
                Logger.info("${Emoji.Satellite} Broadcast listening on ${config.host}:${config.port}")

                while (isActive) {
                    val clientSocket = socket.accept()
                    handleClient(clientSocket)
                }
            } catch (e: Exception) {
                Logger.error("Failed to start broadcast server on port ${config.port}: ${e.message}")
            }
        }
    }

    private fun handleClient(socket: Socket) {
        val address = socket.remoteAddress
        val clientId = UUID.randomUUID()
        Logger.info("New broadcast connection from $address")

        val job = broadcastServerScope.launch(Dispatchers.IO + SupervisorJob()) {
            val input = socket.openReadChannel()

            try {
                val buffer = ByteArray(1024)
                while (isActive) {
                    val bytes = input.readAvailable(buffer)
                    if (bytes <= 0) break
                }
            } catch (e: Exception) {
                Logger.warn("Broadcast socket error for $address: ${e.message}")
            } finally {
                removeClient(clientId)
                socket.close()
                Logger.info("Closed broadcast connection $address")
            }
        }
        clients[clientId] = BroadcastClient(
            clientId = clientId,
            remoteAddress = address.toString(),
            job = job,
            output = socket.openWriteChannel(autoFlush = true)
        )
    }

    private fun removeClient(clientId: UUID) {
        if (clients.remove(clientId) != null) {
            Logger.info("${Emoji.Phone} Client disconnected from broadcast (${clients.size} total)")
        }
    }

    open suspend fun broadcast(message: BroadcastMessage) {
        broadcast(message.toWireFormat())
    }

    open suspend fun broadcast(message: String) {
        if (clients.isEmpty()) return

        val bytesData = message.toByteArray(Charsets.UTF_8)
        val disconnectedClients = mutableListOf<UUID>()

        clients.values.forEach { client ->
            try {
                client.output.writeFully(bytesData)
            } catch (e: IOException) {
                Logger.warn("Failed to send broadcast to client: ${e.message}")
                disconnectedClients.add(client.clientId)
            }
        }

        disconnectedClients.forEach { removeClient(it) }
    }

    override suspend fun shutdown() {
        running = false
        clients.forEach { (_, u) ->
            u.output.flushAndClose()
            u.job.cancelAndJoin()
        }
        clients.clear()
        serverSocket?.close()
        serverJob?.cancelAndJoin()
        broadcastServerScope.cancel()
        selectorManager.close()
        Logger.info("${Emoji.Satellite} Broadcast server stopped.")
    }
}
