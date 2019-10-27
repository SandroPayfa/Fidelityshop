<?php

class  ControllerZo
{
    private $id;
    public function __construct($id=2)
    {
        $this->id=$id;
    }

    public function getEmails()
    {
        $ch = curl_init('http://fidelityshop.be/connexion/public/database/'.$this->id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: applicatio/json'
        ));
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function editView($po, $p)
    {
        $etat = false;
        if (count($po) > 0) {
            foreach ($po as $inHtml => $php) {
                $p = str_ireplace("{{" . $inHtml . "}}", $php, $p);
            }
            $etat = $p;
        }
        return $etat;
    }

    public function get($l, $edi)
    {
        if ($content = file_get_contents($l)) {
            if (!$edi) return $content;
            if (count($edi) > 0) {
                foreach ($edi as $inHtml => $php) {
                    $content = str_ireplace("{{" . $inHtml . "}}", $php, $content);
                }

                $users = $this->getEmails();
                $users = json_decode($users,true);
                $emails = '';
                $users = (gettype($users)==="array")?$users:array();
                for($i=0;$i<count($users);$i++){
                    if($users[$i]!= "" && $users[$i]!= null)
                      $emails = $emails . $users[$i] . ';';
                }

                $content = str_ireplace("{{emails}}", $emails, $content);
                return $content;
            }
        }
        return false;
    }

    public function setView($l = "")
    {
        print $l;
    }
}