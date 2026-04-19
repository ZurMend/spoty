package com.musicapp.ui.home

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.musicapp.data.models.Song
import com.musicapp.data.models.UiState
import com.musicapp.data.network.RetrofitClient
import kotlinx.coroutines.launch

class HomeViewModel : ViewModel() {

    private val api = RetrofitClient.getApi()

    private val _songs = MutableLiveData<UiState<List<Song>>>()
    val songs: LiveData<UiState<List<Song>>> = _songs

    // Lista completa para filtrado local
    private var allSongs: List<Song> = emptyList()

    init { loadSongs() }

    fun loadSongs(genre: String? = null, search: String? = null) {
        _songs.value = UiState.Loading
        viewModelScope.launch {
            runCatching {
                api.listSongs(genre = genre, search = search)
            }.onSuccess { response ->
                allSongs = response.songs
                _songs.value = UiState.Success(response.songs)
            }.onFailure { e ->
                _songs.value = UiState.Error(e.message ?: "Error al cargar canciones")
            }
        }
    }

    fun filterLocal(query: String) {
        val filtered = if (query.isBlank()) allSongs
        else allSongs.filter {
            it.nombre.contains(query, ignoreCase = true) ||
            it.artista.contains(query, ignoreCase = true) ||
            (it.album?.contains(query, ignoreCase = true) == true)
        }
        _songs.value = UiState.Success(filtered)
    }
}
