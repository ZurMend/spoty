package com.musicapp.ui.admin

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.musicapp.data.models.StatsResponse
import com.musicapp.data.models.UiState
import com.musicapp.data.models.User
import com.musicapp.data.network.RetrofitClient
import com.musicapp.databinding.ItemUserBinding
import kotlinx.coroutines.launch

// ─── ViewModel ────────────────────────────────────────────────────────────────

class AdminViewModel : ViewModel() {

    private val api = RetrofitClient.getApi()

    private val _stats = MutableLiveData<StatsResponse?>()
    val stats: LiveData<StatsResponse?> = _stats

    private val _users = MutableLiveData<UiState<List<User>>>()
    val users: LiveData<UiState<List<User>>> = _users

    init { reload() }

    fun reload() {
        loadStats()
        loadUsers()
    }

    private fun loadStats() {
        viewModelScope.launch {
            runCatching { api.getStats() }.onSuccess { _stats.value = it }
        }
    }

    private fun loadUsers() {
        _users.value = UiState.Loading
        viewModelScope.launch {
            runCatching { api.getUsers() }
                .onSuccess { _users.value = UiState.Success(it.users) }
                .onFailure { _users.value = UiState.Error(it.message ?: "Error") }
        }
    }

    fun deleteUser(id: Int) {
        viewModelScope.launch {
            runCatching { api.deleteUser(id = id) }
                .onSuccess { if (it.success) reload() }
        }
    }

    fun changeRole(id: Int, role: String) {
        viewModelScope.launch {
            runCatching { api.changeRole(id = id, role = role) }
                .onSuccess { if (it.success) reload() }
        }
    }
}

// ─── Adapter ──────────────────────────────────────────────────────────────────

class UsersAdapter(
    private val onDelete: (User) -> Unit,
    private val onToggleRole: (User) -> Unit
) : ListAdapter<User, UsersAdapter.VH>(DIFF) {

    inner class VH(private val b: ItemUserBinding) : RecyclerView.ViewHolder(b.root) {
        fun bind(u: User) {
            b.tvName.text  = u.nombre
            b.tvEmail.text = u.email
            b.tvRole.text  = u.role.uppercase()
            b.btnDelete.setOnClickListener     { onDelete(u) }
            b.btnToggleRole.setOnClickListener { onToggleRole(u) }
            b.btnToggleRole.text = if (u.role == "admin") "→ User" else "→ Admin"
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int) =
        VH(ItemUserBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(h: VH, pos: Int) = h.bind(getItem(pos))

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<User>() {
            override fun areItemsTheSame(a: User, b: User) = a.id == b.id
            override fun areContentsTheSame(a: User, b: User) = a == b
        }
    }
}
