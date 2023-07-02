<?php

namespace App\Repository;

use App\Entity\ActorRole;

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

    public function xfindRoles($aid)
    {
        $sql = "select r from App:ActorRole g JOIN App:Role r  ";
         $sql .= " where  g.roleref = r.roleid  ";
        $sql .= " and g.actorref = ".$aid." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $roles = $query->getResult();
        $aroles = array();
        foreach( $roles as $key=>$role)
        {
          $aroles[$role->getRoleid()]= $role;
        }
        return $aroles;
    }

    public function findOne($aref,$rref)
    {
        $sql = "select g from App:ActorRole g ";
        $sql .= " where g.actorref = ".$aref." ";
        $sql .= " and g.roleref = ".$rref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $roles = $query->getResult();

        return $roles[0];
    }

    public function delete($gid,$pref)
    {
        $sql = "delete from App:role g ";
        $sql .= " where g.glimpseid = ".$gid." ";
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
            $arole->{"role"}=  $content;
          }
          if( "App\Entity\Glimpse" == $contentname )
          {
            $arole->{"glimpse"}=  $content;
          }
          }
          $aroles[$i]= $arole;
        }

       /* foreach( $aroles as $key=>$role)
        {

            $gid = $role["r_glimpseref"];
            $qb = $this->createQueryBuilder('ar');
            $sql2 =  "select g from App:glimpse g ";
            $sql2 .= " where g.glimpseid = ".$gid." ";
            $query2 = $this->getEntityManager()->createQuery($sql2);
            $glimpses = $query2->getResult();
            dump($glimpses);
            $aroles[$role->getRoleid()]->glimpse =$glimpses[0];
            $sql3 =  "select r from App:role r ";
            $sql3 .= " where r.glimpseref = ".$gid." ";
            $query3 = $this->getEntityManager()->createQuery($sql3);
            $roles = $query3->getResult();
            $aroles[$role->getRoleid()]->glimpse->setRoles($roles);

        }*/
        return $aroles;
    }
}

