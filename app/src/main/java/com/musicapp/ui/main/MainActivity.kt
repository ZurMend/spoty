package com.musicapp.ui.main

import android.content.Intent
import android.os.Bundle
import android.view.View
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.navigation.fragment.NavHostFragment
import androidx.navigation.ui.setupWithNavController
import com.bumptech.glide.Glide
import com.musicapp.R
import com.musicapp.data.SessionManager
import com.musicapp.data.network.RetrofitClient
import com.musicapp.databinding.ActivityMainBinding
import com.musicapp.ui.auth.LoginActivity
import com.musicapp.ui.player.PlayerViewModel

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    val playerViewModel: PlayerViewModel by viewModels()
    private lateinit var session: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        session = SessionManager(this)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupNavigation()
        setupMiniPlayer()
        playerViewModel.bindService(this)
    }

    private fun setupNavigation() {
        val navHost = supportFragmentManager
            .findFragmentById(R.id.nav_host_fragment) as NavHostFragment
        val navController = navHost.navController

        // Mostrar/ocultar tab Admin según rol
        binding.bottomNav.menu.findItem(R.id.nav_admin)?.isVisible = session.isAdmin()

        binding.bottomNav.setupWithNavController(navController)
    }

    private fun setupMiniPlayer() {
        playerViewModel.currentSong.observe(this) { song ->
            if (song == null) {
                binding.miniPlayer.visibility = View.GONE
                return@observe
            }
            binding.miniPlayer.visibility = View.VISIBLE
            binding.tvMiniTitle.text   = song.nombre
            binding.tvMiniArtist.text  = song.artista
            Glide.with(this)
                .load(buildImageUrl(song.imagenUrl))
                .placeholder(R.drawable.ic_music_note)
                .into(binding.ivMiniCover)
        }

        playerViewModel.isPlaying.observe(this) { playing ->
            binding.btnMiniPlayPause.setImageResource(
                if (playing) R.drawable.ic_pause else R.drawable.ic_play
            )
        }

        binding.btnMiniPlayPause.setOnClickListener {
            playerViewModel.togglePlayPause()
        }

        binding.btnMiniNext.setOnClickListener {
            playerViewModel.playNext()
        }

        // Tap en el mini player → abrir pantalla completa del reproductor
        binding.miniPlayer.setOnClickListener {
            val navHost = supportFragmentManager
                .findFragmentById(R.id.nav_host_fragment) as NavHostFragment
            navHost.navController.navigate(R.id.nav_player)
        }
    }

    private fun buildImageUrl(path: String?): String? {
        if (path.isNullOrBlank()) return null
        return if (path.startsWith("http")) path
        else RetrofitClient.BASE_URL.trimEnd('/') + "/" + path.trimStart('/')
    }

    fun logout() {
        playerViewModel.unbindService(this)
        RetrofitClient.clearCookies()
        session.clearSession()
        startActivity(Intent(this, LoginActivity::class.java))
        finish()
    }

    override fun onDestroy() {
        playerViewModel.unbindService(this)
        super.onDestroy()
    }
}
