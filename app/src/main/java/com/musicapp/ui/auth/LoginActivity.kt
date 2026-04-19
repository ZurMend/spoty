package com.musicapp.ui.auth

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.google.gson.Gson
import com.musicapp.data.SessionManager
import com.musicapp.data.network.RetrofitClient
import com.musicapp.databinding.ActivityLoginBinding
import com.musicapp.ui.main.MainActivity
import kotlinx.coroutines.launch

class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding
    private lateinit var session: SessionManager
    private val api get() = RetrofitClient.getApi()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        session = SessionManager(this)
        if (session.isLoggedIn()) { goToMain(); return }

        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        binding.btnLogin.setOnClickListener { doLogin() }
        binding.tvRegister.setOnClickListener {
            startActivity(Intent(this, RegisterActivity::class.java))
        }
    }

    private fun doLogin() {
        val email    = binding.etEmail.text.toString().trim()
        val password = binding.etPassword.text.toString()

        if (email.isEmpty() || password.isEmpty()) {
            Toast.makeText(this, "Completa todos los campos", Toast.LENGTH_SHORT).show()
            return
        }
        setLoading(true)

        lifecycleScope.launch {
            try {
                val response = api.login(email = email, password = password)
                val body     = response.body()?.string() ?: ""
                val finalUrl = response.raw().request.url.toString()

                // isApiRequest() detecta action= en POST → devuelve JSON (error o éxito)
                // Fallback: si OkHttp siguió un redirect → revisamos la URL final
                when {
                    body.trimStart().startsWith("{") -> {
                        val map = runCatching {
                            @Suppress("UNCHECKED_CAST")
                            Gson().fromJson(body, Map::class.java) as Map<String, Any>
                        }.getOrElse { emptyMap<String, Any>() }

                        if (map.containsKey("error")) {
                            Toast.makeText(this@LoginActivity,
                                "Correo o contraseña incorrectos", Toast.LENGTH_SHORT).show()
                        } else {
                            val role = (map["role"] as? String) ?: "user"
                            session.saveSession(0, email.substringBefore("@"), email, role)
                            goToMain()
                        }
                    }
                    finalUrl.contains("admin.php") -> {
                        session.saveSession(0, "Admin", email, "admin")
                        goToMain()
                    }
                    finalUrl.contains("home.php") -> {
                        session.saveSession(0, email.substringBefore("@"), email, "user")
                        goToMain()
                    }
                    finalUrl.contains("login.php") || response.code() == 401 -> {
                        Toast.makeText(this@LoginActivity,
                            "Correo o contraseña incorrectos", Toast.LENGTH_SHORT).show()
                    }
                    else -> {
                        session.saveSession(0, email.substringBefore("@"), email, "user")
                        goToMain()
                    }
                }
            } catch (e: Exception) {
                Toast.makeText(this@LoginActivity,
                    "No se pudo conectar: ${e.message}", Toast.LENGTH_LONG).show()
            } finally {
                setLoading(false)
            }
        }
    }

    private fun goToMain() {
        startActivity(Intent(this, MainActivity::class.java))
        finish()
    }

    private fun setLoading(loading: Boolean) {
        binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
        binding.btnLogin.isEnabled     = !loading
        binding.etEmail.isEnabled      = !loading
        binding.etPassword.isEnabled   = !loading
    }
}
