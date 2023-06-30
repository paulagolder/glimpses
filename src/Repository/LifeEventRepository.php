<?php

namespace App\Repository;

use App\Entity\LifeEvent;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\DBAL\Driver\Connection;


class LifeEventRepository extends EntityRepository
{

    public function findEvent($aid,$type)
    {
        $sql = "select l from App:Lifeevent  l";
        $sql .= ' where  l.actorref ='. $aid  ;
        $sql .= " and l.eventtype = '".$type."' ";
           dump($sql);
        $query = $this->getEntityManager()->createQuery($sql);
        $events = $query->getResult();
        if(count($events)>0) return $events[0];
        else return null;
    }

      public function findAllEvents($aid)
    {
        $sql = "select l from App:Lifeevent  l";
        $sql .= ' where  l.actorref ='. $aid  ;
        $query = $this->getEntityManager()->createQuery($sql);
        $events = $query->getResult();
        return $events;
    }

    public function findOne($aref,$rref)
    {
        $sql = "select g from ActorRole::class g ";
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
        $sql = "select r from ActorRole::class a JOIN App:Role r ";
        $sql .= " where r.roleid = a.roleref  ";
        $sql .= " and a.actorref = ".$aid;
        $query = $this->getEntityManager()->createQuery($sql);
        $roles = $query->getResult();
        $aroles = array();
        foreach( $roles as $key=>$role)
        {
          $aroles[$role->getRoleid()]= $role;
        }
        foreach( $aroles as $key=>$role)
        {
            $gid = $role->getGlimpseref();
            $sql2 =  "select g from App:Glimpse g ";
            $sql2 .= " where g.glimpseid = ".$gid." ";
            $query2 = $this->getEntityManager()->createQuery($sql2);
            $glimpses = $query2->getResult();
            $aroles[$key]->glimpse =$glimpses[0];
            $sql3 =  "select r from App:role r ";
            $sql3 .= " where r.glimpseref = ".$gid." ";
            $query3 = $this->getEntityManager()->createQuery($sql3);
            $roles = $query3->getResult();
            $aroles[$key]->glimpse->setRoles($roles);
        }
        return $aroles;
    }
}

