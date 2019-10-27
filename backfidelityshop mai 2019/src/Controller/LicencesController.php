<?php

namespace App\Controller;
use App\Form\AddlicenceFormType;


use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Licence;
use App\Entity\Shop;
use App\Entity\User;

class LicencesController extends AbstractController
{
    /**
     * @Route("/licences", name="licences")
     * @param UserInterface $user
     * @param Request $request
     * @return
     */
    public function index(UserInterface $user,Request $request)
    {
        $renew=false;
        if ($request->isMethod('post')) {
            $licence = $this->getDoctrine()->getRepository(Licence::class)->findOneById($request->get('licence_id'));
            $licence->setExpiredAt(new \DateTime($request->get('date_fin')));
            $licence->setActivatedAt(new \DateTime($request->get('date_debut')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($licence);
            $entityManager->flush();
            $renew=true;
//            die(json_encode($request->get('date_fin')));
        }
    	$roles = $user->getRoles();
    	$licences_repo = $this->getDoctrine()->getRepository(Licence::class);

    	if(in_array('ROLE_ADMIN', $roles)) {
    		$licences = $licences_repo->findAll();
    	} else {
    		$licences = $user->getLicences();
    	}

    	$expired=array();

    	foreach ($licences as $licence){
    	    if($licence->getExpiredAt() != null){
                $day_diff=$licence->getExpiredAt()->getTimestamp()- (new \DateTime())->getTimestamp();
                $expired[$licence->getId()] = floor($day_diff / (60 * 60 * 24));
            }
            else{
                $expired[$licence->getId()]=0;
            }
        }

        return $this->render('licences/index.html.twig', [
            'licences' => $licences,
            'expired' => $expired,
            'renew'   => $renew
        ]);
    }

    /**
     * @Route("/licences/add", name="new_licence")
     * @param Request $request
     * @return
     */

    public function addli(Request $request)
    {
        $form_sent=false;
        $licence=new Licence();
        $form = $this->createForm(AddlicenceFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $licence->setSerial($this->generateLicence());
            $licence->setPeriode($form->get('periode')->getData());
//            $licence->setPin($form->get('pin')->getData());
            $licence->setPin($form->get('pin')->getData());
            $licence->setTerminalId($this->generateTerminalId());
            $licence->setCreatedAt(new \DateTime());
            $licence->setActived(0);
            $shop=$this->getDoctrine()->getRepository(Shop::class)->findOneById(
                $form->get('magasin')->getData()
            );
            $licence->setShop(
                $shop
            );
            $licence->setVendor(
                $shop->getUserId()
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($licence);
            $entityManager->flush();
            $form_sent = true;
        }

        return $this->render('licences/addlicence.html.twig', [
            'addlicenceForm' => $form->createView(),
            'form_sent' => $form_sent
        ]);
    }


    private function generateLicence(){
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        do{
            $randomLicence = '';
            for ($i = 1; $i <= 4; $i++) {
                for ($j = 0; $j < 5; $j++) {
                    $randomLicence .= $characters[rand(0, $charactersLength - 1)];
                }
                if($i<4)
                    $randomLicence .='-';
            }
            $licence_exist = $this->getDoctrine()->getRepository(Licence::class)->findBySerial($randomLicence);
        }while($licence_exist);
        return $randomLicence;
    }
    private function generateTerminalId(){
        $characters = '0123456789';
        $charactersLength = strlen($characters);
           do{
               $randomTerminalId = '';
               $randomTerminalId .= $characters[rand(1, $charactersLength - 1)];
                for ($j = 1; $j <= 3; $j++) {
                    $randomTerminalId .= $characters[rand(0, $charactersLength - 1)];
                }
             $licence_exist = $this->getDoctrine()
                 ->getRepository(Licence::class)->findOneBy(
                     array('terminal_id' => $randomTerminalId)
                 );

            }while($licence_exist);
        return $randomTerminalId;
    }


    /**
     * @Route("/licences/{licence}/change_status", name="change_status")
     * @param $licence
     * @return
     */
    public function activeLicence($licence) {
        $licence = $this->getDoctrine()
            ->getRepository(Licence::class)
            ->findOneById($licence);
        if($licence) {
            $licence->setActived(!$licence->getActived());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($licence);
            $entityManager->flush();
        }
        return $this->redirect($this->generateUrl('licences'));
    }

}
