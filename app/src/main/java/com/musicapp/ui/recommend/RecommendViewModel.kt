package com.musicapp.ui.recommend

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.musicapp.R
import com.musicapp.data.models.Recommendation
import com.musicapp.data.models.Song
import com.musicapp.data.models.UiState
import com.musicapp.data.network.RetrofitClient
import com.musicapp.databinding.ItemRecommendBinding
import kotlinx.coroutines.launch

// ─── Estado UI ────────────────────────────────────────────────────────────────

data class RecommendState(
    val loading: Boolean = false,
    val items: List<Recommendation> = emptyList(),
    val genre: String = "",
    val error: String? = null
)

// ─── ViewModel ────────────────────────────────────────────────────────────────

class RecommendViewModel : ViewModel() {

    private val api = RetrofitClient.getApi()

    private val _state = MutableLiveData(RecommendState())
    val state: LiveData<RecommendState> = _state

    fun loadByGenre(genre: String) {
        _state.value = RecommendState(loading = true, genre = genre)
        viewModelScope.launch {
            runCatching { api.recommendByGenre(genre = genre) }
                .onSuccess { resp ->
                    _state.value = RecommendState(
                        items = resp.recommendations,
                        genre = resp.genre ?: genre
                    )
                }
                .onFailure { e ->
                    _state.value = RecommendState(error = e.message, genre = genre)
                }
        }
    }

    /** Busca en el catálogo propio y devuelve la canción si existe */
    fun findAndPlay(nombre: String, artista: String, callback: (Song?) -> Unit) {
        viewModelScope.launch {
            runCatching { api.listSongs(search = nombre) }
                .onSuccess { resp ->
                    val match = resp.songs.firstOrNull {
                        it.nombre.equals(nombre, ignoreCase = true) ||
                        it.artista.equals(artista, ignoreCase = true)
                    }
                    callback(match)
                }
                .onFailure { callback(null) }
        }
    }
}

// ─── Adapter ──────────────────────────────────────────────────────────────────

class RecommendAdapter(
    private val onPlay: (Recommendation) -> Unit
) : ListAdapter<Recommendation, RecommendAdapter.VH>(DIFF) {

    inner class VH(private val b: ItemRecommendBinding) : RecyclerView.ViewHolder(b.root) {
        fun bind(r: Recommendation) {
            b.tvName.text   = r.nombre
            b.tvArtist.text = r.artista
            Glide.with(b.ivCover).load(r.imagen)
                .placeholder(R.drawable.ic_music_note).centerCrop().into(b.ivCover)
            b.root.setOnClickListener { onPlay(r) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int) =
        VH(ItemRecommendBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(h: VH, pos: Int) = h.bind(getItem(pos))

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Recommendation>() {
            override fun areItemsTheSame(a: Recommendation, b: Recommendation) =
                a.nombre == b.nombre && a.artista == b.artista
            override fun areContentsTheSame(a: Recommendation, b: Recommendation) = a == b
        }
    }
}
