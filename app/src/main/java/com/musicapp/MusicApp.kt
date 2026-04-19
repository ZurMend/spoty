package com.musicapp

import android.app.Application
import com.musicapp.data.network.RetrofitClient

class MusicApp : Application() {
    override fun onCreate() {
        super.onCreate()
        RetrofitClient.init(this)
    }
}
