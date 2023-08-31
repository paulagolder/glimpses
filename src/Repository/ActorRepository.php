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

}

