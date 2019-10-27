<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "src/SendMail.php";
require "lib/controllerZo.php";


$a = ["end" => false, "name" => ""];

$d = $_SERVER["SERVER_NAME"];
if (
    isset($_POST["template"]) &&
    isset($_POST["bg"]) &&
    isset($_POST["dst"]) &&
    isset($_POST["obj"]) &&
    !empty($_POST["dst"]) &&
    !empty($_POST["obj"])

) {
    $view = new ControllerZo();
    $Mail = new mailDefault();
    $dst = $_POST["dst"];
    $obj = utf8_decode($_POST["obj"]);

    $template = $_POST["template"];
    $filesMail = [];
    if (isset($_POST["allImg"])) {
        $allImg = $_POST["allImg"];
        foreach ($allImg as $name => $source) {
            $template = str_replace($source, "cid:" . $name, $template);
            $fileName = explode("/", $source);
            $fileName = $fileName[count($fileName) - 1];
            $filesMail[] = [$source, $name, $fileName];
        }
    }


    $tmplDefault = str_replace("contenteditable", "data", $view->get("tmpl/df.html", ["content" => $template, "bg" => $_POST["bg"]]));

    /* $data = $tmplDefault;
     $my_file = 'template'.time().'.html';
     $handle = fopen("tmpl/create/".$my_file, 'w');
     fwrite($handle, $data);
     fclose($handle);*/

    $dts = explode(";", $dst);
    for ($i = 0; $i < count($dts); $i++) {
        $mailTmp = $tmplDefault;
        if (preg_match("#^[a-z0-9._-]+@(gmail).[a-z]{2,4}$#", $dts[$i])) {
            $mailTmp = utf8_encode($mailTmp);
        }
        $mailTmp = utf8_decode($mailTmp);
        $Mail->sendSMTP([$dts[$i]], $obj, $mailTmp, $filesMail);

    }

    $a["end"] = true;
} else if (
    isset($_POST["firstname"]) &&
    isset($_POST["lastname"]) &&
    isset($_POST["email"])
){
    
    $view = new ControllerZo();
    $Mail = new mailDefault();
    $dst = $_POST["email"].';';
    $obj = utf8_decode('inscription');

    $template = '<html><head></head><body><h3>Bonjour '.$_POST["firstname"].' '.$_POST["lastname"].'</h3><p>Nous vous souhaitons la bienvenue parmi communauté <a>FideletyShop.be</a></p></body></html>';
    //$_POST["template"];
    $filesMail = [];
    if (isset($_POST["allImg"])) {
        $allImg = $_POST["allImg"];
        foreach ($allImg as $name => $source) {
            $template = str_replace($source,"cid:".$name,$template);
            $fileName = explode("/",$source);
            $fileName = $fileName[count($fileName)-1];
            $filesMail[] = [$source,$name,$fileName];
        }
    }



    $tmplDefault = str_replace("contenteditable","data",$view -> get("tmpl/df.html",["content" => $template,"bg" => 'transparent']));

    /* $data = $tmplDefault;
     $my_file = 'template'.time().'.html';
     $handle = fopen("tmpl/create/".$my_file, 'w');
     fwrite($handle, $data);
     fclose($handle);*/

    $dts = explode(";",$dst);
    for ($i=0; $i < count($dts); $i++) {
        $mailTmp = $tmplDefault;
        if (preg_match("#^[a-z0-9._-]+@(gmail).[a-z]{2,4}$#", $dts[$i])) {
            $mailTmp = utf8_encode($mailTmp);
        }
        $mailTmp = utf8_decode($mailTmp);
        $Mail -> sendSMTP([$dts[$i]],$obj,$mailTmp,$filesMail);

    }

    $a["end"] = true;
//        }

    die( json_encode($a));
}

die( json_encode($a));