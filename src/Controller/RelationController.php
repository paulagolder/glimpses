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


    public function clearfilter(ManagerRegistry $doctrine)
    {
        $pfield = "";
        $this->lib->clearCookieFilter("relation");
        return $this->redirect("/relation/showall/");
    }


    public function showAll()
    {

            $filter =   $this->lib->getCookieFilter('relation');
            if(is_numeric($filter))
            {
              $relations = $doctrine->getRepository(Relation::class)->getOne($filter);
            }elseif (is_null($filter))
            {
               $relations = $doctrine->getRepository(Relation::class)->getAll();
            }
            else
            {
              $relations = $this->doctrine->getRepository(Relation::class)->filterf($filter);
            }
        //dump($relations);
        foreach($relations as &$relation)
        {
            $relation->{"actor1"}= $this->doctrine->getRepository(Actor::class)->getOne($relation->getActor1ref());
            $relation->{"actor2"}= $this->doctrine->getRepository(Actor::class)->getOne($relation->getActor2ref());
            $relation->{"mask"}= $this->getmask($relation);
        }
        //dump($relations);
        return $this->render(
            'relation/showall.html.twig',
            [
            'relations'=>$relations,
            'filter'=>$filter,
            'returnlink'=>"/relation/showall",
            ]
        );
    }



    public function showone(ManagerRegistry $doctrine,$rid)
    {
        $relation = $doctrine->getRepository(Relation::class)->getOne($rid);
        $actor1 = $doctrine->getRepository(Actor::class)->getOne($relation->getActor1ref());
        $actor2 = $doctrine->getRepository(Actor::class)->getOne($relation->getActor2ref());
        $clues = explode(",",trim($relation->getClues()));
        //dump($clues);
        $cluelist = array();
         foreach($clues as $clue)
        {
          if($clue != null && $clue != "")
          {
            $aglimpse =  $doctrine->getRepository(Glimpse::class)->getOne($clue);
            if($aglimpse != null)
            {
            $roles= $doctrine->getRepository(Role::class)->findChildren($clue);
            $aglimpse->roles = $roles;
             $cluelist[$clue] =  $aglimpse;
             }
          }

        }
    //dump($cluelist);
        $allrolerefs = $doctrine->getRepository(ActorRole::class)->getRelationRoles($relation->getActor1ref(),$relation->getActor2ref());
        $glimpses = array();
         //dump($allrolerefs);
               foreach($allrolerefs as $roleref)
               {
                   $arole =  $doctrine->getRepository(Role::class)->getOne($roleref->getRoleRef());
                   if((!in_array($arole->getGlimpseRef(), $cluelist, true)))
                   {
                       $gref = $arole->getGlimpseref();
                       $aglimpse = $doctrine->getRepository(Glimpse::class)->getOne($gref);
                       $allroles =   $doctrine->getRepository(Role::class)->findChildren($gref);
                       $aglimpse->roles = $allroles;
                       $arole->{"glimpse"}=$aglimpse;
                       $roles[$gref]=$arole;
                   }
               }
           //dump($roles);
        return $this->render(
            'relation/show.html.twig',
            [
            'relation'=>$relation,
            'actor1'=>$actor1,
            'actor2'=>$actor2,
            'clues'=>$cluelist,
            'roles'=>$roles,
            'returnlink'=>"/relation/showall",
            ]
        );
    }


 public function edit(ManagerRegistry $doctrine,$rid)
    {
        $relation = $doctrine->getRepository(Relation::class)->getOne($rid);
        $actor1 = $doctrine->getRepository(Actor::class)->getOne($relation->getActor1ref());
        $actor2 = $doctrine->getRepository(Actor::class)->getOne($relation->getActor2ref());
        $clues = explode(",",trim($relation->getClues()));
        $cluelist = array();
             foreach($clues as $clue)
                {
                if($clue != null && $clue != "")
                {

                         $aglimpse =  $doctrine->getRepository(Glimpse::class)->getOne($clue);
                                  if($aglimpse != null)
                                  {
                                  $roles= $doctrine->getRepository(Role::class)->findChildren($clue);
                                  $aglimpse->roles = $roles;
                                   $cluelist[$clue] =  $aglimpse;
                                   }
                }
            }
        $allrolerefs = $doctrine->getRepository(ActorRole::class)->getRelationRoles($relation->getActor1ref(),$relation->getActor2ref());
        $glimpses = array();
        $roles = array();
        foreach($allrolerefs as $roleref)
        {
            $arole =  $doctrine->getRepository(Role::class)->getOne($roleref->getRoleRef());
            if((!in_array($arole->getGlimpseRef(), $cluelist, true)))
            {
                   $gref = $arole->getGlimpseref();
                                       $aglimpse = $doctrine->getRepository(Glimpse::class)->getOne($gref);
                                       $allroles =   $doctrine->getRepository(Role::class)->findChildren($gref);
                                       $aglimpse->roles = $allroles;
                                       $arole->{"glimpse"}=$aglimpse;
                                       $roles[$gref]=$arole;
            }
        }
        return $this->render(
            'relation/edit.html.twig',
            [
            'relation'=>$relation,
            'actor1'=>$actor1,
            'actor2'=>$actor2,
            'clues'=>$cluelist,
            'roles'=>$roles,
            'returnlink'=>"/relation/showall",
            ]
        );
    }

    public function addclue(ManagerRegistry $doctrine,$rid, $gref)
    {
        $relation = $doctrine->getRepository(Relation::class)->getOne($rid);
        //dump($relation);
          //dump($gref);
        $relation->addclue($gref);
              //dump($relation);
        $entityManager = $doctrine->getManager();
   //     $entityManager->persist($relation);
        $entityManager->flush();
        return $this->redirect("/relation/edit/".$rid);
    }


     public function removeclue(ManagerRegistry $doctrine,$rid, $gref)
    {
        $relation = $doctrine->getRepository(Relation::class)->getOne($rid);
        $relation->removeclue($gref);
        $entityManager = $doctrine->getManager();
       // $entityManager->persist($relation);
        $entityManager->flush();
        return $this->redirect("/relation/edit/".$rid);
    }


    public function deleterole(ManagerRegistry $doctrine,$aid, $rid)
    {

        $em = $doctrine->getManager();
        $ar = $doctrine->getRepository(ActorRole::class)->getOne($aid, $rid);
        $em->remove($ar);
        $em->flush();
        return $this->redirect("/actor/editroles/".$aid);
    }

    public function delete(ManagerRegistry $doctrine, $rid)
    {

        $em = $doctrine->getManager();
        $relation = $doctrine->getRepository(Relation::class)->getOne($rid);
        $em->remove($relation);
        $em->flush();
        return $this->redirect("/actor/showall/");
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


     public function getMask($arelation): ?string
    {
       $actor1= $this->doctrine->getRepository(Actor::class)->getOne($arelation->getActor1ref());
       $mask= $actor1->getGenderSymbol();
       $actor2= $this->doctrine->getRepository(Actor::class)->getOne($arelation->getActor2ref());
       $mask .=" ".$actor2->getGenderSymbol();
       return $mask;
    }

}
