package api.message.social

import kotlinx.serialization.Serializable

@Serializable
data class SocialRefreshOutput(
    val myProfile: SocialProfile = SocialProfile(),
    val friends: List<SocialProfile> = emptyList(),
    val blocked: String = "",
)
