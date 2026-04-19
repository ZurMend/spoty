package com.musicapp.data.network

import com.musicapp.data.models.*
import okhttp3.MultipartBody
import okhttp3.RequestBody
import okhttp3.ResponseBody
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // ─── AUTENTICACIÓN ────────────────────────────────────────────────────

    @FormUrlEncoded
    @POST("backend/auth.php")
    suspend fun login(
        @Field("action")   action:   String = "login",
        @Field("email")    email:    String,
        @Field("password") password: String
    ): Response<ResponseBody>

    @FormUrlEncoded
    @POST("backend/auth.php")
    suspend fun register(
        @Field("action")   action:   String = "register",
        @Field("nombre")   nombre:   String,
        @Field("email")    email:    String,
        @Field("password") password: String,
        @Field("confirm")  confirm:  String
    ): Response<ResponseBody>

    @GET("backend/auth.php")
    suspend fun logout(
        @Query("action") action: String = "logout"
    ): Response<ResponseBody>

    // ─── CANCIONES ────────────────────────────────────────────────────────

    @GET("backend/songs.php")
    suspend fun listSongs(
        @Query("action")  action: String  = "list",
        @Query("genre")   genre:  String? = null,
        @Query("search")  search: String? = null,
        @Query("limit")   limit:  Int     = 50,
        @Query("offset")  offset: Int     = 0
    ): SongsResponse

    @GET("backend/songs.php")
    suspend fun getSong(
        @Query("action") action: String = "get",
        @Query("id")     id:     Int
    ): SongResponse

    @FormUrlEncoded
    @POST("backend/songs.php")
    suspend fun registerPlay(
        @Field("action")  action: String = "history",
        @Field("song_id") songId: Int
    ): GenericResponse

    @Multipart
    @POST("backend/songs.php")
    suspend fun uploadSong(
        @Part("action")              action:           RequestBody,
        @Part("nombre")              nombre:           RequestBody,
        @Part("artista")             artista:          RequestBody,
        @Part("genero")              genero:           RequestBody,
        @Part("album")               album:            RequestBody,
        @Part("fecha_lanzamiento")   fechaLanzamiento: RequestBody,
        @Part("duracion")            duracion:         RequestBody,
        @Part                        archivo:          MultipartBody.Part,
        @Part                        imagen:           MultipartBody.Part? = null
    ): GenericResponse

    @FormUrlEncoded
    @POST("backend/songs.php")
    suspend fun deleteSong(
        @Field("action") action: String = "delete",
        @Field("id")     id:     Int
    ): GenericResponse

    // ─── PLAYLISTS ────────────────────────────────────────────────────────

    @GET("backend/playlists.php")
    suspend fun listPlaylists(
        @Query("action") action: String = "list"
    ): PlaylistsResponse

    @GET("backend/playlists.php")
    suspend fun getPlaylist(
        @Query("action") action: String = "get",
        @Query("id")     id:     Int
    ): PlaylistResponse

    @FormUrlEncoded
    @POST("backend/playlists.php")
    suspend fun createPlaylist(
        @Field("action")      action:      String = "create",
        @Field("nombre")      nombre:      String,
        @Field("descripcion") descripcion: String = ""
    ): GenericResponse

    @FormUrlEncoded
    @POST("backend/playlists.php")
    suspend fun updatePlaylist(
        @Field("action")      action:      String = "update",
        @Field("id")          id:          Int,
        @Field("nombre")      nombre:      String,
        @Field("descripcion") descripcion: String
    ): GenericResponse

    @FormUrlEncoded
    @POST("backend/playlists.php")
    suspend fun addSongToPlaylist(
        @Field("action")      action:     String = "add_song",
        @Field("playlist_id") playlistId: Int,
        @Field("song_id")     songId:     Int
    ): GenericResponse

    @FormUrlEncoded
    @POST("backend/playlists.php")
    suspend fun removeSongFromPlaylist(
        @Field("action")      action:     String = "remove_song",
        @Field("playlist_id") playlistId: Int,
        @Field("song_id")     songId:     Int
    ): GenericResponse

    @FormUrlEncoded
    @POST("backend/playlists.php")
    suspend fun deletePlaylist(
        @Field("action") action: String = "delete",
        @Field("id")     id:     Int
    ): GenericResponse

    // ─── RECOMENDACIONES ──────────────────────────────────────────────────

    @GET("backend/recommend.php")
    suspend fun recommendByGenre(
        @Query("action") action: String = "by_genre",
        @Query("genre")  genre:  String
    ): RecommendationsResponse

    @GET("backend/recommend.php")
    suspend fun nextSong(
        @Query("action")  action: String = "next",
        @Query("song_id") songId: Int
    ): RecommendationsResponse

    @GET("backend/recommend.php")
    suspend fun similarSongs(
        @Query("action") action: String = "similar",
        @Query("artist") artist: String,
        @Query("track")  track:  String
    ): RecommendationsResponse

    // ─── ADMIN ────────────────────────────────────────────────────────────

    @GET("backend/admin_api.php")
    suspend fun getStats(
        @Query("action") action: String = "stats"
    ): StatsResponse

    @GET("backend/admin_api.php")
    suspend fun getUsers(
        @Query("action") action: String = "users"
    ): UsersResponse

    @FormUrlEncoded
    @POST("backend/admin_api.php")
    suspend fun deleteUser(
        @Field("action") action: String = "delete_user",
        @Field("id")     id:     Int
    ): GenericResponse

    @FormUrlEncoded
    @POST("backend/admin_api.php")
    suspend fun changeRole(
        @Field("action") action: String = "change_role",
        @Field("id")     id:     Int,
        @Field("role")   role:   String
    ): GenericResponse
}
