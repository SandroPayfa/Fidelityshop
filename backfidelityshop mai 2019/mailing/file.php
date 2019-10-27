<?php

    $downl = downloadFile("file_produit","image/load/");
    if ($downl["action"]) {
//        $downl["name"] = addcslashes(file_get_contents("image/load/".$downl["name"]));
    }
    echo '<script>window.top.loadFile('.json_encode($downl).')</script>';
    

    function downloadFile($name,$path,$extensions_ok = array('jpg', 'png', 'gif', 'jpeg'),$nm = "")
    {
        $p = false;
        if (isset($_FILES[$name])){
            $tailleMax = 8000000;
            $profil = $_FILES[$name];
            $tailleOctet = $profil['size'];
            
            $extension = substr($profil['name'], strrpos($profil['name'], '.') + 1);
            $nm = empty($nm)?"file_".time():$nm;

            if(!in_array($extension, $extensions_ok)){$p = false;}
            elseif($tailleOctet > $tailleMax){$p = null;}
            else
            {
                $name_file_profil = $nm .'.' . $extension;
                $cheminServeur = $path.$name_file_profil;
                
                if (file_exists($cheminServeur)) {$cheminServeur = $path.$nm ."-".time().'.' . $extension;}

                if(move_uploaded_file($profil['tmp_name'], $cheminServeur))
                {
                    $name = $name_file_profil;
                    $p = true;
                }
                else{$p = false;}
            }
        }
        return ["name" => $name,"action" => $p];

    }