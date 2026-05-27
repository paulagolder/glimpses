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

    public function getgendernamelist()
      {
          $qb = $this->createQueryBuilder('g');
          $qy = $qb->getQuery();
          $actors = $qy->getResult();
          $gendernames = array();
          foreach($actors as $actor)
          {
               $forename = $actor->getForename();
               $n=0;
               if($actor->getGender() =="female") $n=-1;
               if($actor->getGender()=="male")$n=+1;
               if(array_key_exists($forename, $gendernames))
               {
               $gendernames[$forename] += $n;
               }else
               {
                 $gendernames[$forename] = $n;
               }
          }
          return $gendernames;
      }



    public function getOne($aid)
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

         $sql = "SELECT g FROM App\Entity\Actor g  ";
         $name0= $actor->getForename()."+".$actor->getSurname();
         $sql .= " where concat( g.forename,'+',g.surname) = '{$name0}' ";
          $filterlist = explode(",",$actor->getKeywords());
           foreach($filterlist as $filterpair)
           {
           if($filterpair != "")
           {
             $filter = explode("+",$filterpair);

                      if(count($filter)>1)
                      {
                         $namec ="%".$filter[0]."%".$filter[1]."%";
                         $sql .= " or( concat( g.forename,'+',g.surname) like '{$namec}'  )  ";

                      }else
                      {
                         $name1 ="%".$filter[0]."%";
                         $sql .= " or g.forename like '{$name1}'  or  g.surname like '{$name1}'  ";
                      }
           }
       }

           $query = $this->getEntityManager()->createQuery($sql);
           $actors = $query->getResult();
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


      public function findRoleMatches($arole)
      {
         dump($arole);
         $names = explode(" ",$arole->getName());
         dump($names);
           $qb = $this->createQueryBuilder('g');
              $qb -> where(" g.surname = :surname ");
              $qb->setParameter('surname', $names[1]);
              $qb -> orwhere(" g.forename = :forename ");
              $qb->setParameter('forename', $names[0]);
              $qb->orderby("g.surname , g.forename");
              $qy = $qb->getQuery();
              $actors = $qy->getResult();
              return $actors;

      }

    public function exists($anactor)
    {
    //dump($anactor);
        $qb = $this->createQueryBuilder('g');
        $qb -> where(" g.surname = :surname ");
        $qb->setParameter('surname', $anactor->getSurname());
        $qb -> andwhere(" g.forename = :forename ");
        $qb->setParameter('forename', $anactor->getForename());
        $qb -> andwhere(" g.specifier = :specifier ");
        $qb->setParameter('specifier', $anactor->getSpecifier());
        $qy = $qb->getQuery();
        $actors = $qy->getResult();
        if(count($actors)<1)return null;
        else return  $actors[0]->getActorId();
    }


    public function delete($aid)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->delete();
        $qb->where('a.actorid = :aid ');
        $qb->setParameter('aid', $aid);
        $qb->getQuery()->execute();
    }


    public function updategender($name,$gender)
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $query = $queryBuilder->update('App:Actor', 'a')
                ->set('a.gender', ':gender')
                ->where('a.forename = :name')
                ->setParameter('name', $name)
                ->setParameter('gender', $gender)
                ->getQuery();
        $result = $query->execute();

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
                      //dump($filter);
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
              $sql .= " order  by  a.surname , a.forename " ;
           //dump($sql);
                 $query = $this->getEntityManager()->createQuery($sql);
                  $actors = $query->getResult();
           //dump($actors);
           return $actors;
       }

}

