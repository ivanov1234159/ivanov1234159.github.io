<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BuildingRepository extends EntityRepository
{
    public function updateLevel(int $level, int $id_kingdom, string $building_name){
        $db = $this->getEntityManager()->getConnection();

        return $db
            ->executeQuery('update kngdm_bldng INNER JOIN building set kngdm_bldng.`level` = ? 
WHERE kngdm_bldng.id_building = building.id and kngdm_bldng.id_kingdom = ? and building.name = ?', [
                $level, $id_kingdom, $building_name ]);
    }
    public function getLevel(int $id_kingdom, string $building_name){
        $db = $this->getEntityManager()->getConnection();

        return $db
            ->executeQuery('select kngdm_bldng.`level` from kngdm_bldng INNER JOIN building 
WHERE kngdm_bldng.id_building = building.id and kngdm_bldng.id_kingdom = ? and building.name = ?', [
                $id_kingdom, $building_name ])
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function setReadyOn(int $id_kingdom, string $building_name, string $readyOn=null, $only=false){
        $db = $this->getEntityManager()->getConnection();
        if($readyOn == null){
            $only=true;
        }

        if(!$only){
            $create_event = $db->prepare("create EVENT `Level-Up".$id_kingdom.$building_name."`
        ON SCHEDULE AT '".$readyOn."'
        DO
            update kngdm_bldng INNER JOIN building
            set kngdm_bldng.`level` = (kngdm_bldng.`level` + 1)
            WHERE kngdm_bldng.id_building = building.id
            and kngdm_bldng.id_kingdom = ".$id_kingdom."
            and building.name = '".$building_name."';");
            $create_event->execute();
        }

        $set_ready_on = $db->prepare("update kngdm_bldng 
inner join building 
set ready_on=? 
where kngdm_bldng.id_building=building.id 
and kngdm_bldng.id_kingdom=? 
and building.name=?;");
        $set_ready_on->execute([ $readyOn, $id_kingdom, $building_name]);
    }

    public function getGPHOfBuilding(string $building_name){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetGPHOfBuilding(?);");
        $query->execute([ $building_name ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getNBLOfBuilding(string $building_name){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetNBLOfBuilding(?);");
        $query->execute([ $building_name ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getNPLOfBuilding(string $building_name){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetNPLOfBuilding(?);");
        $query->execute([ $building_name ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}