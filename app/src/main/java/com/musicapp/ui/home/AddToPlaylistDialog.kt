package com.musicapp.ui.home

import android.app.Dialog
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.fragment.app.DialogFragment
import androidx.lifecycle.lifecycleScope
import com.musicapp.data.models.Playlist
import com.musicapp.data.network.RetrofitClient
import kotlinx.coroutines.launch

class AddToPlaylistDialog(private val songId: Int) : DialogFragment() {

    private val api = RetrofitClient.getApi()

    override fun onCreateDialog(savedInstanceState: Bundle?): Dialog {
        var playlists: List<Playlist> = emptyList()
        var names = arrayOf("Cargando…")

        val builder = AlertDialog.Builder(requireContext())
            .setTitle("Agregar a playlist")
            .setItems(names) { _, _ -> }
            .setNegativeButton("Cancelar", null)

        val dialog = builder.create()

        // Cargar playlists y reconstruir el diálogo
        lifecycleScope.launch {
            runCatching { api.listPlaylists() }.onSuccess { resp ->
                playlists = resp.playlists
                names = playlists.map { it.nombre }.toTypedArray()

                dialog.listView?.let { lv ->
                    lv.adapter = android.widget.ArrayAdapter(
                        requireContext(),
                        android.R.layout.simple_list_item_1,
                        names
                    )
                    lv.setOnItemClickListener { _, _, pos, _ ->
                        addSong(playlists[pos].id)
                        dialog.dismiss()
                    }
                }
            }.onFailure {
                Toast.makeText(context, "Error al cargar playlists", Toast.LENGTH_SHORT).show()
            }
        }

        return dialog
    }

    private fun addSong(playlistId: Int) {
        lifecycleScope.launch {
            runCatching {
                api.addSongToPlaylist(playlistId = playlistId, songId = songId)
            }.onSuccess { resp ->
                val msg = if (resp.success) "Canción agregada ✓" else "Ya está en la playlist"
                Toast.makeText(context, msg, Toast.LENGTH_SHORT).show()
            }.onFailure {
                Toast.makeText(context, "Error al agregar", Toast.LENGTH_SHORT).show()
            }
        }
    }
}
