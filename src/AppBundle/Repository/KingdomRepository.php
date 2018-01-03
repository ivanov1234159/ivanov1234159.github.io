<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class KingdomRepository extends EntityRepository
{
    public function setResourcesForKingdom(int $id, string $resource, int $value){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL SetResourcesForKingdom(?,?,?);");
        $query->execute([ $id, $resource, $value ]);
    }
    public function getResourcesForKingdom(int $id){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetResourcesForKingdom(?);");
        $query->execute([ $id ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function subtractResourcesForKingdom(int $id, string $resource, int $value){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL SubtractResForKingdom(?,?,?);");
        $query->execute([ $id, $resource, $value ]);
    }

    public function getMaxResourcesForKingdom(int $id){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetMaxResForKingdom(?, 0);");
        $query->execute([ $id ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function setBuildingsInKingdom(int $id, string $building, int $level){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL SetBuildingsInKingdom(?,?,?);");
        $query->execute([ $id, $building, $level ]);
    }
    public function getBuildingsInKingdom(int $id){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetBuildingsInKingdom(?);");
        $query->execute([ $id ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function setUnitsInKingdom(int $id, string $unit, int $count){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL SetUnitsInKingdom(?,?,?);");
        $query->execute([ $id, $unit, $count ]);
    }
    public function getUnitsInKingdom(int $id){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetUnitsInKingdom(?);");
        $query->execute([ $id ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}