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
    const PALACE_ID = 12;

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

        $kingdomId = $em->getRepository(User::class)
            ->findOneBy(["nickname"=>$user->getNickname()])->getKingdomId();
        $builgingLevel = $em->getRepository(Building::class)
            ->getLevel($kingdomId, $try->getName())[0]['level'];


        $dt = new \DateTime();
        $dt->setTimezone(new \DateTimeZone('Europe/Sofia'));
        $readyOn = $dt->setTimestamp($dt->getTimestamp() + ($try->getTimeNPL() * $builgingLevel + 2))->format("Y-m-d H:i:s");

        $tmp = $this->checkResNPL($em, $building, $builgingLevel, $kingdomId);
        if($tmp instanceof Response){ return $tmp; }

        if($tmp && $this->checkNBL($em, $building, $kingdomId)){
            $em->getRepository(Building::class)->setReadyOn($kingdomId, $try->getName(), $readyOn);
        }
        $em->flush();

        return $this->redirectToRoute("homepage");
    }

    /**
     * @Route("/level-up/{building}/readyOn/{readyOn}", name="level-up_readyOn",
     *     requirements={"building"="[A-Za-z]+","readyOn"="[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}"})
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

        $kingdomId = $em->getRepository(User::class)
            ->findOneBy(["nickname"=>$user->getNickname()])->getKingdomId();

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

        $em->getRepository(Building::class)
            ->setReadyOn($kingdomId, $try->getName(), null, true);

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

        $kingdomId = $em->getRepository(User::class)
            ->findOneBy([ "nickname" => $user->getNickname() ])->getKingdomId();

        $user->setKingdomId($kingdomId);

        $UK_name = $em->getRepository(Kingdom::class)
            ->findOneBy([ "id" => $user->getKingdomId() ])->getName();

        $resOfUK = $this->getResources($em, $user);
        if($resOfUK instanceof Response){ return $resOfUK; }

        $maxResOfUK = $this->getMaxResources($em, $user);
        if($maxResOfUK instanceof Response){ return $maxResOfUK; }

        $bldngInUK = $this->getBuildingsInKingdom($em, $user->getKingdomId());
        if($bldngInUK instanceof Response){ return $bldngInUK; }

        $unitsInUK = $this->getUnitsInKingdom($em, $user->getKingdomId());
        if($unitsInUK instanceof Response){ return $unitsInUK; }

        $maxUnitCountArr = [];
        foreach($unitsInUK as $item){
            $tmp = $this->prepareNBLGname($em, $bldngInUK, $item->getName(), $item->getNBLGname(), true);
            if($tmp instanceof Response){ return $tmp; }
            $item->setNBLGname($tmp);
            $tmp = null;

            $tmp = $this->prepareResNPUGname($em, $item->getName(), $item->getResNPUGname());
            if($tmp instanceof Response){ return $tmp; }
            $item->setResNPUGname($tmp);
            $tmp = null;

            $maxUnitCountArr[$item->getName()] = $this
                ->getMaxUnitCount($em, $kingdomId, $item->getName(), $item->getResNPUGname());

            if($item->getNBLGname() != 'no need'){
                $maxUnitCountArr[$item->getName()] = 0;
            }
        }

        $em->flush();

        return $this->render('login/unit.html.twig', [
            "is_logged" => true,
            "nickname" => $user->getNickname(),
            "kingdom_name" => $UK_name,
            "resOfUK" => $resOfUK,
            "maxResOfUK" => $maxResOfUK,
            "unitsInUK" => $unitsInUK,
            "maxUnitCountArr" => $maxUnitCountArr
        ]);
    }

    /**
     * @Route("/unit/order/{unit}/count/{count}", name="order_specific_unit_count",
     *     requirements={"unit"="[A-Za-z_]+","count"="[0-9]+"})
     */
    public function confirmOrderUnitAction(Request $request, $unit, $count){
        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $checkNickname = $this->checkNickname($em, new Session());
        if($checkNickname instanceof Response){
            return $checkNickname;
        }
        $user->setNickname($checkNickname);

        $kingdomId = $em
            ->getRepository(User::class)->findOneBy([ "nickname" => $user->getNickname() ])
            ->getKingdomId();

        $user->setKingdomId($kingdomId);

        $unitsInUK = $this->getUnitsInKingdom($em, $user->getKingdomId());
        if($unitsInUK instanceof Response){ return $unitsInUK; }

        $unitId = null;
        $unitOrderCount = 0;
        $unitTimePU = 0;
        foreach($unitsInUK as $item){
            if($item->getName() == $unit){
                $unitId = $item->getId();
                $unitOrderCount = $item->getOrderCount();
                $unitTimePU = $item->getTimePU();
                break;
            }
        }

        if($unitId == null){
            return $this->redirectToRoute("homepage");
        }

        $testNBL = $this->checkNBLUnit($em, $unitsInUK, $unit, $kingdomId);
        if($testNBL instanceof Response){ return $testNBL; }

        if(!$testNBL){
            return $this->redirectToRoute("homepage");
        }

        $testResNPU = $this->checkResNPU($em, $unit, $count, $kingdomId);

        if($testResNPU){
            $dt = new \DateTime();
            $dt->setTimezone(new \DateTimeZone('Europe/Sofia'));
            $now = $dt->format("Y-m-d H:i:s");
            $readyOn = $dt->setTimestamp($dt->getTimestamp() + ($unitTimePU * ($unitOrderCount + $count)))->format("Y-m-d H:i:s");

            $em->getRepository(Unit::class)
                ->setOrderCount($kingdomId, $unitId, ($unitOrderCount + $count), $unitTimePU, $now, $readyOn);
        }

        $em->flush();

        return $this->redirectToRoute("homepage");
    }

    /**
     * @Route("/attack", name="attack")
     */
    public function attackAction(Request $request){
        $user = new User();
        $em = $this->getDoctrine()->getManager();

        $checkNickname = $this->checkNickname($em, new Session());
        if($checkNickname instanceof Response){
            return $checkNickname;
        }
        $user->setNickname($checkNickname);

        $kingdomId = $em->getRepository(User::class)
            ->findOneBy([ "nickname" => $user->getNickname() ])->getKingdomId();

        $user->setKingdomId($kingdomId);

        $UK_name = $em->getRepository(Kingdom::class)
            ->findOneBy([ "id" => $user->getKingdomId() ])->getName();

        $resOfUK = $this->getResources($em, $user);
        if($resOfUK instanceof Response){ return $resOfUK; }

        $maxResOfUK = $this->getMaxResources($em, $user);
        if($maxResOfUK instanceof Response){ return $maxResOfUK; }

        $bldngInUK = $this->getBuildingsInKingdom($em, $user->getKingdomId());
        if($bldngInUK instanceof Response){ return $bldngInUK; }
        foreach($bldngInUK as $building){
            if($building->getId() == $this::PALACE_ID && $building->getLevel() == 0){
                return $this->redirectToRoute("homepage");
            }
        }

        $kingdoms = $em->getRepository(Kingdom::class)->findAll();
        foreach($kingdoms as $key => $value){
            if($value->getId() == $kingdomId){
                array_splice($kingdoms, $key,1); break;
            }
        }

        $users = [];
        foreach($kingdoms as $kingdom){
            $users[$kingdom->getId()] = $em->getRepository(User::class)->findOneBy([
                "kingdomId" => $kingdom->getId()
            ])->getNickname();
        }

        $em->flush();

        return $this->render('login/attack.html.twig', [
            "is_logged" => true,
            "nickname" => $user->getNickname(),
            "kingdom_name" => $UK_name,
            "resOfUK" => $resOfUK,
            "maxResOfUK" => $maxResOfUK,
            "kingdoms" => $kingdoms,
            "users" => $users
        ]);
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
        foreach($NPLOfBuilding as $resNeed){
            foreach($resOfUK as $resHave){
                if(!array_key_exists($resHave['name'], $return)){
                    $return[$resHave['name']] = intval($resHave['value']);
                    $resNeed['value'] = (intval($resNeed['value']) * ($building_level + 1));
                }

                if($resNeed['name'] == $resHave['name'] && intval($resHave['value']) < intval($resNeed['value'])){
                    return false;
                }elseif($resNeed['name'] == $resHave['name'] && intval($resHave['value']) >= intval($resNeed['value'])){
                    $return[$resHave['name']] -= intval($resNeed['value']);
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

    private function checkNBLUnit(ObjectManager $em, array $unitsInUK, $unit_name, $kingdomId){
        $bldngInUK = $this->getBuildingsInKingdom($em, $kingdomId);
        if($bldngInUK instanceof Response){ return $bldngInUK; }

        $NBLGname = null;
        foreach($unitsInUK as $item){
            if($item->getName() == $unit_name){
                $NBLGname = $item->getNBLGname(); break;
            }
        }

        if($this->prepareNBLGname($em, $bldngInUK, $unit_name, $NBLGname, true) == 'no need'){
            return true;
        }

        return false;
    }

    private function checkResNPU(ObjectManager $em, $unit_name, $unit_count, $kingdomId){

        $try = $em->getRepository(Unit::class)->getNPUOfUnit($unit_name);
        $resOfUK = $em->getRepository(Kingdom::class)->getResourcesForKingdom($kingdomId);

        $return = [];
        foreach($try as $resNeed){
            foreach($resOfUK as $resHave){
                if(!array_key_exists($resHave['name'], $return)){
                    $return[$resHave['name']] = intval($resHave['value']);
                    $resNeed['value'] = (intval($resNeed['value']) * $unit_count);
                }


                if($resNeed['name'] == $resHave['name']
                    && intval($resHave['value']) < intval($resNeed['value'])){
                    return false;
                }elseif($resNeed['name'] == $resHave['name']
                    && intval($resHave['value']) >= intval($resNeed['value'])){
                    $return[$resHave['name']] -= intval($resNeed['value']);
                }
            }
        }

        foreach($return as $key => $value){
            $em->getRepository(Kingdom::class)->subtractResourcesForKingdom($kingdomId, $key, $value);
            $em->flush();
        }

        return true;
    }

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
            $tmp2->setOrderCount(intval($item['order_count']));
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

    private function getMaxUnitCount(ObjectManager $em, int $kingdom_id, $unit_name, $ResNPUGname){
        if($ResNPUGname == null){
            return 0;
        }

        $resInUK = $em->getRepository(Kingdom::class)->getResourcesForKingdom($kingdom_id);
        if(empty($resInUK)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }

        $try = $em->getRepository(Unit::class)->getNPUOfUnit($unit_name);
        if(empty($try)){
            return $this->redirectToRoute("logout");//TODO: this must never be reached
        }

        $arr = [];
        $return = [];
        foreach($try as $resNeed){
            foreach($resInUK as $resHave){
                if($resHave['name'] == $resNeed['name']
                    && intval($resHave['value']) < intval($resNeed['value'])){
                    return 0;
                }elseif($resHave['name'] == $resNeed['name']
                    && intval($resHave['value']) >= intval($resNeed['value'])){
                    if(!array_key_exists($resNeed['name'], $arr)){
                        $arr[$resNeed['name']] = 0;
                    }
                    $arr[$resNeed['name']] = intval($resHave['value']) / intval($resNeed['value']);
                    while($arr[$resNeed['name']] > 0){
                        if(!array_key_exists($resNeed['name'], $return)){
                            $return[$resNeed['name']] = 0;
                        }
                        $return[$resNeed['name']] += 1;
                        $arr[$resNeed['name']]--;
                    }
                }
            }
        }

        return max(min($return), 0);
    }
}