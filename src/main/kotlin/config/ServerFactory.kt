package config

import server.GameServer
import server.GameServerConfig
import server.PolicyFileServer
import server.PolicyFileServerConfig
import server.core.BroadcastServer
import server.core.BroadcastServerConfig
import server.core.Server

object ServerFactory {

    fun createServers(config: AppConfiguration): List<Server> {
        return buildList {
            add(
                GameServer(
                    GameServerConfig(
                        host = config.game.host,
                        port = config.game.port
                    )
                )
            )

            if (config.broadcast.enabled) {
                add(
                    BroadcastServer(
                        BroadcastServerConfig(
                            host = config.broadcast.host,
                            port = config.broadcast.port
                        )
                    )
                )
            }

            if (config.broadcast.enablePolicyServer) {
                add(
                    PolicyFileServer(
                        PolicyFileServerConfig(
                            host = config.policy.host,
                            port = config.policy.port,
                            allowedPort = config.broadcast.port
                        )
                    )
                )
            }
        }
    }

    fun getBroadcastServer(servers: List<Server>): BroadcastServer? {
        return servers.firstOrNull { it is BroadcastServer } as? BroadcastServer
    }
}
