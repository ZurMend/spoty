package com.musicapp.service

import android.app.Notification
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.app.Service
import android.content.Intent
import android.os.Binder
import android.os.Build
import android.os.IBinder
import androidx.core.app.NotificationCompat
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.media3.common.MediaItem
import androidx.media3.common.Player
import androidx.media3.exoplayer.ExoPlayer
import com.musicapp.R
import com.musicapp.data.models.Song
import com.musicapp.data.network.RetrofitClient
import com.musicapp.ui.main.MainActivity
import kotlinx.coroutines.*

class MusicService : Service() {

    inner class MusicBinder : Binder() {
        fun getService(): MusicService = this@MusicService
    }

    private val binder = MusicBinder()
    private lateinit var player: ExoPlayer
    private var progressJob: Job? = null
    private val serviceScope = CoroutineScope(Dispatchers.Main + SupervisorJob())

    private val _currentSong = MutableLiveData<Song?>()
    val currentSong: LiveData<Song?> = _currentSong

    private val _isPlaying = MutableLiveData(false)
    val isPlaying: LiveData<Boolean> = _isPlaying

    private val _progress = MutableLiveData(0)
    val progress: LiveData<Int> = _progress

    companion object {
        private const val CHANNEL_ID   = "music_channel"
        private const val NOTIFICATION_ID = 1
    }

    override fun onCreate() {
        super.onCreate()
        createNotificationChannel()

        player = ExoPlayer.Builder(this).build().apply {
            addListener(object : Player.Listener {
                override fun onIsPlayingChanged(isPlaying: Boolean) {
                    _isPlaying.postValue(isPlaying)
                    if (isPlaying) startProgressTracking() else stopProgressTracking()
                    updateNotification()
                }
                override fun onPlaybackStateChanged(playbackState: Int) {
                    if (playbackState == Player.STATE_ENDED) {
                        _isPlaying.postValue(false)
                        registerPlayToServer()
                    }
                }
            })
        }
    }

    override fun onBind(intent: Intent?): IBinder = binder

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        startForeground(NOTIFICATION_ID, buildNotification())
        return START_STICKY
    }

    fun playSong(song: Song) {
        val url = buildAudioUrl(song.archivoUrl)
        if (url.isNullOrBlank()) return

        _currentSong.postValue(song)
        player.setMediaItem(MediaItem.fromUri(url))
        player.prepare()
        player.play()
        updateNotification()
        registerPlayToServer(song.id)
    }

    fun togglePlayPause() {
        if (player.isPlaying) player.pause() else player.play()
    }

    fun seekTo(progressPercent: Int) {
        val duration = player.duration
        if (duration > 0) {
            player.seekTo((duration * progressPercent / 100L))
        }
    }

    private fun buildAudioUrl(archivoUrl: String?): String? {
        if (archivoUrl.isNullOrBlank()) return null
        return if (archivoUrl.startsWith("http")) archivoUrl
        else RetrofitClient.BASE_URL.trimEnd('/') + "/" + archivoUrl.trimStart('/')
    }

    private fun startProgressTracking() {
        progressJob?.cancel()
        progressJob = serviceScope.launch {
            while (true) {
                val duration = player.duration
                val position = player.currentPosition
                if (duration > 0) {
                    _progress.postValue((position * 100 / duration).toInt())
                }
                delay(500)
            }
        }
    }

    private fun stopProgressTracking() {
        progressJob?.cancel()
    }

    private fun registerPlayToServer(songId: Int? = _currentSong.value?.id) {
        songId ?: return
        serviceScope.launch {
            runCatching { RetrofitClient.getApi().registerPlay(songId = songId) }
        }
    }

    // ─── Notificación ────────────────────────────────────────────────────

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID, "Reproducción de música",
                NotificationManager.IMPORTANCE_LOW
            ).apply { description = "Controles de reproducción" }
            getSystemService(NotificationManager::class.java)
                .createNotificationChannel(channel)
        }
    }

    private fun updateNotification() {
        val manager = getSystemService(NotificationManager::class.java)
        manager.notify(NOTIFICATION_ID, buildNotification())
    }

    private fun buildNotification(): Notification {
        val song = _currentSong.value
        val pendingIntent = PendingIntent.getActivity(
            this, 0,
            Intent(this, MainActivity::class.java),
            PendingIntent.FLAG_IMMUTABLE
        )

        return NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle(song?.nombre ?: "MusicApp")
            .setContentText(song?.artista ?: "")
            .setSmallIcon(R.drawable.ic_music_note)
            .setContentIntent(pendingIntent)
            .setOngoing(true)
            .setSilent(true)
            .build()
    }

    override fun onDestroy() {
        serviceScope.cancel()
        player.release()
        super.onDestroy()
    }
}
