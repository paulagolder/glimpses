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


    public function filterf($filterpair)
    {
          $filter = explode("+",$filterpair);
          $sql = "SELECT r FROM App:Relation r, App:Actor a1 , App:Actor a2 WHERE r.actor1ref = a1.actorid and r.actor2ref = a2.actorid  ";
          dump($filter);
          if(count($filter)>1)
          {
             $namefilter ="%".$filter[0]."%".$filter[1]."%";
             $name0 = "%".$filter[0]."%";
             $name1 = "%".$filter[1]."%";
             $sql .= "and (( a1.surname LIKE '".$name1."' and a1.forename LIKE '".$name0."') or  ( a2.surname LIKE '".$name1."' and a2.forename LIKE '".$name0."'))";
          }
          else
          {
             $name0 = "%".$filter[0]."%";
             $sql .= "and ( a1.surname LIKE '".$name0."' or a1.forename LIKE '".$name0."' or  a2.surname LIKE '".$name0."' or a2.forename LIKE '".$name0."')";
          }
        dump($sql);
        $query = $this->getEntityManager()->createQuery($sql);
        $sources = $query->getResult();
        return $sources;
    }


     public function xfilterf($filterstring)
    {
        $filterlist = explode(",", $filterstring);
        dump($filterlist);
        $qb = $this->createQueryBuilder('r');
        $qb->select('r');
        $qb->from('App:Glimpse','g');
        $qb->where('  r.glimpseref = g.glimpseid  ');
        foreach($filterlist as $filterpair)
        {
           $filter = explode("+",$filterpair);
           dump($filter);
           if(count($filter)>1)
           {
           $namefilter ="%".$filter[0]."%".$filter[1]."%";
           }
            else
            {
            $namefilter ="%".$filter[0]."%";
            }
            $qb->andwhere('  r.name like :name or  r.predicates like :name ');
        }
       /* $qb->orwhere('  g.location like :name  ');
        $qb->orwhere('  r.name like :name ');
        $qb->orwhere('  r.predicates like :name ');
        $qb->orwhere('  g.location like :name  ');*/
        $qb->orderby(' g.date ');
        $qb->setparameter( 'name', $namefilter);
        dump($qb);
        $qy= $qb->getQuery();
        dump($qy);
        $roles = $qy->getResult();
        dump($roles);
        $n=0;
        //not happy with this but it works
        foreach($roles as &$arole )
        {
        $glimpse = $this->getEntityManager()->getRepository(Glimpse::class)->findOne($arole->getGlimpseRef());
         $glimpses[$arole->getRoleid()] = $glimpse;
        }
         dump($glimpses);
        return $glimpses;
    }


}

