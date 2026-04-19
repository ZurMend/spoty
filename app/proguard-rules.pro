# Keep Retrofit and Gson models
-keepattributes Signature
-keepattributes *Annotation*
-keep class com.musicapp.data.models.** { *; }
-keep class retrofit2.** { *; }
-keep class okhttp3.** { *; }
-keep class com.google.gson.** { *; }
-dontwarn okhttp3.**
-dontwarn retrofit2.**
