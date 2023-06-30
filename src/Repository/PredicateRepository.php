<?php

namespace App\Repository;

use App\Entity\Predicate;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class PredicateRepository extends EntityRepository
{


    public function findChildren($gid, $aref)
    {
        $sql = "select g from App:Predicate g ";
        $sql .= " where g.glimpseid = ".$gid." ";
          $sql .= " and g.roleref = ".$aref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $predicates = $query->getResult();
          $apredicates = array();
        foreach( $predicates as $key=>$predicate)
        {
          $apredicates[$predicate->getPredicateref()]= $predicate;
        }

        return $apredicates;
    }


     public function delete($gid,$aref,$pref)
    {
        $sql = "delete from App:Predicate g ";
        $sql .= " where g.glimpseid = ".$gid." ";
        $sql .= " and g.roleref = ".$aref." ";
          $sql .= " and g.predicateref = ".$pref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->getResult();

    }

}

