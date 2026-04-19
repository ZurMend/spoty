package com.musicapp.ui.playlists

import android.os.Bundle
import android.view.*
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import androidx.fragment.app.viewModels
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import com.bumptech.glide.Glide
import com.musicapp.R
import com.musicapp.data.models.Song
import com.musicapp.data.models.UiState
import com.musicapp.data.network.RetrofitClient
import com.musicapp.databinding.FragmentPlaylistDetailBinding
import com.musicapp.ui.home.SongAdapter
import com.musicapp.ui.player.PlayerViewModel

class PlaylistDetailFragment : Fragment() {

    private var _binding: FragmentPlaylistDetailBinding? = null
    private val binding get() = _binding!!
    private val playerVm: PlayerViewModel by activityViewModels()

    private val playlistId: Int by lazy {
        arguments?.getInt("playlistId") ?: 0
    }

    private val vm: PlaylistDetailViewModel by viewModels {
        object : ViewModelProvider.Factory {
            override fun <T : ViewModel> create(modelClass: Class<T>): T {
                @Suppress("UNCHECKED_CAST")
                return PlaylistDetailViewModel(playlistId) as T
            }
        }
    }

    private lateinit var adapter: SongAdapter

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentPlaylistDetailBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = SongAdapter(
            onSongClick = { song ->
                val songs = (vm.detail.value as? UiState.Success)?.data?.songs ?: listOf(song)
                playerVm.playSong(song, songs)
            },
            onAddToPlaylist = { song -> confirmRemove(song) }
        )
        binding.recyclerSongs.adapter = adapter

        vm.detail.observe(viewLifecycleOwner) { state ->
            when (state) {
                is UiState.Loading -> binding.progressBar.visibility = View.VISIBLE
                is UiState.Success -> {
                    binding.progressBar.visibility = View.GONE
                    val p = state.data
                    binding.tvTitle.text = p.nombre
                    binding.tvDesc.text  = p.descripcion ?: ""
                    val url = p.portadaUrl?.let {
                        if (it.startsWith("http")) it
                        else RetrofitClient.BASE_URL.trimEnd('/') + "/" + it.trimStart('/')
                    }
                    Glide.with(this).load(url)
                        .placeholder(R.drawable.ic_music_note)
                        .into(binding.ivCover)
                    adapter.submitList(p.songs ?: emptyList())
                }
                is UiState.Error -> {
                    binding.progressBar.visibility = View.GONE
                    Toast.makeText(context, state.message, Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    private fun confirmRemove(song: Song) {
        com.google.android.material.dialog.MaterialAlertDialogBuilder(requireContext())
            .setTitle("Quitar de la playlist")
            .setMessage("¿Quitar \"${song.nombre}\"?")
            .setPositiveButton("Quitar") { _, _ -> vm.removeSong(song.id) }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
