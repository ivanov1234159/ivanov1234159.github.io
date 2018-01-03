<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UnitRepository extends EntityRepository
{
    public function setOrderCount(int $id_kingdom, int $unit_id, int $orderCount){
        $db = $this->getEntityManager()->getConnection();

            $create_event = $db->prepare("create EVENT `Level-Up".$id_kingdom.$unit_id."`
        ON SCHEDULE AT '".$orderCount."'
        DO
            update kngdm_bldng INNER JOIN building
            set kngdm_bldng.`level` = (kngdm_bldng.`level` + 1)
            WHERE kngdm_bldng.id_building = building.id
            and kngdm_bldng.id_kingdom = ".$id_kingdom."
            and building.name = '".$unit_id."';");
            $create_event->execute();

        $set_ready_on = $db->prepare("update kngdm_bldng 
inner join building 
set ready_on=? 
where kngdm_bldng.id_building=building.id 
and kngdm_bldng.id_kingdom=? 
and building.name=?;");
        $set_ready_on->execute([ $orderCount, $id_kingdom, $unit_id]);
    }

    public function getNBLOfUnit(string $unit_name){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetNBLOfUnit(?);");
        $query->execute([ $unit_name ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getNPUOfUnit(string $unit_name){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetNPUOfUnit(?);");
        $query->execute([ $unit_name ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}