<?php

namespace App\Repository;

use App\Entity\Actor;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query;
use Doctrine\ORM\Doctrine_Core;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\DBAL\Driver\Connection;


class ActorRepository extends EntityRepository
{

    public function findAll()
    {
        $qb = $this->createQueryBuilder('g');
        $qb->orderBy(" g.surname , g.forename ");
        $qy = $qb->getQuery();
        $actors = $qy->getResult();
        return $actors;
    }

    public function findAllIndexed()
    {
        $qb = $this->createQueryBuilder('g');
        $qy = $qb->getQuery();
        $actors = $qy->getResult();
        $indexedactors = array();
        foreach($actors as $actor)
        {
             $indexedactors[$actor->getActorId()] = $actor;
        }
        return $indexedactors;
    }

    public function findOne($aid)
    {
        $qb = $this->createQueryBuilder('g');
        $qb -> where(" g.actorid = :aid ");
        $qb->setParameter('aid', $aid);
        $qy = $qb->getQuery();
        $actor = $qy->getOneOrNullResult();
        return $actor;
    }

    public function findAllMatching($actor)
    {
        $qb = $this->createQueryBuilder('g');
        $qb -> where(" g.forename = :afname ");
        $qb->setParameter('afname', $actor->getForename());
        $qb ->andwhere(" g.surname = :asname ");
        $qb->setParameter('asname', $actor->getSurname());
        $qy = $qb->getQuery();
          $actors = $qy->getResult();
        return $actors;
    }

    public function findDups($surname,$forename)
    {
        $qb = $this->createQueryBuilder('g');
        $qb -> where(" g.surname = :surname ");
        $qb->setParameter('surname', $surname);
        $qb -> andwhere(" g.forename = :forename ");
        $qb->setParameter('forename', $forename);
        $qy = $qb->getQuery();
        $actors = $qy->getResult();
        return $actors;
    }

    public function delete($aid)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->delete();
        $qb->where('a.actorid = :aid ');
        $qb->setParameter('aid', $aid);
        $qb->getQuery()->execute();
    }


 public function filterf($filterstr)
       {
           $qb = $this->createQueryBuilder('a');
           $qb->select();
           $sql = "SELECT a FROM App\Entity\Actor a WHERE ";
           $filterlist = explode(",",$filterstr);
           $n=0;
           foreach($filterlist as $filterpair)
           {
             $filter = explode("+",$filterpair);
                      dump($filter);
                      if($n>0) $sql .=" or ";
                      if(count($filter)>1)
                      {
                         $namec ="%".$filter[0]."%".$filter[1]."%";
                         $name1= "%".$filter[0]."%";
                         $name2= "%".$filter[1]."%";
                         $sql .= "  a.keywords like '{$namec}'  or ( a.surname like '{$name2}'  and a.forename like '{$name1}' )  ";

                      }else
                      {
                         $name1 ="%".$filter[0]."%";
                         $sql .= "  a.keywords like '{$name1}'  or  a.surname like '{$name1}'  or a.forename like '{$name1}'   ";
                      }
             $n++;
                  }
           dump($sql);
                 $query = $this->getEntityManager()->createQuery($sql);
                  $actors = $query->getResult();
           dump($actors);
           return $actors;
       }

}

