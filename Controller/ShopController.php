<?php

namespace App\Controller;

use http\Env\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\RedirectResponse;


use App\Form\EditShopFormType;
use App\Form\EditPointsFormType;
use App\Form\EditReductionFormType;
use App\Form\EditShopFormTypeMore;

use App\Entity\Transactions;
use App\Entity\Shop;
use App\Entity\Licence;
use App\Entity\User;

class ShopController extends AbstractController

{
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    /**
     * @Route("/shop/add", name="new_shop")
     * @param Request $request
     * @return RedirectResponse
     * @internal param Request $request
     */
    public function addShop(Request $request){
        $upd_shop = new Shop();
        $upd_shop_more = $this->getDoctrine()->getRepository(Shop::class)->findById($upd_shop->getId());
        if (isset($upd_shop_more[0])) $upd_shop_more = $upd_shop_more[0];
        $edit_shop_form = $this->createForm(EditShopFormTypeMore::class, $upd_shop);
        $edit_shop_form->handleRequest($request);

        if ($edit_shop_form->isSubmitted() && $edit_shop_form->isValid()) {

            $file = $upd_shop->getImage();
            if($file !== null){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
                $upd_shop->setImage($fileName);
            }
            $user_id  = $request->get('user_id')?$request->get('user_id') : $this->get('security.token_storage')->getToken()->getUser()->getId();


            $upd_shop->setName($edit_shop_form->get('name')->getData());
            $upd_shop->setAddress($edit_shop_form->get('address')->getData());
            $upd_shop->setCity($edit_shop_form->get('city')->getData());
            $upd_shop->setZip($edit_shop_form->get('zip')->getData());
            $upd_shop->setPays($edit_shop_form->get('pays')->getData());
            $upd_shop->setUserId($this->getDoctrine()->getRepository(User::class)->findOneById(
                $user_id
            ));

            $image = $upd_shop->getImage();

            if($file===null)
                $upd_shop->setImage($image);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_shop);
            $entityManager->flush();

           return new RedirectResponse(
               $this->container->get('router')->generate('update_shop', ['id'=>$upd_shop->getId()]));
        }

            $em = $this->getDoctrine()->getManager();

            $RAW_QUERY = "select * from user where roles like '%ROLE_VENDOR%' ";
            $statement = $em->getConnection()->prepare($RAW_QUERY);
            $statement->execute();
            $vendors=$statement->fetchall();


        return $this->render('shop/add_shop.html.twig', [
            'shop' => $upd_shop,
            'vendors' => $vendors,
            'edit_shop_form' => $edit_shop_form->createView(),
        ]);
    }

    /**
     * @Route("/shop/{id}/delete", name="delete_shop")
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function deleteShop(Request $request,$id){
        $shop = $this->getDoctrine()->getRepository(Shop::class)->findOneById($id);

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($shop);
        $em->flush();
        return new RedirectResponse(
            $this->container->get('router')->generate('shop', []));
    }

    /**
     * @Route("/shop/{id}/update", name="update_shop")
     * @param Request $request
     * @param $id
     * @return
     */
    public function updateShop(Request $request,$id){
        $form_sent = false;

        if ($request->isMethod('post') && $request->get('pin') && $request->get('licence_id')) {
            $licence = $this->getDoctrine()->getRepository(Licence::class)->findOneById($request->get('licence_id'));
            $licence->setPin($request->get('pin'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($licence);
            $entityManager->flush();
            $form_sent=true;
        }

        $shop = $this->getDoctrine()->getRepository(Shop::class)->findById(intval($id));
        $day_horaires=json_decode($shop[0]->getHoraires());
        $upd_shop = $this->getDoctrine()->getRepository(Shop::class)->findById($id);
        $image = $upd_shop[0]->getImage();
        if (isset($upd_shop[0])) $upd_shop = $upd_shop[0];
        $edit_shop_form = $this->createForm(EditShopFormType::class, $upd_shop);
        $edit_shop_form->handleRequest($request);
        if ($edit_shop_form->isSubmitted() && $edit_shop_form->isValid()) {

            $file = $upd_shop->getImage();
            if($file !== null){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
                $upd_shop->setImage($fileName);
            }

            $upd_shop->setName($edit_shop_form->get('name')->getData());
            $upd_shop->setAddress($edit_shop_form->get('address')->getData());
            $upd_shop->setCity($edit_shop_form->get('city')->getData());
            $upd_shop->setZip($edit_shop_form->get('zip')->getData());
            $upd_shop->setPays($edit_shop_form->get('pays')->getData());

            $roles = $this->get('security.token_storage')->getToken()->getUser()->getRoles();

            if(in_array('ROLE_ADMIN', $roles))
            $upd_shop->setUserId($this->getDoctrine()->getRepository(User::class)->findOneById(
                $request->get('user_id'))
            );

            /******************************begin Horraires****************************************/

            $days= ["lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche"];
            $horraires = array();
            for (  $i=1;$i<=7;$i++)
                array_push($horraires,
                    array(
                        "jour" => $days[$i-1],
                        "o_h" => $edit_shop_form->get('o_j_'.$i)->getData(),
                        "c_h" => $edit_shop_form->get('f_j_'.$i)->getData()
                    )
                );
//
            $upd_shop->setHoraires(json_encode($horraires));
            /************************************End Horraires************************************/
            if($file===null)
            $upd_shop->setImage($image);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_shop);
            $entityManager->flush();

            $form_sent = true;
        }

        $upd_shop_more = $this->getDoctrine()->getRepository(Shop::class)->findById($id);
        if (isset($upd_shop_more[0])) $upd_shop_more = $upd_shop_more[0];
        $edit_shop_form_more = $this->createForm(EditShopFormTypeMore::class, $upd_shop);
        $edit_shop_form_more->handleRequest($request);
        if ($edit_shop_form_more->isSubmitted() && $edit_shop_form_more->isValid()) {
            $upd_shop_more->setWebsite($edit_shop_form_more->get('website')->getData());
            $upd_shop_more->setFacebook($edit_shop_form_more->get('facebook')->getData());
            $upd_shop_more->setTwitter($edit_shop_form_more->get('twitter')->getData());
            $upd_shop_more->setLinkdin($edit_shop_form_more->get('linkdin')->getData());
            $upd_shop_more->setInstagram($edit_shop_form_more->get('instagram')->getData());
            $upd_shop_more->setEmail($edit_shop_form_more->get('email')->getData());
            $upd_shop_more->setTel($edit_shop_form_more->get('tel')->getData());


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_shop_more);
            $entityManager->flush();

            $form_sent = true;
        }

        $upd_points = $this->getDoctrine()->getRepository(Shop::class)->findById($id);
        if (isset($upd_points[0])) $upd_points = $upd_points[0];
        $edit_points_form = $this->createForm(EditPointsFormType::class, $upd_points);
        $edit_points_form->handleRequest($request);
        if ($edit_points_form->isSubmitted() && $edit_points_form->isValid()) {
            $upd_points->setSpendRate($edit_points_form->get('spend_rate')->getData());
            $upd_points->setRewardRate($edit_points_form->get('reward_rate')->getData());
            $upd_points->setThreshold($edit_points_form->get('threshold')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_points);
            $entityManager->flush();

            $form_sent = true;
        }

        /**************
         * ٌ       Reduction
         */

        $upd_reduction = $this->getDoctrine()->getRepository(Shop::class)->findById($id);

        if (isset($upd_reduction[0])) $upd_reduction = $upd_reduction[0];
        $edit_reduction_form = $this->createForm(EditReductionFormType::class, $upd_reduction);
        $edit_reduction_form->handleRequest($request);
        if ($edit_reduction_form->isSubmitted() && $edit_reduction_form->isValid()) {
            $upd_reduction->setReductionMontant($edit_reduction_form->get('reduction_montant')->getData());
            $upd_reduction->setReductionPoints($edit_reduction_form->get('reduction_points')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_reduction);
            $entityManager->flush();

            $form_sent = true;
        }

        $em = $this->getDoctrine()->getManager();

        $RAW_QUERY = "select * from user where roles like '%ROLE_VENDOR%' ";
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $vendors=$statement->fetchall();

        return $this->render('shop/update_shop.html.twig', [
            'shop' => $shop,
            'current_vendor'=> $shop[0]->getUserId(),
            'vendors'=>$vendors,
            'day_horaires' => $day_horaires,
            'edit_shop_form' => $edit_shop_form->createView(),
            'edit_shop_form_more' => $edit_shop_form_more->createView(),
            'edit_points_form' => $edit_points_form->createView(),
            'edit_reduction_form' => $edit_reduction_form->createView(),
            'form_sent' => $form_sent
        ]);
    }


    /**
     * @Route("/shop/{id}", name="single_shop")
     * @param Request $request
     * @param $id
     * @return
     */
    public function single_shop(Request $request, $id)
    {
        $form_sent = false;

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $roles = $user->getRoles();
        $shop = $this->getDoctrine()->getRepository(Shop::class)->findById(intval($id));
        $view = 'customer';
        $day_horaires=json_decode($shop[0]->getHoraires());
        if ($this->isGranted('ROLE_VENDOR')) $view = 'vendor';
        if ($this->isGranted('ROLE_ADMIN')) $view = 'admin';


        $shop = $this->getDoctrine()->getRepository(Shop::class)->findById(intval($id));
        $day_horaires=json_decode($shop[0]->getHoraires());
        $upd_shop = $this->getDoctrine()->getRepository(Shop::class)->findById($id);
        $image = $upd_shop[0]->getImage();
        if (isset($upd_shop[0])) $upd_shop = $upd_shop[0];
        $edit_shop_form = $this->createForm(EditShopFormType::class, $upd_shop);
        $edit_shop_form->handleRequest($request);
        if ($edit_shop_form->isSubmitted() && $edit_shop_form->isValid()) {

            $file = $upd_shop->getImage();
            if($file !== null){
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
                $upd_shop->setImage($fileName);
            }

            $upd_shop->setName($edit_shop_form->get('name')->getData());
            $upd_shop->setAddress($edit_shop_form->get('address')->getData());
            $upd_shop->setCity($edit_shop_form->get('city')->getData());
            $upd_shop->setZip($edit_shop_form->get('zip')->getData());
            $upd_shop->setPays($edit_shop_form->get('pays')->getData());
            $roles = $this->get('security.token_storage')->getToken()->getUser()->getRoles();
            if(in_array('ROLE_ADMIN', $roles))
                $upd_shop->setUserId($this->getDoctrine()->getRepository(User::class)->findOneById(
                    $request->get('user_id'))
                );

            /******************************begin Horraires****************************************/

            $days= ["lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche"];
            $horraires = array();
            for (  $i=1;$i<=7;$i++)
                array_push($horraires,
                    array(
                        "jour" => $days[$i-1],
                        "o_h" => $edit_shop_form->get('o_j_'.$i)->getData(),
                        "c_h" => $edit_shop_form->get('f_j_'.$i)->getData()
                    )
                );
//
            $upd_shop->setHoraires(json_encode($horraires));
            /************************************End Horraires************************************/
            if($file===null)
                $upd_shop->setImage($image);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_shop);
            $entityManager->flush();

            $form_sent = true;
        }

        $upd_shop_more = $this->getDoctrine()->getRepository(Shop::class)->findById($id);
        if (isset($upd_shop_more[0])) $upd_shop_more = $upd_shop_more[0];
        $edit_shop_form_more = $this->createForm(EditShopFormTypeMore::class, $upd_shop);
        $edit_shop_form_more->handleRequest($request);
        if ($edit_shop_form_more->isSubmitted() && $edit_shop_form_more->isValid()) {
            $upd_shop_more->setWebsite($edit_shop_form_more->get('website')->getData());
            $upd_shop_more->setFacebook($edit_shop_form_more->get('facebook')->getData());
            $upd_shop_more->setTwitter($edit_shop_form_more->get('twitter')->getData());
            $upd_shop_more->setLinkdin($edit_shop_form_more->get('linkdin')->getData());
            $upd_shop_more->setInstagram($edit_shop_form_more->get('instagram')->getData());
            $upd_shop_more->setEmail($edit_shop_form_more->get('email')->getData());
            $upd_shop_more->setTel($edit_shop_form_more->get('tel')->getData());


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_shop_more);
            $entityManager->flush();

            $form_sent = true;
        }

        $upd_points = $this->getDoctrine()->getRepository(Shop::class)->findById($id);
        if (isset($upd_points[0])) $upd_points = $upd_points[0];
        $edit_points_form = $this->createForm(EditPointsFormType::class, $upd_points);
        $edit_points_form->handleRequest($request);
        if ($edit_points_form->isSubmitted() && $edit_points_form->isValid()) {
            $upd_points->setSpendRate($edit_points_form->get('spend_rate')->getData());
            $upd_points->setRewardRate($edit_points_form->get('reward_rate')->getData());
            $upd_points->setThreshold($edit_points_form->get('threshold')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_points);
            $entityManager->flush();

            $form_sent = true;
        }

        /**************
         * ٌ       Reduction
         */

        $upd_reduction = $this->getDoctrine()->getRepository(Shop::class)->findById($id);

        if (isset($upd_reduction[0])) $upd_reduction = $upd_reduction[0];
        $edit_reduction_form = $this->createForm(EditReductionFormType::class, $upd_reduction);
        $edit_reduction_form->handleRequest($request);
        if ($edit_reduction_form->isSubmitted() && $edit_reduction_form->isValid()) {
            $upd_reduction->setReductionMontant($edit_reduction_form->get('reduction_montant')->getData());
            $upd_reduction->setReductionPoints($edit_reduction_form->get('reduction_points')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($upd_reduction);
            $entityManager->flush();

            $form_sent = true;
        }

        $em = $this->getDoctrine()->getManager();

        $RAW_QUERY = "select * from user where roles like '%ROLE_VENDOR%' ";
        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();
        $vendors=$statement->fetchall();

        return $this->render('shop/single_' . $view . '.html.twig', [
            'shop' => $shop,
            'day_horaires' => $day_horaires,
            'edit_shop_form' => $edit_shop_form->createView(),
            'edit_shop_form_more' => $edit_shop_form_more->createView(),
            'edit_points_form' => $edit_points_form->createView(),
            'edit_reduction_form' => $edit_reduction_form->createView(),
            'form_sent' => $form_sent
        ]);
    }

    /**
     * @Route("/shop", name="shop")
     */
    public function index()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $shops_repo = $this->getDoctrine()->getRepository(Shop::class);
        $roles = $user->getRoles();

        $is_admin = in_array('ROLE_ADMIN', $roles);
        $is_vendor = in_array('ROLE_VENDOR', $roles);

        if ($is_admin) {
            $shops = $shops_repo->findAll();
        } elseif ($is_vendor) {
            $shops = $user->getShops();
        }

        if (isset($shops)) {
            return $this->render('shop/index.html.twig', [
                'shops' => $shops
            ]);
        } else {
            return $this->forward('App\Controller\MainController::index');
        }
    }

    /**
     * @Route("/my_shops", name="my_shops")
     */
    public function my_shops()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_VENDOR', $roles) || in_array('ROLE_ADMIN', $roles)) {
            $shops = $user->getShops();
            return $this->render('shop/index.html.twig', [
                'shops' => $shops
            ]);
        } elseif (in_array('ROLE_CUSTOMER', $roles) || in_array('ROLE_ADMIN', $roles)) {
            $shops = $user->getMyShops();

            foreach ($shops as $shop){
                $shop->points=0;
                foreach ($shop->getTransactions() as $tr){
                    foreach ($tr->getUserId() as $id){
                        if($id->getId()==$user->getId()){
                            $shop->points+=$tr->getPoints();
                        }
                    }
                }
            }

            return $this->render('shop/index.html.twig', [
                'shops' => $shops
            ]);
        } else {
            return $this->forward('App\Controller\MainController::index');
        }
    }
}