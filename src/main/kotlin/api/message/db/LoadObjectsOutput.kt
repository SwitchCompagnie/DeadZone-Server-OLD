package api.message.db

import api.bigdb.BigDBConverter
import kotlinx.serialization.Serializable

@Serializable
data class LoadObjectsOutput(
    val objects: List<BigDBObject> = listOf()
) {
    companion object {
        inline fun <reified T : Any> fromData(obj: T, key: String = ""): BigDBObject {
            return BigDBConverter.toBigDBObject(key = key, obj = obj)
        }
    }
}