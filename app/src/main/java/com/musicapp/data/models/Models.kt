package com.musicapp.data.models

import com.google.gson.annotations.SerializedName

// ─── Entidades principales ─────────────────────────────────────────────────

data class Song(
    val id: Int = 0,
    val nombre: String = "",
    val artista: String = "",
    val genero: String = "",
    val duracion: Int = 0,          // segundos
    @SerializedName("imagen_url")  val imagenUrl: String? = null,
    val album: String? = null,
    @SerializedName("fecha_lanzamiento") val fechaLanzamiento: String? = null,
    @SerializedName("archivo_url") val archivoUrl: String? = null
)

data class Playlist(
    val id: Int = 0,
    val nombre: String = "",
    val descripcion: String? = null,
    @SerializedName("portada_url") val portadaUrl: String? = null,
    @SerializedName("total_canciones") val totalCanciones: Int = 0,
    @SerializedName("created_at") val createdAt: String = "",
    val songs: List<Song>? = null
)

data class User(
    val id: Int = 0,
    val nombre: String = "",
    val email: String = "",
    val role: String = "user",
    @SerializedName("created_at") val createdAt: String = ""
)

data class Recommendation(
    val nombre: String = "",
    val artista: String = "",
    val url: String? = null,
    val imagen: String? = null
)

// ─── Wrappers de respuesta ─────────────────────────────────────────────────

data class SongsResponse(val songs: List<Song> = emptyList())

data class SongResponse(val song: Song = Song())

data class PlaylistsResponse(val playlists: List<Playlist> = emptyList())

data class PlaylistResponse(val playlist: Playlist = Playlist())

data class UsersResponse(val users: List<User> = emptyList())

data class StatsResponse(
    val songs: Int = 0,
    val users: Int = 0,
    val plays: Int = 0,
    val playlists: Int = 0
)

data class GenericResponse(
    val success: Boolean = false,
    val message: String? = null,
    val id: Int? = null
)

data class RecommendationsResponse(
    val recommendations: List<Recommendation> = emptyList(),
    val genre: String? = null,
    @SerializedName("based_on_song")  val basedOnSong: String? = null,
    @SerializedName("based_on_genre") val basedOnGenre: String? = null
)

// ─── Estado sellado para la UI ─────────────────────────────────────────────

sealed class UiState<out T> {
    object Loading : UiState<Nothing>()
    data class Success<T>(val data: T) : UiState<T>()
    data class Error(val message: String) : UiState<Nothing>()
}
