<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Building;
use AppBundle\Entity\Kingdom;
use AppBundle\Entity\Unit;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class BuildingController extends DefaultController
{

    /**
     * @Route("/level-up/{building}", name="level-up", requirements={"building"="[A-Za-z]+"})
     */
    public function buildingAction(Request $request, $building){
        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $checkNickname = $this->checkNickname($em, new Session());
        if($checkNickname instanceof Response){
            return $checkNickname;
        }
        $user->setNickname($checkNickname);

        $try = $em->getRepository(Building::class)->findOneBy([
            "name" => $building
        ]);
        if($try == null){
            return $this->redirectToRoute("logout");//TODO: this can and must never be reached
        }

        $kingdomId = $em->getRepository(User::class)->findOneBy(["nickname"=>$user->getNickname()])->getKingdomId();
        $builgingLevel = $em->getRepository(Building::class)->getLevel($kingdomId, $try->getName())[0]['level'];


        $dt = new \DateTime();
        $dt->setTimezone(new \DateTimeZone('Europe/Sofia'));
        $readyOn = $dt->setTimestamp($dt->getTimestamp() + $try->getTimeNPL())->format("Y-m-d H:i:s");

        $tmp = $this->checkResNPL($em, $building, $builgingLevel, $kingdomId);
        if($tmp instanceof Response){ return $tmp; }

        if($tmp && $this->checkNBL($em, $building, $kingdomId)){
            $em->getRepository(Building::class)->setReadyOn($kingdomId, $try->getName(), $readyOn);
        }
        $em->flush();

        return $this->redirectToRoute("homepage");
    }

    /**
     * @Route("/level-up/{building}/readyOn/{readyOn}", name="level-up_readyOn", requirements={"building"="[A-Za-z]+","readyOn"="[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}"})
     */
    public function readyAction(Request $request, $building, $readyOn){
        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $checkNickname = $this->checkNickname($em, new Session());
        if($checkNickname instanceof Response){
            return $checkNickname;
        }
        $user->setNickname($checkNickname);

        $try = $em->getRepository(Building::class)->findOneBy([
            "name" => $building
        ]);
        if($try == null){
            return $this->redirectToRoute("logout");//TODO: this can and must never be reached
        }

        $kingdomId = $em->getRepository(User::class)->findOneBy(["nickname"=>$user->getNickname()])->getKingdomId();

        $bldngInUK = $this->getBuildingsInKingdom($em, $kingdomId);
        if($bldngInUK instanceof Response){ return $bldngInUK; }

        $buildingName = null;
        foreach($bldngInUK as $item){
            if($item->getReadyOn() != null && ($item->getReadyOn())->format("Y-m-d H:i:s") == $readyOn){
                $buildingName = $item->getName(); break;
            }
        }

        if($buildingName != $building){
            return $this->redirectToRoute("homepage");
        }

        $em->getRepository(Building::class)->setReadyOn($kingdomId, $try->getName(), null, true);
        $em->flush();

        return $this->redirectToRoute("homepage");
    }

    /**
     * @Route("/unit/order", name="order_unit")
     */
    public function orderUnitAction(Request $request){
        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $checkNickname = $this->checkNickname($em, new Session());
        if($checkNickname instanceof Response){
            return $checkNickname;
        }
        $user->setNickname($checkNickname);

        $kingdomId = $em->getRepository(User::class)->findOneBy([ "nickname" => $user->getNickname() ])->getKingdomId();
        $user->setKingdomId($kingdomId);

        $UK_name = $em->getRepository(Kingdom::class)->findOneBy([ "id" => $user->getKingdomId() ])->getName();

        $resOfUK = $this->getResources($em, $user);
        if($resOfUK instanceof Response){ return $resOfUK; }

        $maxResOfUK = $this->getMaxResources($em, $user);
        if($maxResOfUK instanceof Response){ return $maxResOfUK; }

        $bldngInUK = $this->getBuildingsInKingdom($em, $user->getKingdomId());
        if($bldngInUK instanceof Response){ return $bldngInUK; }

        $unitsInUK = $this->getUnitsInKingdom($em, $user->getKingdomId());
        if($unitsInUK instanceof Response){ return $unitsInUK; }

        foreach($unitsInUK as $item){
            $tmp = $this->prepareNBLGname($em, $bldngInUK, $item->getName(), $item->getNBLGname(), true);
            if($tmp instanceof Response){ return $tmp; }
            $item->setNBLGname($tmp);
            $tmp = null;

            $tmp = $this->prepareResNPUGname($em, $item->getName(), $item->getResNPUGname());
            if($tmp instanceof Response){ return $tmp; }
            $item->setResNPUGname($tmp);
            $tmp = null;
        }

        $em->flush();

        return $this->render('login/unit.html.twig', [
            "is_logged" => true,
            "nickname" => $user->getNickname(),
            "kingdom_name" => $UK_name,
            "resOfUK" => $resOfUK,
            "maxResOfUK" => $maxResOfUK,
            "unitsInUK" => $unitsInUK
        ]);
    }

    /**
     * @Route("/unit/order/{unit}/count/{count}", name="order_specific_unit_count", requirements={"unit"="[A-Za-z_]+","count"="[0-9]+"})
     */
    public function confirmOrderUnitAction(Request $request, $unit, $count){
        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $checkNickname = $this->checkNickname($em, new Session());
        if($checkNickname instanceof Response){
            return $checkNickname;
        }
        $user->setNickname($checkNickname);

        $kingdomId = $em->getRepository(User::class)->findOneBy([ "nickname" => $user->getNickname() ])->getKingdomId();
        $user->setKingdomId($kingdomId);

        $resOfUK = $this->getResources($em, $user);
        if($resOfUK instanceof Response){ return $resOfUK; }

        $maxResOfUK = $this->getMaxResources($em, $user);
        if($maxResOfUK instanceof Response){ return $maxResOfUK; }

        // to down

        $bldngInUK = $this->getBuildingsInKingdom($em, $user->getKingdomId());
        if($bldngInUK instanceof Response){ return $bldngInUK; }

        $unitsInUK = $this->getUnitsInKingdom($em, $user->getKingdomId());
        if($unitsInUK instanceof Response){ return $unitsInUK; }

        foreach($unitsInUK as $item){
            $tmp = $this->checkNBLUnit($em, $item->getName(), $kingdomId);
            if($tmp instanceof Response){ return $tmp; }
            $item->setNBLGname($tmp);
            $tmp = null;

            $tmp = $this->checkResNPU($em, $item->getName(), $item->getResNPUGname());
            if($tmp instanceof Response){ return $tmp; }
            $item->setResNPUGname($tmp);
            $tmp = null;
        }

        // stop here

        $unitId = null;
        $unitOrderCount = 0;
        foreach($unitsInUK as $item){
            if($item->getName() == $unit && $item->getOrderCount() == 0){
                $unitId = $item->getId();
                $unitOrderCount = $item->getOrderCount(); break;
            }
        }

        if($unitId == null){
            return $this->redirectToRoute("homepage");
        }

        $em->getRepository(Unit::class)->setOrderCount($kingdomId, $unitId, ($unitOrderCount + $count));
        $em->flush();

        return $this->redirectToRoute("homepage");
    }

    private function checkResNPL(ObjectManager $em, $building_name, $building_level, $kingdomId){

        $NPLOfBuilding = $em->getRepository(Building::class)->getNPLOfBuilding($building_name);
        if($NPLOfBuilding == null){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }
        $resOfUK = $em->getRepository(Kingdom::class)->getResourcesForKingdom($kingdomId);
        if($resOfUK == null){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }

        $return = [];
        foreach($NPLOfBuilding as $item){
            foreach($resOfUK as $item2){
                if(!array_key_exists($item2['name'], $return)){
                    $return[$item2['name']] = 0;
                }
                $item['value'] = ($item['value'] * ($building_level + 1));

                if($item['name'] == $item2['name'] && $item2['value'] < $item['value']){
                    return false;
                }elseif($item['name'] == $item2['name'] && $item2['value'] >= $item['value']){
                    $return[$item2['name']] += $item2['value'] - $item['value'];
                }
            }
        }

        foreach($return as $key => $value){
            $em->getRepository(Kingdom::class)->subtractResourcesForKingdom($kingdomId, $key, $value);
            $em->flush();
        }
        return true;
    }

    private function checkNBL(ObjectManager $em, $building_name, $kingdomId){
        $bldngInUK = $this->getBuildingsInKingdom($em, $kingdomId);
        if($bldngInUK instanceof Response){ return $bldngInUK; }

        $NBLGname = null;
        foreach($bldngInUK as $item){
            if($item->getName() == $building_name){
                $NBLGname = $item->getNBLGname(); break;
            }
        }

        if($this->prepareNBLGname($em, $bldngInUK, $building_name, $NBLGname) == 'no need'){
            return true;
        }

        return false;
    }

//    private function checkNBLUnit(ObjectManager $em, $unit_name, $kingdomId){
//        $bldngInUK = $this->getBuildingsInKingdom($em, $kingdomId);
//        if($bldngInUK instanceof Response){ return $bldngInUK; }
//
//        $NBLGname = null;
//        foreach($bldngInUK as $item){
//            if($item->getName() == $unit_name){
//                $NBLGname = $item->getNBLGname(); break;
//            }
//        }
//
//        if($this->prepareNBLGname($em, $bldngInUK, $unit_name, $NBLGname) == 'no need'){
//            return true;
//        }
//
//        return false;
//    }

    private function getUnitsInKingdom(ObjectManager $em, int $kingdomId){
        $try = $em->getRepository(Kingdom::class)->getUnitsInKingdom($kingdomId);
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }
        $unitsInUK = [];
        foreach($try as $item){
            $tmp2 = new Unit();
            $tmp2->setId($item['id']);
            $tmp2->setName($item['name']);
            $tmp2->setAttack($item['attack']);
            $tmp2->setLive($item['live']);
            $tmp2->setResNPUGname($item['id_resNeedPU']);
            $tmp2->setNBLGname(intval($item['id_needBldngL']));
            $tmp2->setTimePU(intval($item['timePU']));
            $tmp2->setCount(intval($item['count']));
            $unitsInUK[] = $tmp2;
            unset($tmp2);
        }
        return $unitsInUK;
    }

    private function prepareResNPUGname(ObjectManager $em, $unit_name, $ResNPUGname){
        if($ResNPUGname == null){
            return 'no need';
        }

        $try = $em->getRepository(Unit::class)->getNPUOfUnit($unit_name);
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }

        $arr = [];
        foreach($try as $item){
            $arr[] = $item['value'].' '.$item['name'];
        }

        return implode(", ", $arr);
    }
}