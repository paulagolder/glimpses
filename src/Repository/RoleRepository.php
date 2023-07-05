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

    public function filter($keyword)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where("r.name  like  :gid ");
        $qb->setParameter( "gid", $keyword);
        $qy = $qb->getQuery();
        $roles = $qy->getResult();
        $aroles = array();
        foreach( $roles as $key=>$role)
        {
            $aroles[$role->getroleid()]= $role;
        }
        return $aroles;
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

}

