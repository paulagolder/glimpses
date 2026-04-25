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


    public function seek($filter)
    {
       // $sql = "select s from App:Relation s ";
      //  $sql .= " where s.region LIKE '".$location."' ";
      //  $sql .= " or s.title LIKE '".$location."' ";
        $sql = "SELECT r FROM App:Relation r, App:Actor a1 , App:Actor a2 WHERE r.actor1ref = a1.actorid and r.actor2ref = a2.actorid  ";
        $sql .= "and ( a1.surname LIKE '".$filter."' or a2.surname LIKE '".$filter."')";
        dump($sql);
        $query = $this->getEntityManager()->createQuery($sql);
        $sources = $query->getResult();
        return $sources;
    }

}

