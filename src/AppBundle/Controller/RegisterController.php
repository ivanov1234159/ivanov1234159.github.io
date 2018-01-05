<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Building;
use AppBundle\Entity\Kingdom;
use AppBundle\Entity\Resources;
use AppBundle\Entity\Unit;
use AppBundle\Entity\User;
use AppBundle\Form\RegisterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class RegisterController extends Controller
{
    /**
     * @Route("/register", name="register")
     * @Method("GET")
     */
    public function registerAction(Request $request, $error=null)
    {
        $session = new Session();
        if($session->has('nickname')){
            return $this->redirectToRoute("homepage");
        }

        $form = $this->createForm(RegisterType::class);

        $form->handleRequest($request);//only on POST

        return $this->render('default/register.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/register", name="registerPost")
     * @Method("POST")
     */
    public function registerPostAction(Request $request)
    {
        $user = new User();
        $reqAll = $request->request->all()['register_user'];
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $em = $this->getDoctrine()->getManager();

        if($reqAll['password']['first'] != $reqAll['password']['second']){
            return $this->registerPostErrors($request, 'Passwords do not match!');
        }
        $pass = $reqAll['password']['first'];
        $password = $encoder->encodePassword($pass, $user->getSalt());

        $try = $em->getRepository(User::class)->findOneBy([
            "nickname" => $reqAll['nickname']
        ]);
        if($try != null){
            return $this->registerPostErrors($request, 'Invalid or duplicate nickname!');
        }
        $try = null;


        $try = $em->getRepository(User::class)->findOneBy([
            "username" => $reqAll['username'],
            "password" => $password
        ]);
        if($try != null){
            return $this->registerPostErrors($request, 'Invalid or duplicate username!');
        }
        $try = null;

        $kingdom = new Kingdom();

        $randX = 0;
        $randY = 0;
        while(true){
            $randX = rand($kingdom::MIN_COORDINATE_X, $kingdom::MAX_COORDINATE_X);
            $randY = rand($kingdom::MIN_COORDINATE_Y, $kingdom::MAX_COORDINATE_Y);
            $try = $em->getRepository(Kingdom::class)->findOneBy([
                "coordinateX" => $randX,
                "coordinateY" => $randY
            ]);
            if($try == null){
                break;
            }
            $try = null;
        }
        $kingdom->setName('Kingdom of '.$reqAll['nickname']);
        $kingdom->setCoordinateX($randX);
        $kingdom->setCoordinateY($randY);

        $em->persist($kingdom);
        $em->flush();

        $try = $em->getRepository(Resources::class)->findAll();
        foreach($try as $item){
            $em->getRepository(Kingdom::class)
                ->setResourcesForKingdom($kingdom->getId(), $item->getName(), $item->getDefValue());
        }
        $try = null;

        $try = $em->getRepository(Building::class)->findAll();
        foreach($try as $item){
            if($item->getLevel() == null){
                $item->setLevel(0);
            }
            $em->getRepository(Kingdom::class)
                ->setBuildingsInKingdom($kingdom->getId(), $item->getName(), $item->getLevel());
        }
        $try = null;

        $try = $em->getRepository(Unit::class)->findAll();
        foreach($try as $item){
            if($item->getCount() == null){
                $item->setCount(0);
            }
            $em->getRepository(Kingdom::class)
                ->setUnitsInKingdom($kingdom->getId(), $item->getName(), $item->getCount());
        }
        $try = null;

        $user->setNickname($reqAll['nickname']);
        $user->setUsername($reqAll['username']);
        $user->setPassword($password);
        $user->setEmail($reqAll['email']);
        $user->setKingdomId($kingdom->getId());
        $user->setDateCreated(new \DateTime());

        $em->persist($user);
        $em->flush();

        // ... do any other work - like sending them an email, etc
        // maybe set a "flash" success message for the user

        return $this->redirectToRoute('login');
    }

    private function registerPostErrors($request, $error){
        return $this->registerAction($request, $error);
    }
}