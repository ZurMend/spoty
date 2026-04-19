package com.musicapp.ui.admin

import android.os.Bundle
import android.view.*
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.musicapp.data.models.UiState
import com.musicapp.data.models.User
import com.musicapp.databinding.FragmentAdminBinding

class AdminFragment : Fragment() {

    private var _binding: FragmentAdminBinding? = null
    private val binding get() = _binding!!
    private val vm: AdminViewModel by viewModels()
    private lateinit var adapter: UsersAdapter

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentAdminBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = UsersAdapter(
            onDelete = { user -> confirmDelete(user) },
            onToggleRole = { user ->
                val newRole = if (user.role == "admin") "user" else "admin"
                vm.changeRole(user.id, newRole)
            }
        )
        binding.recyclerUsers.adapter = adapter
        binding.swipeRefresh.setOnRefreshListener { vm.reload() }

        observeStats()
        observeUsers()
    }

    private fun observeStats() {
        vm.stats.observe(viewLifecycleOwner) { stats ->
            stats ?: return@observe
            binding.tvStatSongs.text     = stats.songs.toString()
            binding.tvStatUsers.text     = stats.users.toString()
            binding.tvStatPlays.text     = stats.plays.toString()
            binding.tvStatPlaylists.text = stats.playlists.toString()
        }
    }

    private fun observeUsers() {
        vm.users.observe(viewLifecycleOwner) { state ->
            binding.swipeRefresh.isRefreshing = false
            when (state) {
                is UiState.Loading -> binding.progressBar.visibility = View.VISIBLE
                is UiState.Success -> {
                    binding.progressBar.visibility = View.GONE
                    adapter.submitList(state.data)
                }
                is UiState.Error -> {
                    binding.progressBar.visibility = View.GONE
                    Toast.makeText(context, state.message, Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    private fun confirmDelete(user: User) {
        MaterialAlertDialogBuilder(requireContext())
            .setTitle("¿Eliminar usuario?")
            .setMessage("Se eliminará a ${user.nombre} (${user.email}). ¿Continuar?")
            .setPositiveButton("Eliminar") { _, _ -> vm.deleteUser(user.id) }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
