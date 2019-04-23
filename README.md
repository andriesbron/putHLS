# putHLS
Php script that handles an incoming ffmpeg (:bow:) HLS stream.

## Installation

Add this into apache httpd.conf:

```
<Directory />
Script PUT /put/puthls.php
</Directory>
```

Create a directory ```put``` in your httdocs directory and place there puthls.php.

If your server happens to operate on port 8888 you can use the following ffmpeg (:bow:) command to stream to your puthls script:

```
ffmpeg -y -r 30 -f avfoundation -i "0:0" -c:v libx264 -pix_fmt yuv420p -s 720x380 -start_number 0 -threads 25 -preset ultrafast -async 1 -hls_time 4 -hls_list_size 5 -use_localtime 1 -segment_format mpegts -hls_segment_filename "http://localhost:8888/put/puthls.php?v=video-%s.ts" -f hls -method PUT "http://localhost:8888/put/puthls.php?v=playlist.m3u8"
```

Create a webpage containing:

```
<video controls>
    <source src="http://localhost:8888/put/video_put_hls/playlist.m3u8" type="video/mp4">
</video>
```
You should now be viewing your livestream. If not, submit an issue for me to help you.
