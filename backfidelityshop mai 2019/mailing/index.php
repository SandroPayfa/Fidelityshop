<?php

    require "lib/controllerZo.php";
    require "src/MailChimp.php";
    use \DrewM\MailChimp\MailChimp;

//    $id= $_GET['id'];
    $MailChimp = new MailChimp('62f1cf66aaa37181e0659eb57f3ac62e-us20');
        $view = new ControllerZo($_GET['id']);

    $query = explode("=",$_SERVER["QUERY_STRING"])[0];

    switch ($query) {
        case 'list':
            $content = "list";
            //$result  =  $MailChimp -> get('lists');
            break;
        case 'mail':
            $content = "mail service";
            $result = [];
            break;
        default:
            $content = "campagne";
            //$result  =  $MailChimp -> get('campaigns');

            break;
    }


    $index = $view -> get("html/index.html",["content" => $content]);

$view -> setView($index);
?>