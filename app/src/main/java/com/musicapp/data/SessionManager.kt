package com.musicapp.data

import android.content.Context
import android.content.SharedPreferences

/**
 * Almacena datos de sesión del usuario (nombre, email, rol).
 * Las cookies PHP se manejan por separado en PersistentCookieJar.
 */
class SessionManager(context: Context) {

    private val prefs: SharedPreferences =
        context.getSharedPreferences("session_prefs", Context.MODE_PRIVATE)

    companion object {
        private const val KEY_LOGGED_IN = "logged_in"
        private const val KEY_USER_ID    = "user_id"
        private const val KEY_NOMBRE     = "nombre"
        private const val KEY_EMAIL      = "email"
        private const val KEY_ROLE       = "role"
    }

    fun saveSession(id: Int, nombre: String, email: String, role: String) {
        prefs.edit()
            .putBoolean(KEY_LOGGED_IN, true)
            .putInt(KEY_USER_ID, id)
            .putString(KEY_NOMBRE, nombre)
            .putString(KEY_EMAIL, email)
            .putString(KEY_ROLE, role)
            .apply()
    }

    fun isLoggedIn(): Boolean = prefs.getBoolean(KEY_LOGGED_IN, false)
    fun getUserId():   Int     = prefs.getInt(KEY_USER_ID, -1)
    fun getNombre():   String  = prefs.getString(KEY_NOMBRE, "") ?: ""
    fun getEmail():    String  = prefs.getString(KEY_EMAIL, "") ?: ""
    fun getRole():     String  = prefs.getString(KEY_ROLE, "user") ?: "user"
    fun isAdmin():     Boolean = getRole() == "admin"

    fun clearSession() {
        prefs.edit().clear().apply()
    }
}
