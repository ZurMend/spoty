package com.musicapp.data.network

import android.content.Context
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object RetrofitClient {

    // Emulador Android → 10.0.2.2 apunta a localhost de tu PC
    // Dispositivo físico en la misma red → "http://192.168.X.X/soundwave/"
    // Producción (Hostinger) → "https://tudominio.com/soundwave/"
    const val BASE_URL = "http://10.0.2.2/soundwave/"

    private var cookieJar: PersistentCookieJar? = null
    private var retrofit: Retrofit? = null

    fun init(context: Context) {
        cookieJar = PersistentCookieJar(context.applicationContext)

        val logging = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        }

        val client = OkHttpClient.Builder()
            .cookieJar(cookieJar!!)
            .addInterceptor(logging)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(60, TimeUnit.SECONDS)
            // Seguimos redirects: el login redirige a home.php o admin.php
            .followRedirects(true)
            .followSslRedirects(true)
            .build()

        retrofit = Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
    }

    fun getApi(): ApiService {
        return retrofit?.create(ApiService::class.java)
            ?: error("RetrofitClient no inicializado. Llama init() en Application.onCreate()")
    }

    fun clearCookies() = cookieJar?.clearAll()
}
