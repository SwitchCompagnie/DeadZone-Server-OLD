package user.auth

import user.model.PlayerSession

interface AuthProvider {
    suspend fun register(username: String, password: String, email: String? = null, countryCode: String? = null): PlayerSession
    suspend fun login(username: String, password: String): PlayerSession?
    suspend fun doesUserExist(username: String): Boolean
}
