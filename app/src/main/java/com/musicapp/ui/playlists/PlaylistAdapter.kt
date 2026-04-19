package com.musicapp.ui.playlists

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.musicapp.R
import com.musicapp.data.models.Playlist
import com.musicapp.data.network.RetrofitClient
import com.musicapp.databinding.ItemPlaylistBinding

class PlaylistAdapter(
    private val onClick: (Playlist) -> Unit,
    private val onDelete: (Playlist) -> Unit
) : ListAdapter<Playlist, PlaylistAdapter.ViewHolder>(DIFF) {

    inner class ViewHolder(private val b: ItemPlaylistBinding) :
        RecyclerView.ViewHolder(b.root) {
        fun bind(p: Playlist) {
            b.tvName.text   = p.nombre
            b.tvCount.text  = "${p.totalCanciones} canciones"
            b.tvDesc.text   = p.descripcion ?: ""

            val url = p.portadaUrl?.let {
                if (it.startsWith("http")) it
                else RetrofitClient.BASE_URL.trimEnd('/') + "/" + it.trimStart('/')
            }
            Glide.with(b.ivCover).load(url)
                .placeholder(R.drawable.ic_music_note).centerCrop().into(b.ivCover)

            b.root.setOnClickListener { onClick(p) }
            b.btnDelete.setOnClickListener { onDelete(p) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int) =
        ViewHolder(ItemPlaylistBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(h: ViewHolder, pos: Int) = h.bind(getItem(pos))

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Playlist>() {
            override fun areItemsTheSame(a: Playlist, b: Playlist) = a.id == b.id
            override fun areContentsTheSame(a: Playlist, b: Playlist) = a == b
        }
    }
}
