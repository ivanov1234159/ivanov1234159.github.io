<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Kingdom;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ProfileController extends DefaultController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function profileAction(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $checkNickname = $this->checkNickname($em, new Session());
        if($checkNickname instanceof Response){
            return $checkNickname;
        }
        $user->setNickname($checkNickname);

        $userData = $em->getRepository(User::class)->findOneBy([
            "nickname" => $user->getNickname()
        ]);
        $user->setId($userData->getId());
        $user->setUsername($userData->getUsername());
        $user->setPassword($userData->getPassword());
        $user->setEmail($userData->getEmail());
        $user->setKingdomId($userData->getKingdomId());
        $user->setDateCreated($userData->getDateCreated());

        $try = $em->getRepository(Kingdom::class)->findOneBy([
            "id" => $user->getKingdomId()
        ]);
        if($try == null){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }
        $UK_name = $try->getName();
        unset($try);

        $resOfUK = $this->getResources($em, $user);
        if($resOfUK instanceof Response){ return $resOfUK; }

        $maxResOfUK = $this->getMaxResources($em, $user);
        if($maxResOfUK instanceof Response){ return $maxResOfUK; }

        return $this->render('login/profile.html.twig', [
            "is_logged" => true,
            "nickname" => $user->getNickname(),
            "kingdom_name" => $UK_name,
            "resOfUK" => $resOfUK,
            "maxResOfUK" => $maxResOfUK,
            "user" => $user
        ]);
    }
}
