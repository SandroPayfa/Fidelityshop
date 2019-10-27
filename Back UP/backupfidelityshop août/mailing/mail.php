

<?php
    //require_once('mailchimpint/mcapi/inc/MCAPI.class.php');
    $apikey = '62f1cf66aaa37181e0659eb57f3ac62e-us20';

    $to_emails = array('piolola23@gmail.com');
    $to_names = array('Name 1', 'Name 2');

    $message = array(
        'html'=>'Yo, this is the <b>html</b> portion',
        'text'=>'Yo, this is the *text* portion',
        'subject'=>'This is the subject',
        'from_name'=>'Me!',
        'from_email'=>'noreplay@gmail.com',
        'to_email'=>$to_emails,
        'to_name'=>$to_names
    );

    $tags = array('WelcomeEmail');

    $params = array(
        'apikey'=>$apikey,
        'message'=>$message,
        'track_opens'=>true,
        'track_clicks'=>false,
        'tags'=>$tags
    );

    $url = "http://us20.sts.mailchimp.com/1.0/SendEmail";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    echo $result;
    curl_close ($ch);
    var_dump($result);
    $data = json_decode($result);

    var_dump($data);
 ?>

