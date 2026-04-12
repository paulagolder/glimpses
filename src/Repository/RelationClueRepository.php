<?php

namespace App\Repository;

use App\Entity\RelationClue;
use App\Entity\Role;


use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\ORM\Query;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\DBAL\Driver\Connection;


class RelationClueRepository extends EntityRepository
{



    public function findOne($aref,$rref)
    {
        $sql = "select g from App:RelationClue g ";
        $sql .= " where g.relationref = ".$aref." ";
        $sql .= " and g.roleref = ".$rref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $roles = $query->getResult();

        return $roles[0];
    }


    public function findClues($aref)
    {
      $sql = "select g from App:RelationClue g ";
      $sql .= " where g.relationref = ".$aref." ";
      $query = $this->getEntityManager()->createQuery($sql);
      $roles = $query->getResult();

      return $roles;
    }

    public function delete($gid,$pref)
    {
        $sql = "delete from App:RelationClue g ";
        $sql .= " where g.relationref = ".$gid." ";
        $sql .= " and g.roleref = ".$pref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->getResult();
    }

      public function findRoles($aid)
    {
        $qb = $this->createQueryBuilder('ar');
        $qb->leftJoin('App:Role', 'r', 'WITH', ' ar.roleref = r.roleid ');
        $qb->addSelect('r');
        $qb->leftJoin('App:Glimpse', 'g', 'WITH', ' r.glimpseref = g.glimpseid ');
        $qb->addSelect('g');
        $qb->where ("  ar.actorref = :aid ");
        $qb->addOrderBy('g.date', 'ASC');
        $qb->setParameter('aid', $aid );
         $roles= $qb->getQuery()->getResult();
        $aroles = array();
        $i=0;
        foreach( $roles as $key=>$content)
        {
          if($content)
          {
          $contentname = get_class($content);
          if( "App\Entity\ActorRole" == $contentname )
          {
            $arole= $content;
            $i=$i+1;
          }
          if( "App\Entity\Role" == $contentname )
          {
            $arole->{"role"}=  $content->getRole();
            $arole->{"rolename"}=  $content->getName();
              $arole->{"predicates"}=  $content->getPredicatestr();
            $gref =  $content->getGlimpseref();
          }
          if( "App\Entity\Glimpse" == $contentname )
          {
            $arole->{"glimpse"}=  $content;
              $arole->{"glimpse"}->{"roles"}=   $this->getEntityManager()->getRepository(Role::class)->findChildren($gref);
          }
          }
          $aroles[$i]= $arole;
        }

        return $aroles;
    }
}

