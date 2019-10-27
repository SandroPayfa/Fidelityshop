<?php

namespace App\Controller;

header("Access-Control-Allow-Origin: *");

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \Ovh\Api;

use App\Entity\User;
use App\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;

class NewsController extends AbstractController
{

    private $entityManager;
    private $mailer;


    public function __construct(\Swift_Mailer $mailer, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->mailer=$mailer;

    }

    /**
     * @Route("/news", name="news")
     */
    public function index()
    {
//        $str = 'W29iamVjdCBIVE1MRWxlbWVudF0=';
//        echo base64_decode($str);
//        die();
        return $this->render('news/index.html.twig', [
            'controller_name' => 'NewsController',
            'var' => base64_decode("W29iamVjdCBIVE1MRWxlbWVudF0=")
        ]);
    }

    /**
     * @Route("/news/sms", name="news_sms")
     */
    public function indexSms()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user = $this->entityManager->getRepository(User::class)->findOneById($user->getId());
        $shops = $user->getShops();
        $params=array();

        foreach ($shops as $shop){
            $customers = $shop->getCustomers();
            foreach ($customers as $customer){
                $params[] = $customer->getTel();
            }
        }

        return $this->render('news/sms.html.twig', [
            'controller_name' => 'NewsController',
            'clients' => $params
        ]);
    }

    /**
     * @Route("/news/mail", name="news_mail")
     */
    public function indexMailing()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user = $this->entityManager->getRepository(User::class)->findOneById($user->getId());
        $shops = $user->getShops();
        $params=array();

        foreach ($shops as $shop){
            $customers = $shop->getCustomers();
            foreach ($customers as $customer){
                if($customer->getEmail())
                    $params[] = $customer->getEmail();
            }
        }

        return $this->render('news/mail.html.twig', [
            'controller_name' => 'NewsController',
            'clients' => $params
        ]);
    }

    /**
     * @Route("/news/sms/send", name="send_news_sms")
     * @param Request $request
     * @return JsonResponse
     */
    public function sendSms(Request $request)
    {
        $params = json_decode($request->getContent(), true);
        $smsContent = $params["sms_content"];
        $numbers = $params["numbers_list"];
        for ($i=0 ; $i < count($numbers) ;$i++){
            $numbers[$i] = "0032".ltrim($numbers[$i], "0");
        }
           $endpoint = 'ovh-eu';
           $applicationKey = "iPdOiLokOZkDjC7a";
           $applicationSecret = "Gr02s2DFOtfhbCk6yLWsobzGvuvS1h8w";
           $consumer_key = "xMxWu1MKhxLWsWJAZMuSzB6EKAA52QDD";

           $conn = new Api(    $applicationKey,
               $applicationSecret,
               $endpoint,
               $consumer_key);
           $smsServices = $conn->get('/sms/');
           $content = (object) array(
               "charset"=> "UTF-8",
               "class"=> "phoneDisplay",
               "coding"=> "7bit",
               "message"=> $smsContent,
               "noStopClause"=> false,
               "priority"=> "high",
               "receivers"=> $numbers,
               "senderForResponse"=> true,
               "validityPeriod"=> 2880
           );
           $resultPostJob  = $conn->post('/sms/'. $smsServices[0] . '/jobs/', $content);
           $smsJobs = $conn->get('/sms/'. $smsServices[0] . '/jobs/');

        return new JsonResponse(['data' => $resultPostJob]);
    }

    /**
     * @Route("/news/mails/send", name="send_mails")
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMails(Request $request)
    {
        $params = json_decode($request->getContent(), true);
        $mailObject = $params["mail_object"];
        $mailContent = $params["mail_content"];
        //print_r($mailContent);
       // die();
        $emails_list = $params["emails_list"];
      //  $mailContent = str_replace('"',"'",$mailContent);
       $emails_list[] = 'user.hr6@gail.com';
        $result="";
        foreach ($emails_list as $email) {
            try {
                $result = $this->sendMail($mailObject, $mailContent,$email );
            } catch (Exception $e) {
                return new JsonResponse(['data' => "cant_send_Mail"]);
            }
        }
        return new JsonResponse(['data' => $mailContent]);
    }

    private function sendMail($object, $content, $email)
    {
        $message = (new \Swift_Message($object))
            ->setFrom('no-reply@spbusiness.eu')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    'news/mail_custom_template.html.twig',
                    [
                        'content' => implode("",$content)
                    ]
                ),
                'text/html'
            );

        $mailer = $this->mailer->send($message);
        return $mailer;
    }

}