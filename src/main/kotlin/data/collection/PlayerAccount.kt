package data.collection

import io.ktor.util.date.getTimeMillis
import kotlinx.serialization.Serializable

@Serializable
data class PlayerAccount(
    val playerId: String,
    val hashedPassword: String,
    val email: String = "",
    val displayName: String,
    val avatarUrl: String,
    val createdAt: Long,
    val lastLogin: Long,
    val countryCode: String? = null,
    val serverMetadata: ServerMetadata,
)

@Serializable
data class ServerMetadata(
    val notes: String? = null,
    val flags: Map<String, Boolean> = emptyMap(),
    val extra: Map<String, String> = emptyMap(),
)