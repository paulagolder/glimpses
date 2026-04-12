<?php

namespace App\Repository;

use App\Entity\Relation;


use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class RelationRepository extends EntityRepository
{


  public function getAll()
  {
    $qb = $this->createQueryBuilder('r');
    $qy = $qb->getQuery();
    $rns = $qy->getResult();
    dump($rns);
    foreach( $rns as $key=>$Relation)
    {
      $aRelations[$Relation->getRelationId()]= $Relation;
    }

    return $aRelations;
  }

  public function getOne($rid)
  {
    $qb = $this->createQueryBuilder('r');
    $qb->where(" r.relationid = :rid");
    $qb->setParameter("rid",$rid);
    $qy = $qb->getQuery();
     $rln =  $qy->getOneOrNullResult();
    dump($rln);

    return $rln;
  }



  public function findByActor($aid)
  {
    $qb = $this->createQueryBuilder('r');
    $qb->where(" r.actor1ref = :aid");
    $qb->orwhere(" r.actor2ref = :aid");
    $qb->setParameter("aid",$aid);
    $qy = $qb->getQuery();
    $rns = $qy->getResult();
    dump($rns);
    $Relations = array();
    foreach( $rns as $key=>$Relation)
    {
      $Relations[$Relation->getRelationId()]= $Relation;
    }
        return $Relations;
  }


    public function delete($gid,$aref,$pref)
    {
        $sql = "delete from App:Relation g ";
        $sql .= " where g.glimpseid = ".$gid." ";
        $sql .= " and g.roleref = ".$aref." ";
          $sql .= " and g.Relationref = ".$pref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->getResult();
    }

}

