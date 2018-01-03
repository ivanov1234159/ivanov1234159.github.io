<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Building;
use AppBundle\Entity\Kingdom;
use AppBundle\Entity\Unit;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $checkNickname = $this->checkNickname($em, new Session());
        if($checkNickname instanceof Response){
            return $checkNickname;
        }
        $user->setNickname($checkNickname);

        $tmp = $this->getKingdomId($em, $user);//getKingdomId() use $user->getNickname()
        if($tmp instanceof RedirectResponse){ return $tmp; }
        $user->setKingdomId($tmp);
        unset($tmp);

        $try = $em->getRepository(Kingdom::class)->findOneBy([
            "id" => $user->getKingdomId()
        ]);
        if($try == null){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }
        $UK_name = $try->getName();
        unset($try);

        $resOfUK = $this->getResources($em, $user);
        if($resOfUK instanceof RedirectResponse){ return $resOfUK; }

        $maxResOfUK = $this->getMaxResources($em, $user);
        if($maxResOfUK instanceof RedirectResponse){ return $maxResOfUK; }

        $bldngInUK = $this->getBuildingsInKingdom($em, $user->getKingdomId());
        if($bldngInUK instanceof RedirectResponse){ return $bldngInUK; }

        foreach($bldngInUK as $item){
            $tmp = $this->prepareResGPHGname($em, $item->getName(), $item->getResGPHGname(), $item->getLevel());
            if($tmp instanceof RedirectResponse){ return $tmp; }
            $item->setResGPHGname($tmp);
            $tmp = null;

            $tmp = $this->prepareNBLGname($em, $bldngInUK, $item->getName(), $item->getNBLGname());
            if($tmp instanceof RedirectResponse){ return $tmp; }
            $item->setNBLGname($tmp);
            $tmp = null;

            $tmp = $this->prepareResNPLGname($em, $item->getName(), $item->getResNPLGname(), $item->getLevel());
            if($tmp instanceof RedirectResponse){ return $tmp; }
            $item->setResNPLGname($tmp);
            $tmp = null;

            if($item->getReadyOn() != null){
                $item->setReadyOn(($item->getReadyOn())->format("Y-m-d H:i:s"));
            }

        }

        return $this->render('login/ingex.html.twig', [
            "is_logged" => true,
            "nickname" => $user->getNickname(),
            "kingdom_name" => $UK_name,
            "resOfUK" => $resOfUK,
            "maxResOfUK" => $maxResOfUK,
            "bldngInUK" => $bldngInUK
        ]);
    }

    protected function checkNickname(ObjectManager $em, Session $session){
        if(get_class($this) == DefaultController::class){
            $return = $this->render('default/index.html.twig');
        }else{
            $return = $this->redirectToRoute("homepage");
        }

        if(!$session->has('nickname') || $em->getRepository(User::class)->findOneBy([
            "nickname" => $session->get("nickname")
        ]) == null){ return $return; }

        return $session->get("nickname");
    }

    private function getKingdomId(ObjectManager $em, User $user){
        $try = $em->getRepository(User::class)->findOneBy([
            "nickname" => $user->getNickname()
        ]);
        if($try == null){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }
        return $try->getKingdomId();
    }

    protected function getResources(ObjectManager $em, User $user){
        $try = $em->getRepository(Kingdom::class)->getResourcesForKingdom($user->getKingdomId());
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }
        $resOfUK = [];
        foreach($try as $item){
            if(!array_key_exists($item['name'], $resOfUK)){
                $resOfUK[$item['name']] = 0;
            }
            $resOfUK[$item['name']] += $item['value'];
        }
        $try = null;
        return $resOfUK;
    }

    protected function getMaxResources(ObjectManager $em, User $user){
        $try = $em->getRepository(Kingdom::class)->getMaxResourcesForKingdom($user->getKingdomId());
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }
        $maxResOfUK = [];
        foreach($try as $item){
            if(!array_key_exists($item['name'], $maxResOfUK)){
                $maxResOfUK[$item['name']] = 0;
            }
            $maxResOfUK[$item['name']] += $item['max_value'];
        }
        $try = null;
        return $maxResOfUK;
    }

    protected function getBuildingsInKingdom(ObjectManager $em, int $kingdomId){
        $try = $em->getRepository(Kingdom::class)->getBuildingsInKingdom($kingdomId);
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }
        $bldngInUK = [];
        foreach($try as $item){
            $tmp2 = new Building();
            $tmp2->setId($item['id']);
            $tmp2->setName($item['name']);
            $tmp2->setResGPHGname($item['id_resGivePH']);
            $tmp2->setNBLGname($item['id_needBldngL']);
            $tmp2->setResNPLGname($item['id_resNeedPL']);
            $tmp2->setTimeNPL(intval($item['timeNeedPL']));
            $tmp2->setAction($item['action']);
            $tmp2->setActionLabel($item['action_label']);
            $tmp2->setLevel(intval($item['level']));
            $readyOn = new \DateTime($item['ready_on'], new \DateTimeZone("Europe/Sofia"));
            if($item['ready_on'] == null){
                $readyOn = null;
            }
            $tmp2->setReadyOn($readyOn);
            $bldngInUK[] = $tmp2;
            unset($tmp2);
        }
        return $bldngInUK;
    }

    private function prepareResGPHGname(ObjectManager $em, $building_name, $ResGPHGname, $building_level){
        if($ResGPHGname == null){
            return 'nothing';
        }

        $try = $em->getRepository(Building::class)->getGPHOfBuilding($building_name);
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }

        $arr = [];
        foreach($try as $item){
            if($building_level == 0){
                $building_level++;
            }
            $arr[] = ($item['value'] * $building_level).' '.$item['name'];
        }

        return implode(", ", $arr);
    }

    protected function prepareNBLGname(ObjectManager $em, array $bldngInUK, $building_name, $NBLGname, $unit=false){
        if($NBLGname == null){
            return 'no need';
        }

        if(!$unit){
            $try = $em->getRepository(Building::class)->getNBLOfBuilding($building_name);
        }else{
            $try = $em->getRepository(Unit::class)->getNBLOfUnit($building_name);
        }
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }

        $arr = [];
        foreach($try as $item){
            $arr[] = $item['name'].' ('.$item['level'].' level)';
        }

        $arr2 = [];
        foreach($bldngInUK as $item){
            $arr2[] = $item->getName().' ('.$item->getLevel().' level)';
        }

        $return = implode(", ", array_diff($arr, $arr2));
        if(strlen($return) == 0 || $return == '' || empty($return)){
            $return = 'no need';
        }

        return $return;
    }

    private function prepareResNPLGname(ObjectManager $em, $building_name, $ResNPLGname, $building_level){
        if($ResNPLGname == null){
            return 'no need';
        }

        $try = $em->getRepository(Building::class)->getNPLOfBuilding($building_name);
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }

        $arr = [];
        foreach($try as $item){
            $arr[] = ($item['value'] * ($building_level + 1)).' '.$item['name'];
        }

        return implode(", ", $arr);
    }

    /**
     * @Route("/test")
     */
    public function testAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/test.html.twig');
    }
}
