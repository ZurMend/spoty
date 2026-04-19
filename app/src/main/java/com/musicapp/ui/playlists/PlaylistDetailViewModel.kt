package com.musicapp.ui.playlists

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.musicapp.data.models.Playlist
import com.musicapp.data.models.UiState
import com.musicapp.data.network.RetrofitClient
import kotlinx.coroutines.launch

class PlaylistDetailViewModel(private val playlistId: Int) : ViewModel() {

    private val api = RetrofitClient.getApi()

    private val _detail = MutableLiveData<UiState<Playlist>>()
    val detail: LiveData<UiState<Playlist>> = _detail

    init { load() }

    fun load() {
        _detail.value = UiState.Loading
        viewModelScope.launch {
            runCatching { api.getPlaylist(id = playlistId) }
                .onSuccess { _detail.value = UiState.Success(it.playlist) }
                .onFailure { _detail.value = UiState.Error(it.message ?: "Error") }
        }
    }

    fun removeSong(songId: Int) {
        viewModelScope.launch {
            runCatching { api.removeSongFromPlaylist(playlistId = playlistId, songId = songId) }
                .onSuccess { if (it.success) load() }
        }
    }
}
