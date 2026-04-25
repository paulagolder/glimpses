<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\MyLibrary;

use App\Service\Templates;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Persistence\ManagerRegistry;


use App\Entity\Actor;
use App\Entity\ActorRole;
use App\Entity\Role;
use App\Entity\Glimpse;
use App\Entity\Relation;
use App\Entity\RelationClue;
use App\Entity\LifeEvent;


use Symfony\Component\Config\FileLocator;

class RelationController extends AbstractController
{

    private $requestStack ;
       private $lib;
       private $templates;
       private $doctrine;

       public function __construct(private ManagerRegistry $adoctrine,MyLibrary $lib, Templates $templates,  RequestStack $request_stack, string $templatedir)
       {
           $this->requestStack = $request_stack;
           $this->lib = $lib;
           $this->templates = $templates;
           $this->doctrine = $adoctrine;
       }

   /* public function __construct(private ManagerRegistry $adoctrine, RequestStack $request_stack, string $templatedir)
    {

        // $ageyml = $templates->agelist; //fileLocator->locate('agelist3.yml', null, false);
        // $this->agelist =    $templates->agelist;
        $this->requestStack = $request_stack;
        $this->doctrine = $adoctrine;

    }*/

    public function showAll()
    {

            $pfield =   $this->lib->getCookieFilter('relation');
            if(is_numeric($pfield))
            {
              $relations = $doctrine->getRepository(Relation::class)->findOne($pfield);
            }elseif (is_null($pfield))
            {
               $relations = $doctrine->getRepository(Relation::class)->getAll();
            }
            else
            {
              $filter = "%".$pfield."%";
              $relations = $this->doctrine->getRepository(Relation::class)->seek($filter);
            }
        dump($relations);
        foreach($relations as &$relation)
        {
            $relation->{"actor1"}= $this->doctrine->getRepository(Actor::class)->findOne($relation->getActor1ref());
            $relation->{"actor2"}= $this->doctrine->getRepository(Actor::class)->findOne($relation->getActor2ref());
        }
        dump($relations);
        return $this->render(
            'relation/showall.html.twig',
            [
            'relations'=>$relations,
            'filter'=>$pfield,
            'returnlink'=>"/relation/showall",
            ]
        );
    }



    public function showone(ManagerRegistry $doctrine,$rid)
    {
        $relation = $doctrine->getRepository(Relation::class)->getOne($rid);
        $actor1 = $doctrine->getRepository(Actor::class)->findOne($relation->getActor1ref());
        $actor2 = $doctrine->getRepository(Actor::class)->findOne($relation->getActor2ref());

        dump($actor1);
        dump($actor2);

        $clues = $doctrine->getRepository(RelationClue::class)->findClues($rid);
        $cluelist=array();
        foreach($clues as $clue)
        {
            $clue->{"glimpse"} =  $doctrine->getRepository(Glimpse::class)->findOne($clue->getGlimpseRef());
              $clue->{"glimpse"}->{"roles"} =  $doctrine->getRepository(Role::class)->findChildren($clue->getGlimpseRef());
            $cluelist[]=$clue->getGlimpseRef();
        }
        dump($clues);
        $allroles = $doctrine->getRepository(Role::class)->getRelationClues($actor1,$actor2);
        $glimpses = array();
        $roles = array();
        foreach($allroles as &$role)
        {
            if((!in_array($role->getGlimpseref(), $cluelist, true)))
            {
                $roles[$role->getGlimpseref()]=$role;
                $role->{"glimpse"}=$doctrine->getRepository(Glimpse::class)->findOne($role->getGlimpseref());
                $glimpses[$role->getGlimpseref()] =  $doctrine->getRepository(Glimpse::class)->findOne($role->getGlimpseref());
            }
        }
        dump($roles);
        return $this->render(
            'relation/show.html.twig',
            [
            'relation'=>$relation,
            'actor1'=>$actor1,
            'actor2'=>$actor2,
            'clues'=>$clues,
            'roles'=>$roles,
            'returnlink'=>"/relation/showall",
            ]
        );
    }


 public function edit(ManagerRegistry $doctrine,$rid)
    {
        $relation = $doctrine->getRepository(Relation::class)->getOne($rid);
        $actor1 = $doctrine->getRepository(Actor::class)->findOne($relation->getActor1ref());
        $actor2 = $doctrine->getRepository(Actor::class)->findOne($relation->getActor2ref());

        dump($actor1);
        dump($actor2);

        $clues = $doctrine->getRepository(RelationClue::class)->findClues($rid);
        $cluelist=array();
        foreach($clues as $clue)
        {
            $clue->{"glimpse"} =  $doctrine->getRepository(Glimpse::class)->findOne($clue->getGlimpseRef());
              $clue->{"glimpse"}->{"roles"} =  $doctrine->getRepository(Role::class)->findChildren($clue->getGlimpseRef());
            $cluelist[]=$clue->getGlimpseRef();
        }
        dump($clues);
        $allroles = $doctrine->getRepository(Role::class)->getRelationClues($actor1,$actor2);
        $glimpses = array();
        $roles = array();
        foreach($allroles as &$role)
        {
            if((!in_array($role->getGlimpseref(), $cluelist, true)))
            {
                $roles[$role->getGlimpseref()]=$role;
                $role->{"glimpse"}=$doctrine->getRepository(Glimpse::class)->findOne($role->getGlimpseref());
                $glimpses[$role->getGlimpseref()] =  $doctrine->getRepository(Glimpse::class)->findOne($role->getGlimpseref());
            }
        }
        dump($roles);
        return $this->render(
            'relation/show.html.twig',
            [
            'relation'=>$relation,
            'actor1'=>$actor1,
            'actor2'=>$actor2,
            'clues'=>$clues,
            'roles'=>$roles,
            'returnlink'=>"/relation/showall",
            ]
        );
    }

    public function addclue(ManagerRegistry $doctrine,$rid, $gref)
    {

        $aclue = new RelationClue();
        $aclue->setRelationRef($rid);
        $aclue->setGlimpseRef($gref);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($aclue);
        $entityManager->flush();
        return $this->redirect("/relation/show/".$rid);
    }

    public function deleterole(ManagerRegistry $doctrine,$aid, $rid)
    {

        $em = $doctrine->getManager();
        $ar = $doctrine->getRepository(ActorRole::class)->findone($aid, $rid);
        $em->remove($ar);
        $em->flush();
        return $this->redirect("/actor/editroles/".$aid);
    }


    public function setfilter(ManagerRegistry $doctrine)
    {
            $request = $this->requestStack->getCurrentRequest();
            $pfield = $request->query->get('filter');
            if (is_null($pfield))
            {
                $this->lib->clearCookieFilter("relation");
            }else
            {
               $this->lib->setCookieFilter('relation',$pfield);
            }
            return $this->redirect("/relation/showall/");
    }

}
