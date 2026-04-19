package com.musicapp.ui.player

import android.os.Bundle
import android.view.*
import android.widget.SeekBar
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import com.bumptech.glide.Glide
import com.musicapp.R
import com.musicapp.data.network.RetrofitClient
import com.musicapp.databinding.FragmentPlayerBinding

class PlayerFragment : Fragment() {

    private var _binding: FragmentPlayerBinding? = null
    private val binding get() = _binding!!
    private val playerVm: PlayerViewModel by activityViewModels()

    private var isUserSeeking = false

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentPlayerBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        observePlayer()
        setupControls()
    }

    private fun observePlayer() {
        playerVm.currentSong.observe(viewLifecycleOwner) { song ->
            song ?: return@observe
            binding.tvTitle.text   = song.nombre
            binding.tvArtist.text  = song.artista
            binding.tvAlbum.text   = song.album ?: ""

            Glide.with(this)
                .load(buildImageUrl(song.imagenUrl))
                .placeholder(R.drawable.ic_music_note)
                .centerCrop()
                .into(binding.ivCover)
        }

        playerVm.isPlaying.observe(viewLifecycleOwner) { playing ->
            binding.btnPlayPause.setImageResource(
                if (playing) R.drawable.ic_pause else R.drawable.ic_play
            )
        }

        playerVm.progress.observe(viewLifecycleOwner) { progress ->
            if (!isUserSeeking) {
                binding.seekBar.progress = progress
            }
        }
    }

    private fun setupControls() {
        binding.btnPlayPause.setOnClickListener { playerVm.togglePlayPause() }
        binding.btnNext.setOnClickListener     { playerVm.playNext() }
        binding.btnPrev.setOnClickListener     { playerVm.playPrevious() }

        binding.seekBar.setOnSeekBarChangeListener(object : SeekBar.OnSeekBarChangeListener {
            override fun onProgressChanged(sb: SeekBar?, progress: Int, fromUser: Boolean) {
                if (fromUser) { /* preview */ }
            }
            override fun onStartTrackingTouch(sb: SeekBar?) { isUserSeeking = true }
            override fun onStopTrackingTouch(sb: SeekBar?) {
                isUserSeeking = false
                playerVm.seekTo(sb?.progress ?: 0)
            }
        })
    }

    private fun buildImageUrl(path: String?): String? {
        if (path.isNullOrBlank()) return null
        return if (path.startsWith("http")) path
        else RetrofitClient.BASE_URL.trimEnd('/') + "/" + path.trimStart('/')
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
