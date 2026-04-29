<?php

namespace App\Repository;

use App\Entity\Role;
use App\Entity\Glimpse;

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
        dump($keywords);
        $kwarray = explode(",",$keywords);
        $qb = $this->createQueryBuilder('r');
        $i=0;
        foreach($kwarray as $key=>$kw)
        {
            $k= trim($kw);
             $qb->andwhere("r.name  like  :kw$i ");
              $qb->setParameter( "kw".$i, "%".$k."%");
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

    }

    public function getOne($rid)
    {
        $sql = "select r from App:role r ";
        $sql .= " where r.roleid = ".$rid." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $roles = $query->getResult();

        return $roles[0];
    }

    public function deleteOne($gid,$pref)
    {
        $sql = "delete from App:role g ";
        $sql .= " where g.glimpseref = ".$gid." ";
        $sql .= " and g.roleref = ".$pref." ";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->getResult();
    }


    public function deleteAllinGlimpse($gid)
    {
        $sql = "delete from App:role g ";
        $sql .= " where g.glimpseref = ".$gid." ";
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
        $qb->select('r1','r2');
        $qb->leftjoin('App:role', 'r2',\Doctrine\ORM\Query\Expr\Join::WITH, "r2.glimpseref  = r1.glimpseref ");
        //$qb->where("r2.glimpseref  = r1.glimpseref ");
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


     public function filterf($filterstring)
    {
        $filterlist = explode(",", $filterstring);
        dump($filterlist);
        $qb = $this->createQueryBuilder('r');
        $qb->select('r');
        $qb->from('App:Glimpse','g');
        $qb->where('  r.glimpseref = g.glimpseid  ');
        $clause = "";
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
           $clause .= " or  r.name like '".$namefilter."' or  r.predicates like '".$namefilter."' ";
        }
        dump($clause);
        $clause = preg_replace( "/ or/"," ",$clause ,1);
        dump($clause);
        $qb->andwhere($clause);
        $qb->orderby(' g.date ');
       // $qb->setparameter( 'name', $namefilter);
        dump($qb);
        $qy= $qb->getQuery();
        dump($qy);
        $roles = $qy->getResult();
        dump($roles);
        $n=0;
        $glimpses= array();
        foreach($roles as &$arole )
        {
        $glimpse = $this->getEntityManager()->getRepository(Glimpse::class)->findOne($arole->getGlimpseRef());
         $glimpses[$arole->getRoleid()] = $glimpse;
        }
         dump($glimpses);
        return $glimpses;
    }


}

