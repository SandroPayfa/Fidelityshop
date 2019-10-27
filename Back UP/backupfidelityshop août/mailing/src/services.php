<?php
    require "src/MailChimp.php";
    use \DrewM\MailChimp\MailChimp;
    
    $MailChimp = new MailChimp('62f1cf66aaa37181e0659eb57f3ac62e-us20');
    
    $result  =  $MailChimp -> get('lists');
    $list_id = $result["lists"][0]["id"];

   /* $subscriber_hash  =  $MailChimp -> subscriberHash ('ahomlanto.david35@gmail.com');
    $MailChimp -> delete ( "lists/$list_id/members/$subscriber_hash");

    /*$result  =  $MailChimp -> post ("lists/$list_id/members", ['email_address' => 'ahomlanto.david35@gmail.com','status' =>'subscribed',"merge_fields" => [
        "FNAME" => "",
        "LNAME" => "",
        "ADDRESS" => "",
        "PHONE" => "",
        "BIRTHDAY" => ""
    ]]);*/

    //print_r($result);