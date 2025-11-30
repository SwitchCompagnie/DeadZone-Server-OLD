package api.message.social

import io.ktor.util.date.getTimeMillis
import kotlinx.serialization.Serializable

@Serializable
data class SocialProfile(
    val userId: String = "",
    val displayName: String = "",
    val avatarUrl: String = "",
    val lastOnline: Long = 0,
    val countryCode: String = "",
    val userToken: String = "",
)
