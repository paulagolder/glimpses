<?php

namespace App\Repository;

use App\Entity\Role;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\DBAL\Driver\Connection;


class RoleRepository extends EntityRepository
{

    public function findChildren($gid)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where("r.glimpseref = :gid ");
        $qb->setParameter( "gid", $gid);
        $qy = $qb->getQuery();
        $roles = $qy->getResult();
        $aroles = array();
        foreach( $roles as $key=>$role)
        {
          $aroles[$role->getroleid()]= $role;
        }
        return $aroles;
    }

    public function filter($keywords)
    {
        $kwarray = explode("+",$keywords);
        $qb = $this->createQueryBuilder('r');
        $i=0;
        foreach($kwarray as $key=>$kw)
        {
             $qb->where("r.name  like  :kw$key ");
              $qb->setParameter( "kw".$key, "%".$kw."%");
              $i=$i+1;
        }
        $qy = $qb->getQuery();
        dump($qy);
        $roles = $qy->getResult();
        $aroles = array();
        foreach( $roles as $key=>$role)
        {
            $aroles[$role->getroleid()]= $role;
        }
        return $aroles;

        /*
         *    ->where("firstname LIKE ? AND surname LIKE ?")
         - >setParameter(0, $user_input_1st_name) *
         ->setParameter(1, $user_input_2nd_name);
         */
    }

    public function findOne($gid,$rid)
    {
        $sql = "select r from App:role r ";
        $sql .= " where r.glimpseref = ".$gid." ";
        $sql .= " and r.roleid = ".$rid." ";
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


    public function getRelationClues($actor1,$actor2)
    {
        $kw1 = $actor1->getSurname();
        $fn1 = $actor1->getForename();
        $kw2 = $actor2->getSurname();
         $fn2 = $actor2->getForename();
        $qb = $this->createQueryBuilder('r1');
        $qb->select('r2');
         $qb->select('r2');
        $qb->leftjoin('App:role', 'r2',\Doctrine\ORM\Query\Expr\Join::WITH, "r2.glimpseref  = r1.glimpseref ");
        $qb->where("r2.glimpseref  = r1.glimpseref ");
        $qb->andwhere("r2.name  like  :kw2");
        $qb->andwhere("r1.name  like  :kw1");
        $qb->andwhere("r1.name  like  :fn1");
        $qb->andwhere("r2.name  like  :fn2");
        $qb->setParameter( "kw1", "%".$kw1."%");
        $qb->setParameter( "fn1", "%".$fn1."%");
          $qb->setParameter( "fn2", "%".$fn2."%");
        $qb->setParameter( "kw2", "%".$kw2."%");
        $qy = $qb->getQuery();
        dump($qy);
        $roles = $qy->getResult();
        $aroles = array();
        foreach( $roles as $key=>$role)
        {
            $aroles[$role->getroleid()]= $role;
        }
        return $aroles;
    }



}

