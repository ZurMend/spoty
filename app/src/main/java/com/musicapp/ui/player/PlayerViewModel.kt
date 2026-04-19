package com.musicapp.ui.player

import android.app.Application
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.content.ServiceConnection
import android.os.IBinder
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import com.musicapp.data.models.Song
import com.musicapp.service.MusicService

/**
 * ViewModel compartido entre todos los fragmentos.
 * Contiene el estado de reproducción y se comunica con MusicService.
 */
class PlayerViewModel(application: Application) : AndroidViewModel(application) {

    private val _currentSong   = MutableLiveData<Song?>()
    val currentSong: LiveData<Song?> = _currentSong

    private val _isPlaying     = MutableLiveData(false)
    val isPlaying: LiveData<Boolean> = _isPlaying

    private val _progress      = MutableLiveData(0)
    val progress: LiveData<Int> = _progress          // 0-100

    private val _queue         = MutableLiveData<List<Song>>(emptyList())
    val queue: LiveData<List<Song>> = _queue

    private var musicService: MusicService? = null
    private var bound = false

    private val connection = object : ServiceConnection {
        override fun onServiceConnected(name: ComponentName, binder: IBinder) {
            val b = binder as MusicService.MusicBinder
            musicService = b.getService()
            bound = true

            // Observar estado del servicio
            musicService?.isPlaying?.observeForever { _isPlaying.postValue(it) }
            musicService?.currentSong?.observeForever { _currentSong.postValue(it) }
            musicService?.progress?.observeForever { _progress.postValue(it) }
        }

        override fun onServiceDisconnected(name: ComponentName) {
            bound = false
            musicService = null
        }
    }

    fun bindService(context: Context) {
        val intent = Intent(context, MusicService::class.java)
        context.startService(intent)
        context.bindService(intent, connection, Context.BIND_AUTO_CREATE)
    }

    fun unbindService(context: Context) {
        if (bound) {
            context.unbindService(connection)
            bound = false
        }
    }

    fun playSong(song: Song, queue: List<Song> = emptyList()) {
        _queue.value = queue.ifEmpty { listOf(song) }
        musicService?.playSong(song)
    }

    fun togglePlayPause() {
        musicService?.togglePlayPause()
    }

    fun playNext() {
        val currentQueue = _queue.value ?: return
        val currentIndex = currentQueue.indexOfFirst { it.id == _currentSong.value?.id }
        val nextIndex = currentIndex + 1
        if (nextIndex < currentQueue.size) {
            playSong(currentQueue[nextIndex], currentQueue)
        }
    }

    fun playPrevious() {
        val currentQueue = _queue.value ?: return
        val currentIndex = currentQueue.indexOfFirst { it.id == _currentSong.value?.id }
        val prevIndex = currentIndex - 1
        if (prevIndex >= 0) {
            playSong(currentQueue[prevIndex], currentQueue)
        }
    }

    fun seekTo(progress: Int) {
        musicService?.seekTo(progress)
    }

    override fun onCleared() {
        super.onCleared()
        musicService = null
    }
}
