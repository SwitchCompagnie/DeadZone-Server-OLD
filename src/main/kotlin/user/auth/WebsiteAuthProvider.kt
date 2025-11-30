package user.auth

import user.model.PlayerSession
import data.db.BigDB
import user.PlayerAccountRepository

class WebsiteAuthProvider(
    private val db: BigDB,
    private val playerAccountRepository: PlayerAccountRepository,
    private val sessionManager: SessionManager
) : AuthProvider {
    override suspend fun register(username: String, password: String, email: String?, countryCode: String?): PlayerSession {
        val pid = db.createUser(username, password, email, countryCode)
        return sessionManager.create(playerId = pid)
    }

    override suspend fun login(username: String, password: String): PlayerSession? {
        val pid = playerAccountRepository.verifyCredentials(username, password).getOrNull() ?: return null
        return sessionManager.create(pid)
    }

    override suspend fun doesUserExist(username: String): Boolean {
        return playerAccountRepository.doesUserExist(username).getOrNull() ?: false
    }
}