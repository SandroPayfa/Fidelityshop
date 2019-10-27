<?php
    if (isset($_GET["f"]) && !empty($_GET["f"])) {
        $filename = "image/load/".str_replace("-",".",$_GET["f"]);
        $handle = fopen($filename, "rb");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        
        header("content-type: image/jpeg");
        
        echo $contents;
    }
   