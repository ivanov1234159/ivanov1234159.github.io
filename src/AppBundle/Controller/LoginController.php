<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class LoginController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Method("GET")
     */
    public function loginAction(Request $request, $error=null)
    {
        $session = new Session();
        if($session->has('nickname')){
            return $this->redirectToRoute("homepage");
        }

        $form = $this->createForm(LoginType::class);

        $form->handleRequest($request);//only on POST

        return $this->render('default/login.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/login", name="loginPost")
     * @Method("POST")
     */
    public function loginPostAction(Request $request, $error=null)
    {
        $session = new Session();
        $user = new User();
        $reqAll = $request->request->all()['login_user'];
        $em = $this->getDoctrine()->getManager();

        $password = null;

        $try = $em->getRepository(User::class)->findBy([
            "username" => $reqAll['username']
        ]);
        if($try == null){
            return $this->loginPostErrors($request, 'Invalid username!');
        }
        foreach($try as $user){
            if(crypt($reqAll['password'], $user->getPassword()) == $user->getPassword()){
                $password = crypt($reqAll['password'], $user->getSalt());
                break;
            }
        }
        if($password == null){
            return $this->loginPostErrors($request, 'Invalid password!');
        }

        unset($try, $password, $em, $reqAll);

        if(!$session->has('nickname')){
            $session->set('nickname', $user->getNickname());
        }

        return $this->redirectToRoute('homepage');
    }

    private function loginPostErrors($request, $error){
        return $this->loginAction($request, $error);
    }
}