package com.musicapp.data.network

import android.content.Context
import android.content.SharedPreferences
import okhttp3.Cookie
import okhttp3.CookieJar
import okhttp3.HttpUrl

/**
 * Cookie jar que persiste las cookies (PHPSESSID) en SharedPreferences,
 * así la sesión PHP sobrevive entre reinicios de la app.
 */
class PersistentCookieJar(context: Context) : CookieJar {

    private val prefs: SharedPreferences =
        context.getSharedPreferences("cookies_prefs", Context.MODE_PRIVATE)

    private val cookieStore = mutableMapOf<String, MutableList<Cookie>>()

    init { loadFromPrefs() }

    override fun saveFromResponse(url: HttpUrl, cookies: List<Cookie>) {
        val host = url.host
        val list = cookieStore.getOrPut(host) { mutableListOf() }
        cookies.forEach { newCookie ->
            list.removeAll { it.name == newCookie.name }
            list.add(newCookie)
        }
        saveToPrefs()
    }

    override fun loadForRequest(url: HttpUrl): List<Cookie> {
        return cookieStore[url.host] ?: emptyList()
    }

    fun clearAll() {
        cookieStore.clear()
        prefs.edit().clear().apply()
    }

    private fun saveToPrefs() {
        val editor = prefs.edit()
        cookieStore.forEach { (host, cookies) ->
            val serialized = cookies.joinToString("|") {
                "${it.name}=${it.value};domain=${it.domain};path=${it.path}"
            }
            editor.putString(host, serialized)
        }
        editor.apply()
    }

    private fun loadFromPrefs() {
        prefs.all.forEach { (host, value) ->
            if (value is String && value.isNotBlank()) {
                val cookies = value.split("|").mapNotNull { raw ->
                    runCatching {
                        val parts = raw.split(";")
                        val (name, cookieValue) = parts[0].split("=", limit = 2)
                        val domain = parts.firstOrNull { it.startsWith("domain=") }
                            ?.removePrefix("domain=") ?: host
                        val path = parts.firstOrNull { it.startsWith("path=") }
                            ?.removePrefix("path=") ?: "/"
                        Cookie.Builder()
                            .name(name).value(cookieValue)
                            .domain(domain).path(path).build()
                    }.getOrNull()
                }
                cookieStore[host] = cookies.toMutableList()
            }
        }
    }
}
