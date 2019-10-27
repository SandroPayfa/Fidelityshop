<?php
use  PHPMailer\PHPMailer\PHPMailer; 
require  'MAIL/Exception.php' ;
require 'MAIL/PHPMailer.php';
require 'MAIL/SMTP.php';

 class mailDefault 
    {
        public function send($to,$object,$body,$pieces = false)
        {
            $passage_ligne = !preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $to)?"\r\n":"\n";
            $boundary = "-----=".md5(rand());
    
            $header = "From: \"SPBusiness\"<noreply@etudierplus.com>".$passage_ligne;
            $header .= "MIME-Version: 1.0".$passage_ligne;
            $header .= 'Content-Type: multipart/related;boundary=' . $boundary . $passage_ligne;
    
            $message = $passage_ligne."--".$boundary.$passage_ligne;
    
            $message.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
            $message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
            $message.= $passage_ligne.$body.$passage_ligne;
    
            
            if ($pieces != '' && file_exists($pieces)) {
                $conAttached = self::prepareAttachment($pieces);
                if ($conAttached !== false) {
                    $message .= $passage_ligne . '--' . $boundary . $passage_ligne;
                    $message .= $conAttached;
                }
            }
    
            @mail($to,$object,$message,$header);
        }
        public static function prepareAttachment($path) {
            $rn = "\r\n";
    
            if (file_exists($path)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $ftype = finfo_file($finfo, $path);
                $file = fopen($path, "r");
                $attachment = fread($file, filesize($path));
                $attachment = chunk_split(base64_encode($attachment));
                fclose($file);
    
                $msg = 'Content-Type: \'' . $ftype . '\'; name="' . basename($path) . '"' . $rn;
                $msg .= "Content-Transfer-Encoding: base64" . $rn;
                $msg .= 'Content-ID: <' . basename($path) . '>' . $rn;
                $msg .= $rn . $attachment . $rn . $rn;
                return $msg;
            } else {
                return false;
            }
        }
        function sendSMTP($email,$subjet,$message,$attachment = false)
        {
            

           
            
            $mail = new PHPMailer;
            
            
            $mail->IsSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'SSL0.OVH.NET';                 // Specify main and backup server
            $mail->Port = 465;                                    // Set the SMTP port
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'dev@spbusiness.eu';                // SMTP username  noreply@quadcitytour.fr
            $mail->Password = 'g6jAUM4u5';                  // SMTP password   MailQuad2019
            $mail->SMTPSecure = 'ssl';                    // Enable encryption, 'ssl' also accepted
            
            $mail->From = 'info@fidelityshop.be';
            $mail->FromName = "FidelityShop.be";
            
            for ($i=0; $i < count($email); $i++) { $mail->AddAddress($email[$i]);}
            

              // Add a recipient
            if ($attachment) {
                for ($i=0; $i < count($attachment); $i++) { 
                    $mail->AddEmbeddedImage($attachment[$i][0],$attachment[$i][1],$attachment[$i][2]);
                }
            }
            $mail->IsHTML(true);                                  // Set email format to HTML
            
            $mail->Subject = $subjet;
            $mail->Body    = $message;
            
            
            $end = ($mail->Send())?true:false;
            
            return $end;
    }
    }
    
  
