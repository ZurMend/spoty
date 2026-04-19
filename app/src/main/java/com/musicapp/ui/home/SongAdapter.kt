package com.musicapp.ui.home

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.musicapp.R
import com.musicapp.data.models.Song
import com.musicapp.data.network.RetrofitClient
import com.musicapp.databinding.ItemSongBinding

class SongAdapter(
    private val onSongClick: (Song) -> Unit,
    private val onAddToPlaylist: (Song) -> Unit
) : ListAdapter<Song, SongAdapter.SongViewHolder>(DIFF) {

    inner class SongViewHolder(private val b: ItemSongBinding) :
        RecyclerView.ViewHolder(b.root) {

        fun bind(song: Song) {
            b.tvSongName.text   = song.nombre
            b.tvArtist.text     = song.artista
            b.tvDuration.text   = formatDuration(song.duracion)

            val imageUrl = buildImageUrl(song.imagenUrl)
            Glide.with(b.ivCover)
                .load(imageUrl)
                .placeholder(R.drawable.ic_music_note)
                .centerCrop()
                .into(b.ivCover)

            b.root.setOnClickListener { onSongClick(song) }
            b.btnMore.setOnClickListener { onAddToPlaylist(song) }
        }

        private fun formatDuration(seconds: Int): String {
            val m = seconds / 60
            val s = seconds % 60
            return "%d:%02d".format(m, s)
        }

        private fun buildImageUrl(path: String?): String? {
            if (path.isNullOrBlank()) return null
            return if (path.startsWith("http")) path
            else RetrofitClient.BASE_URL.trimEnd('/') + "/" + path.trimStart('/')
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): SongViewHolder {
        val binding = ItemSongBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return SongViewHolder(binding)
    }

    override fun onBindViewHolder(holder: SongViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    companion object {
        val DIFF = object : DiffUtil.ItemCallback<Song>() {
            override fun areItemsTheSame(a: Song, b: Song) = a.id == b.id
            override fun areContentsTheSame(a: Song, b: Song) = a == b
        }
    }
}
