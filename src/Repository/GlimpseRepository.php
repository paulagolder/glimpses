<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\ORM\EntityRepository;



class GlimpseRepository extends EntityRepository
{


    public function getOne($gid)
    {
        $qb = $this->createQueryBuilder('g');
        $qb->where('g.glimpseid = :gid ');
        $qb->setParameter('gid', $gid);
        $glimpse = $qb->getQuery()->getOneOrNullResult();

        return $glimpse;
    }

    public function findAll()
    {
        $qb = $this->createQueryBuilder('g');
        $qb->orderby('g.date ');
        $glimpses = $qb->getQuery()->getResult();

        return $glimpses;
    }

    public function delete($gid)
    {
        $sql = 'delete from App:Glimpse g ';
        $sql .= ' where g.glimpseid = '.$gid.' ';
        $query = $this->getEntityManager()->createQuery($sql);
        $query->getResult();
    }

    public function viewregion($region)
    {
        $sql = 'select g from App:glimpse g ';
        $sql .= " where g.location = '".$region."' ";
        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();

        return $glimpses;
    }

    public function viewsource($sourceid)
    {
        $qb = $this->createQueryBuilder('g');
        $qb->where('g.sourceid = :sourceid ');
        $qb->orderby('g.date');
        $qb->setParameter('sourceid', $sourceid);
        $sources = $qb->getQuery()->getResult();
        foreach ($sources as &$source) {
            $gid = $source->getGlimpseid();
            $roles = $this->getEntityManager()->getRepository(Role::class)->findChildren($gid);
            $source->{'roles'} = $roles;
        }

        // dump($sources);
        return $sources;
    }

  public function findMatch($type,$date,$name)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'select g from App:Glimpse g , App:Role r where  g.glimpseid = r.glimpseref ';
        $sql .= " and g.type= '$type' and g.date = '$date' and r.name = '$name'  " ;


        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();
        $n = 0;

        foreach ($glimpses as &$glimpse) {
            $roles = $this->getEntityManager()->getRepository(Role::class)->findChildren($glimpse->getGlimpseid());
            $glimpse->{'role'} = $roles;
        }

        return $glimpses;
    }





    public function filterf($filterlist)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'select g from App:Glimpse g , App:Role r where  g.glimpseid = r.glimpseref ';
        $n = 0;
        $globalloc=null;
        foreach ($filterlist as $seeklist)
        {
          $locstring= null;
          if( $seeklist[2] != null)
          {
              $aloc = $seeklist[2];
              $locstring = "%".$aloc."%";
          }
          if($seeklist[0]!= null)
          {
             if($seeklist[1] != null)
             {
               $namefilter = '%'.$seeklist[0].'%'.$seeklist[1].'%';
             }
             else
             {
               $namefilter = '%'.$seeklist[0].'%';
             }
          }
          else
          {
             if($seeklist[1] != null)
             {
                 $namefilter = '%'.$seeklist[1].'%';
              }
               else
              {
                $namefilter= null;
              }
          }

          if($locstring != null and $namefilter == null)
           {
             $globalloc = $locstring;
             break;
           }
            if ($n > 0)
            {
                $sql .= ' or ';
             }
             else
             {
               $sql .= ' and  ';//' and ( '
              }
        if($locstring!= null and $namefilter != null )
        {
           $sql .= " (( r.name like '".$namefilter."'  or  r.predicates like '".$namefilter."' ) and g.location like '".$locstring."' ) " ;
        }
        else if($namefilter != null )
        {
            $sql .= " ( r.name like '".$namefilter."' or  r.predicates like '".$namefilter."' )";
        }
        else
        {
         $sql .= " 1=1 ";
        }
            ++$n;
        }

        if( $globalloc != null)
        {
          $sql .= " and  g.location like '".$locstring."'  order by  g.date ";
        }
        else
        {
          $sql .= ' order by  g.date ';
        }

        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();
        $n = 0;

        foreach ($glimpses as &$glimpse) {
            $roles = $this->getEntityManager()->getRepository(Role::class)->findChildren($glimpse->getGlimpseid());
            $glimpse->{'role'} = $roles;
        }

        return $glimpses;
    }

    public function findDuplicates($glimpse1)
    {
        $conn = $this->getEntityManager()->getConnection();
        // dump($glimpse1);
        $sql = "select g from App:Glimpse g  where  g.type = '".$glimpse1->getType()."' and  g.date = '".$glimpse1->getDate()."' ";
        $query = $this->getEntityManager()->createQuery($sql);
        $glimpses = $query->getResult();
        $n = 0;
        foreach ($glimpses as &$glimpse) {
            $roles = $this->getEntityManager()->getRepository(Role::class)->findChildren($glimpse->getGlimpseid());
            $glimpse->{'role'} = $roles;
        }

        return $glimpses;
    }

    public function Countglimpses($sourceid)
    {
        $sql = 'select  min(g.date),max(g.date),count(g) from App:Glimpse as g ';
        $sql .= " where g.sourceid = $sourceid  group by g.sourceid";
        $query = $this->getEntityManager()->createQuery($sql);
        $results = $query->getResult();
        if ($results) {
            return $results[0];
        }

        return [0, 0, 0, 0];
    }
}
