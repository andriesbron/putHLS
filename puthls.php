<?php

/* putHLS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * putHLS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with putHLS. If not, see <http://www.gnu.org/licenses/>.
 */


class putHLS
{
    private $args = array();
    private $request_uri;
    private $playlist_dir = "video_put_hls".DIRECTORY_SEPARATOR;
    private $video_name = "video.ts";
    private $putdata;
    private $fp_log;
    private $enable_logging = True;

    public function __construct($delete_history=true)
    {
    	// Create the resource directory if it does not yet exist
    	if (!file_exists($this->playlist_dir) && !is_dir($this->playlist_dir)) {
    		mkdir($this->playlist_dir, 0777, true);
    	}
      
    	if ($this->enable_logging) {
    		$this->fp_log = fopen("put_hls.log", "a+");
    		$this->log("*** Starting put_hls");
    	}
    	
    	if (!($this->putdata = fopen("php://input", "r"))) {
    		throw new Exception("Can't get PUT data.");
    		$this->log("Can't get PUT data", $type="ERROR");
    		
    		return False;
    	}
      
    	$this->request_uri = $_SERVER['REQUEST_URI'];
    	foreach ($_SERVER['argv'] as $a) {
            $expl = explode ("=",$a);
            $this->args [$expl[0]] = $expl[1];
        }
        
	/**
	 * @desc Next is to find out if the script is launched for a .ts file upload or a .m3u8 file upload.
	 */
        if (array_key_exists ("v", $this->args)) {
        	// A video file was given.
        	$this->video_name = $this->args["v"];
        	//$this->log("Processing video file ". $this->video_name);
        	if (strpos ($this->video_name, ".m3u8") !== False) {
	        	// This is the playlist call
	        	$this->writePlaylistFile();
	        	// @todo only cleanup if cleanhistory is set, should be in the session cookie.
	        	// @todo cleanup directory at start of streaming. use session variable? don't know if that is possible...
	        	$this->cleanupPlaylistDirectory();
        	} else if (strpos ($this->video_name,".ts") !== False) {
	        	// This is a video file call
	        	$this->writeVideoFile();
	        }
        }
        fclose ($this->putdata);
        if ($this->enable_logging) {
        	fclose($this->fp_log);
        }
        return True;
        
    }
    
    private function log($log, $type="INFO")
    {
    	if ($this->enable_logging) {
    		fwrite($this->fp_log, date("d-m-Y h:m:s  ").$type."  ".$log."\n");
    	}
    }
    
    public function args() 
    {
    	return $this->args;
    }
    
    /**
    * @todo Cleanup files from previous upload, tricky thing is how to detect it was a previous session.
    * @todo Or, just have the last 10 files stored is also an option.
    */
    public function cleanupPlaylistDirectory()
    {
    	//array_map('unlink', array_filter((array) glob($this->playlist_dir."*")));
    }
    
    public function writePlaylistFile ()
    {
    	$filename = $this->playlist_dir.$this->video_name;
    	$this->log("Writing playlistfile ". $filename);
    	$fp = fopen ($filename, "w");
    	// Playlist that requires to replace filenames.
    	$m3u8 ="";
    	while ($data = fread ($this->putdata, 2048)) {
    		$m3u8 .= $data;
    	}
    	// FFMPEG writes the filename literally unfortunately. So, remove the request string.
    	// @todo Make this generic, now this requires a formated ffmpeg call, namely that request uri is exactly followed by ?v=
    	$m3u8 = str_replace ("put.php?v=", "", $m3u8);
    	$this->log("Request uri ".$this->request_uri);
    	fwrite ($fp, $m3u8);
    	fclose ($fp);
    }
    
    public function writeVideoFile()
    {
    	$filename = $this->playlist_dir.$this->video_name;
    	$fp = fopen ($filename, "w");
    	while ($data = fread($this->putdata, 2048)) {
    		fwrite($fp, $data);
    	}
    	fclose ($fp);
    }
}

$hls = new putHLS();

?>
