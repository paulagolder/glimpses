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
        $qy = $qb->getQuery();
        $actors = $qy->getResult();
        return $actors;
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


    public function delete($aid)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->delete();
        $qb->where('a.actorid = :aid ');
        $qb->setParameter('aid', $aid);
        $qb->getQuery()->execute();
    }

}

