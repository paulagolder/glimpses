<?php

namespace App\Repository;

use App\Entity\Person;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class PersonRepository extends EntityRepository
{


    public function findChildren($gid)
    {
        $sql = "select g from App:person g ";
        $sql .= " where g.glimpseid = ".$gid." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();
        $aglimpses = array();
        foreach( $glimpses as $key=>$glimpse)
        {
          $aglimpses[$glimpse->getPersonref()]= $glimpse;
        }
        return $aglimpses;
    }


     public function delete($gid,$pref)
    {
        $sql = "delete from App:person g ";
        $sql .= " where g.glimpseid = ".$gid." ";
           $sql .= " and g.personref = ".$pref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();

    }

}

