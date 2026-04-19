package com.musicapp.ui.recommend

import android.os.Bundle
import android.view.*
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import androidx.fragment.app.viewModels
import com.musicapp.databinding.FragmentRecommendBinding
import com.musicapp.ui.player.PlayerViewModel

class RecommendFragment : Fragment() {

    private var _binding: FragmentRecommendBinding? = null
    private val binding get() = _binding!!
    private val vm: RecommendViewModel by viewModels()
    private val playerVm: PlayerViewModel by activityViewModels()
    private lateinit var adapter: RecommendAdapter

    private val genres = listOf(
        "pop","rock","hip-hop","electronica","jazz",
        "clasica","reggaeton","metal","latin","r&b",
        "soul","country","blues","indie"
    )

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentRecommendBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        adapter = RecommendAdapter { rec ->
            vm.findAndPlay(rec.nombre, rec.artista) { song ->
                if (song != null) {
                    playerVm.playSong(song)
                } else {
                    Toast.makeText(context,
                        "\"${rec.nombre}\" no está en tu catálogo aún",
                        Toast.LENGTH_SHORT).show()
                }
            }
        }
        binding.recyclerRecommend.adapter = adapter

        val spinnerAdapter = ArrayAdapter(
            requireContext(), android.R.layout.simple_spinner_item, genres
        ).also { it.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
        binding.spinnerGenre.adapter = spinnerAdapter

        binding.btnRecommend.setOnClickListener {
            val genre = genres[binding.spinnerGenre.selectedItemPosition]
            vm.loadByGenre(genre)
        }

        vm.loadByGenre(genres[0])

        vm.state.observe(viewLifecycleOwner) { state ->
            binding.progressBar.visibility =
                if (state.loading) View.VISIBLE else View.GONE
            binding.recyclerRecommend.visibility =
                if (!state.loading && state.error == null) View.VISIBLE else View.GONE

            if (!state.loading) {
                binding.tvGenreLabel.text = "Recomendado · ${state.genre}"
                adapter.submitList(state.items)
                state.error?.let {
                    Toast.makeText(context, "Error: $it", Toast.LENGTH_SHORT).show()
                }
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
