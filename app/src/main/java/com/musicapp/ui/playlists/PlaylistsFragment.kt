package com.musicapp.ui.playlists

import android.os.Bundle
import android.view.*
import android.widget.Toast
import androidx.core.os.bundleOf
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.navigation.fragment.findNavController
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.android.material.textfield.TextInputEditText
import com.musicapp.R
import com.musicapp.data.models.Playlist
import com.musicapp.data.models.UiState
import com.musicapp.databinding.FragmentPlaylistsBinding

class PlaylistsFragment : Fragment() {

    private var _binding: FragmentPlaylistsBinding? = null
    private val binding get() = _binding!!
    private val vm: PlaylistsViewModel by viewModels()
    private lateinit var adapter: PlaylistAdapter

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentPlaylistsBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        setupRecycler()
        observeState()
        binding.fabCreate.setOnClickListener { showCreateDialog() }
        binding.swipeRefresh.setOnRefreshListener { vm.loadPlaylists() }
    }

    private fun setupRecycler() {
        adapter = PlaylistAdapter(
            onClick = { playlist ->
                findNavController().navigate(
                    R.id.nav_playlist_detail,
                    bundleOf("playlistId" to playlist.id)
                )
            },
            onDelete = { playlist -> confirmDelete(playlist) }
        )
        binding.recyclerPlaylists.adapter = adapter
    }

    private fun observeState() {
        vm.playlists.observe(viewLifecycleOwner) { state ->
            binding.swipeRefresh.isRefreshing = false
            when (state) {
                is UiState.Loading -> {
                    binding.progressBar.visibility   = View.VISIBLE
                    binding.tvEmpty.visibility       = View.GONE
                }
                is UiState.Success -> {
                    binding.progressBar.visibility   = View.GONE
                    if (state.data.isEmpty()) {
                        binding.tvEmpty.visibility          = View.VISIBLE
                        binding.recyclerPlaylists.visibility = View.GONE
                    } else {
                        binding.tvEmpty.visibility          = View.GONE
                        binding.recyclerPlaylists.visibility = View.VISIBLE
                        adapter.submitList(state.data)
                    }
                }
                is UiState.Error -> {
                    binding.progressBar.visibility   = View.GONE
                    Toast.makeText(context, state.message, Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    private fun showCreateDialog() {
        val view     = layoutInflater.inflate(R.layout.dialog_create_playlist, null)
        val etNombre = view.findViewById<TextInputEditText>(R.id.etNombre)
        val etDesc   = view.findViewById<TextInputEditText>(R.id.etDescripcion)

        MaterialAlertDialogBuilder(requireContext())
            .setTitle("Nueva playlist")
            .setView(view)
            .setPositiveButton("Crear") { _, _ ->
                val nombre = etNombre.text.toString().trim()
                val desc   = etDesc.text.toString().trim()
                if (nombre.isBlank()) {
                    Toast.makeText(context, "Escribe un nombre", Toast.LENGTH_SHORT).show()
                    return@setPositiveButton
                }
                vm.createPlaylist(nombre, desc)
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun confirmDelete(playlist: Playlist) {
        MaterialAlertDialogBuilder(requireContext())
            .setTitle("¿Eliminar \"${playlist.nombre}\"?")
            .setMessage("Esta acción no se puede deshacer.")
            .setPositiveButton("Eliminar") { _, _ -> vm.deletePlaylist(playlist.id) }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
