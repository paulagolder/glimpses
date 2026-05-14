<?php

namespace App\Repository;

use App\Entity\ActorRole;
use App\Entity\Role;


use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\ORM\Query;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\DBAL\Driver\Connection;


class ActorRoleRepository extends EntityRepository
{

    public function getRolesIndexed($aid)
    {
      $sql = "select g from App:ActorRole g ";
      $sql .= " where g.actorref = ".$aid." ";

        $query = $this->getEntityManager()->createQuery($sql);
        $roles = $query->getResult();
        $aroles = array();
        foreach( $roles as $key=>$role)
        {
          $aroles[$role->getRoleRef()]= $role;
        }
        return $aroles;
    }

    public function getActors($rid)
    {
      $sql = "select a from App:ActorRole g , App:Actor a  ";
      $sql .= " where g.roleref = ".$rid." and g.actorref = a.actorid ";

        $query = $this->getEntityManager()->createQuery($sql);
        $actors = $query->getResult();
        return $actors;
    }

    public function findOne($aref,$rref)
    {
        $sql = "select g from App:ActorRole g ";
        $sql .= " where g.actorref = ".$aref." ";
        $sql .= " and g.roleref = ".$rref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $roles = $query->getResult();
        dump($roles);
        return $roles;
    }

    public function delete($aref,$rref)
    {
      $sql = "delete from App:ActorRole g ";
      $sql .= " where g.actorref = ".$aref." ";
      $sql .= " and g.roleref = ".$rref." " ;
        $query = $this->getEntityManager()->createQuery($sql);
        $query->getResult();
    }


    public function deleteByActor($aid)
    {
      $sql = "delete from App:ActorRole g  ";
      $sql .= " where g.actorref = ".$aid." ";
      $query = $this->getEntityManager()->createQuery($sql);
      $query->getResult();
    }

      public function xfindRoles($aid)
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
        dump($qb);
           dump($aroles);
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


       public function getRoles($aid)
       {
           $sql = "SELECT  r FROM App:actorrole ar , App:Role r , App:Glimpse g where  ar.roleref = r.roleid  ";
           $sql .=  " and  r.glimpseref = g.glimpseid  and   ar.actorref = ".$aid."  ORDER BY g.date ASC ";
           $query = $this->getEntityManager()->createQuery($sql);
           $roles = $query->getResult();
           return $roles;
       }

       public function getActorIds($rid)
          {
              $sql = "SELECT  ar.actorref FROM App:actorrole ar where  ar.roleref =  ".$rid;
              $query = $this->getEntityManager()->createQuery($sql);
              $actorids = $query->getResult();
              return $actorids;
          }

       public function getActorRoles($aid)
       {
              $sql = "SELECT  ar FROM App:actorrole ar , App:Role r , App:Glimpse g where  ar.roleref = r.roleid  ";
              $sql .=  " and  r.glimpseref = g.glimpseid  and   ar.actorref = ".$aid."  ORDER BY g.date ASC ";
              $query = $this->getEntityManager()->createQuery($sql);
              $aroles = $query->getResult();
              return $aroles;
       }



       public function getRelationRoles($aid1,$aid2)
       {
              $sql = "SELECT  ar FROM App:actorrole ar , App:Role r , App:Glimpse g where  ar.roleref = r.roleid  ";
              $sql .=  " and  r.glimpseref = g.glimpseid  and  ( ar.actorref = ".$aid1." or ar.actorref = ".$aid2.") ORDER BY g.date ASC ";
              $query = $this->getEntityManager()->createQuery($sql);
              $roles = $query->getResult();
              return $roles;
       }
}

