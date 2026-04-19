package com.musicapp.ui.playlists

import android.widget.Toast
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.musicapp.data.models.Playlist
import com.musicapp.data.models.UiState
import com.musicapp.data.network.RetrofitClient
import kotlinx.coroutines.launch

class PlaylistsViewModel : ViewModel() {

    private val api = RetrofitClient.getApi()

    private val _playlists = MutableLiveData<UiState<List<Playlist>>>()
    val playlists: LiveData<UiState<List<Playlist>>> = _playlists

    init { loadPlaylists() }

    fun loadPlaylists() {
        _playlists.value = UiState.Loading
        viewModelScope.launch {
            runCatching { api.listPlaylists() }
                .onSuccess { _playlists.value = UiState.Success(it.playlists) }
                .onFailure { _playlists.value = UiState.Error(it.message ?: "Error") }
        }
    }

    fun createPlaylist(nombre: String, descripcion: String) {
        viewModelScope.launch {
            runCatching { api.createPlaylist(nombre = nombre, descripcion = descripcion) }
                .onSuccess { if (it.success) loadPlaylists() }
        }
    }

    fun deletePlaylist(id: Int) {
        viewModelScope.launch {
            runCatching { api.deletePlaylist(id = id) }
                .onSuccess { if (it.success) loadPlaylists() }
        }
    }
}
