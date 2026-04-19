package com.musicapp.ui.home

import android.os.Bundle
import android.view.*
import android.widget.ArrayAdapter
import androidx.appcompat.widget.SearchView
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import androidx.fragment.app.viewModels
import com.musicapp.R
import com.musicapp.data.models.Song
import com.musicapp.data.models.UiState
import com.musicapp.databinding.FragmentHomeBinding
import com.musicapp.ui.main.MainActivity
import com.musicapp.ui.player.PlayerViewModel

class HomeFragment : Fragment() {

    private var _binding: FragmentHomeBinding? = null
    private val binding get() = _binding!!

    private val vm: HomeViewModel by viewModels()
    private val playerVm: PlayerViewModel by activityViewModels()
    private lateinit var adapter: SongAdapter

    private val genres = listOf(
        "Todos", "pop", "rock", "hip-hop", "electronica",
        "jazz", "clasica", "reggaeton", "metal", "latin",
        "r&b", "soul", "country", "blues", "indie"
    )

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentHomeBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        setupRecycler()
        setupGenreFilter()
        setupSearch()
        observeState()

        binding.swipeRefresh.setOnRefreshListener { vm.loadSongs() }

        // Menú con logout
        binding.toolbar.inflateMenu(R.menu.menu_home)
        binding.toolbar.setOnMenuItemClickListener { item ->
            if (item.itemId == R.id.action_logout) {
                (requireActivity() as MainActivity).logout()
                true
            } else false
        }
    }

    private fun setupRecycler() {
        adapter = SongAdapter(
            onSongClick = { song ->
                val allSongs = (vm.songs.value as? UiState.Success)?.data ?: listOf(song)
                playerVm.playSong(song, allSongs)
            },
            onAddToPlaylist = { song ->
                AddToPlaylistDialog(song.id).show(parentFragmentManager, "add_playlist")
            }
        )
        binding.recyclerSongs.adapter = adapter
    }

    private fun setupGenreFilter() {
        val spinnerAdapter = ArrayAdapter(
            requireContext(),
            android.R.layout.simple_spinner_item,
            genres
        ).also { it.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }

        binding.spinnerGenre.adapter = spinnerAdapter
        binding.spinnerGenre.setOnItemSelectedListener(
            object : android.widget.AdapterView.OnItemSelectedListener {
                override fun onItemSelected(p: android.widget.AdapterView<*>?, v: View?, pos: Int, id: Long) {
                    val genre = if (pos == 0) null else genres[pos]
                    vm.loadSongs(genre = genre)
                }
                override fun onNothingSelected(p: android.widget.AdapterView<*>?) {}
            }
        )
    }

    private fun setupSearch() {
        binding.searchView.setOnQueryTextListener(object : SearchView.OnQueryTextListener {
            override fun onQueryTextSubmit(query: String?) = false
            override fun onQueryTextChange(newText: String?): Boolean {
                vm.filterLocal(newText ?: "")
                return true
            }
        })
    }

    private fun observeState() {
        vm.songs.observe(viewLifecycleOwner) { state ->
            binding.swipeRefresh.isRefreshing = false
            when (state) {
                is UiState.Loading -> {
                    binding.progressBar.visibility = View.VISIBLE
                    binding.recyclerSongs.visibility = View.GONE
                    binding.tvEmpty.visibility = View.GONE
                }
                is UiState.Success -> {
                    binding.progressBar.visibility = View.GONE
                    if (state.data.isEmpty()) {
                        binding.recyclerSongs.visibility = View.GONE
                        binding.tvEmpty.visibility = View.VISIBLE
                    } else {
                        binding.recyclerSongs.visibility = View.VISIBLE
                        binding.tvEmpty.visibility = View.GONE
                        adapter.submitList(state.data)
                    }
                }
                is UiState.Error -> {
                    binding.progressBar.visibility = View.GONE
                    binding.tvEmpty.text = state.message
                    binding.tvEmpty.visibility = View.VISIBLE
                }
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
