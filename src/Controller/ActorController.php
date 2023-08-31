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
use App\Entity\LifeEvent;


use Symfony\Component\Config\FileLocator;

class ActorController extends AbstractController
{

    private $requestStack ;
    private $lib;
    private $templates;

    public function __construct( MyLibrary $lib, Templates $templates,  RequestStack $request_stack, string $templatedir)
    {

        $this->requestStack = $request_stack;
        $this->lib = $lib;
        $this->templates = $templates;
    }

    public function show(ManagerRegistry $doctrine,$aid)
    {
        $actor = $doctrine->getRepository(Actor::class)->findOne($aid);
        $actors = $doctrine->getRepository(Actor::class)->findAllIndexed();
        dump($actor);
        $lifeevents =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        $relations = $doctrine->getRepository(Relation::class)->findByActor($aid);
        dump($relations);
        $roles =  $doctrine->getRepository(ActorRole::class)->findroles($aid);
        dump($roles);
        $dups =  $doctrine->getRepository(Actor::class)->findDups($actor->getSurname(),$actor->getForename());
        dump($dups);
        return $this->render(
            'actor/show.html.twig',
            [
            'actor'=>$actor,
            'actors'=>$actors,
            'lifeevents'=>$lifeevents,
            'relations'=>$relations,
            'roles'=>$roles,
            'dups'=>$dups,
            'returnlink'=>"/actor/show/$aid",
            ]
        );
    }


    public function  edit(ManagerRegistry $doctrine,$aid)
    {
        $actor = $doctrine->getRepository(Actor::class)->findOne($aid);
        $roles =  $doctrine->getRepository(ActorRole::class)->findRoles($aid);
        $nrole = new ActorRole();
        $nrole->setActorref($aid);
        $roles[]=$nrole;
        return $this->render('actor/edit.html.twig', array(
            'actor' => $actor,
            'roles'=>$roles,
            'returnlink' => "/actor/show/".$aid,
            'typelist' =>['baptism','marriage', 'burial'],
        ));

    }

    public function  editroles(ManagerRegistry $doctrine,$aid)
    {
        $em = $doctrine->getManager();
        $actor = $doctrine->getRepository(Actor::class)->findOne($aid);
        $actors = $doctrine->getRepository(Actor::class)->findAllIndexed();
        if($actor->getKeywords())
        {
            $gfilter = $actor->getKeywords();
        }else
        {
            $gfilter = $actor->getSurname().", ".$actor->getForename();
        }
        dump($gfilter);

        $lifeevents =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        dump($lifeevents);

        $relations = $doctrine->getRepository(Relation::class)->findByActor($aid);
        foreach($relations as &$relation)
        {
            $relation->{"actor1"}= $em->getRepository(Actor::class)->findOne($relation->getActor1ref());
            $relation->{"actor2"}= $em->getRepository(Actor::class)->findOne($relation->getActor2ref());
        }


        $roles =  $doctrine->getRepository(ActorRole::class)->findRoles($aid);
          dump($roles);
        $froles = array();
        foreach($roles as &$role)
        {
            dump($role);
            $froles[$role->getRoleref()]=$role;
            if( property_exists('role', '"glimpse"'))
            {
                $dates = $this->templates->getLifeEvents($aid,$role->glimpse->getType(),$role->{"role"},$role->glimpse->getDate());
                dump($dates);
                $this->templates->updateLifeEvents($lifeevents,$dates);
                dump($lifeevents);
            }
            else
            {
                dump(" missing glimpse ".$role->getRoleRef());
                   $arole =  $doctrine->getRepository(Role::class)->getOne($role->getRoleRef());
                $gref =$arole->getGlimpseref();
                 $glimpse = $doctrine->getRepository(Glimpse::class)->findOne($gref);
                 dump($glimpse);

            }
        }
        dump($froles);

        $sroles = $doctrine->getRepository(Role::class)->filter($gfilter);
        $croles = array();
        dump($sroles);
        foreach($sroles as &$srole)
        {

            if(! array_key_exists($srole->getRoleid(), $froles))
            {
                $srole->glimpse = $doctrine->getRepository(Glimpse::class)->findOne($srole->getGlimpseref());
                dump($srole);
                if(!is_null($srole->glimpse))
                {
                    $srole->glimpse->roles = $doctrine->getRepository(Role::class)->findChildren($srole->getGlimpseref());
                    $croles[]=$srole;
                }

            }
        }
        dump($croles);

        $duplicates = $doctrine->getRepository(Actor::class)->findAllMatching($actor);

        return $this->render('actor/editroles.html.twig', array(
            'actor' => $actor,
            'actors'=>$actors,
            'lifeevents'=>$lifeevents,
            'relations'=>$relations,
            'roles'=>$froles,
            'returnlink' => "/actor/show/".$aid,
            'xroles' => $croles,
            'duplicates'=>$duplicates,
            'typelist' =>['baptism','marriage', 'burial', 'inventory', 'will'],
        ));

    }


    public function  new(ManagerRegistry $doctrine)
    {
        $actor = new Actor();
        $actor->setSurname("name...");
        $actor->setForename("forename...");

        $actor->setContributor("paul");
        $now = new \DateTime();
        $actor->setUpdateDt($now);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($actor);
        $entityManager->flush();
        $aid = $actor->getActorid();

        return $this->render('actor/edit.html.twig', array(
            'actor' => $actor,
            'returnlink' => "/actor/show/".$aid,
        ));

    }

    public function  newactor(ManagerRegistry $doctrine,$gid,$rref)
    {
        $role =   $doctrine->getRepository(Role::class)->find($rref);
        $glimpse = $doctrine->getRepository(Glimpse::class)->find($gid);
        $actor = new Actor();
        $names =  explode(" ", $role->getName()."  ?");
        $actor->setForename($names[0]);
        $actor->setSurname($names[1]);

        $specifier = $role->getRole()." ".$glimpse->getDate()." ".$role->getPredicatestr();

        $actor->setContributor("paul");
        $now = new \DateTime();
        $actor->setUpdateDt($now);
        $actor->setSpecifier($specifier);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($actor);
        $entityManager->flush();
        $aid = $actor->getActorid();

        return $this->render('actor/edit.html.twig', array(
            'actor' => $actor,
            'returnlink' => "/actor/show/".$aid,
        ));
    }


    public function  newrelationship(ManagerRegistry $doctrine,$aid)
    {
        $actor =  $doctrine->getRepository(Actor::class)->findOne($aid);
        $actors =  $doctrine->getRepository(Actor::class)->findAll();
        $relationships = ["father","mother","son","daughter","wife","husband", "sister", "brother"];
        $relations = $doctrine->getRepository(Relation::class)->findByActor($aid);
        return $this->render('actor/newrelationship.html.twig', array(
            'actor' => $actor,
            'actors'=>$actors,
            'relations'=>$relations,
            'relationships'=>$relationships,
            'returnlink' => "/actor/show/".$aid,
        ));
    }

    public function  delete(ManagerRegistry $doctrine,$aid)
    {
        $roles =  $doctrine->getRepository(Actor::class)->delete($aid);
        return $this->redirect("/actor/showall/");
    }


    public function  merge(ManagerRegistry $doctrine,$aid,$daid)
    {

        $actor1 =  $doctrine->getRepository(Actor::class)->findOne($aid);
        $actor2 =  $doctrine->getRepository(Actor::class)->findOne($daid);
        $lifeevents1 =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        $lifeevents2 =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($daid);
        LifeEvent::merge($lifeevents1,$lifeevents2 );
          $doctrine->getRepository(LifeEvent::class)->deleteAll($daid);
         $doctrine->getRepository(Actor::class)->delete($daid);
        return $this->redirect("/actor/editroles/".$aid);
    }


    public function  compare(ManagerRegistry $doctrine,$aid,$daid)
    {

        $actor1 =  $doctrine->getRepository(Actor::class)->findOne($aid);
        $actor2 =  $doctrine->getRepository(Actor::class)->findOne($daid);
        $lifeevents1 =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        $lifeevents2 =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($daid);
        $roles1 =   $doctrine->getRepository(ActorRole::class)->findRoles($aid);
          $roles2 =   $doctrine->getRepository(ActorRole::class)->findRoles($daid);
     //   LifeEvent::merge($lifeevents1,$lifeevents2 );
    //    return $this->redirect("/actor/editroles/".$aid);


       // $lifeevents1 =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
     //   $relations1 = $doctrine->getRepository(Relation::class)->findByActor($aid);

     //   $lifeevents2 =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($daid);
     //   $relations2 = $doctrine->getRepository(Relation::class)->findByActor($daid);

        return $this->render(
            'actor/match.html.twig',
            [
            'actor1'=>$actor1,
            'actor2'=>$actor2,
             'lifeevents1'=>$lifeevents1,
              'lifeevents2'=> $lifeevents2,
              'roles1'=>$roles1,
              'roles2'=>$roles2,
            'returnlink'=>"/actor/show/$aid",
            ]
        );
    }

    public function  delete_role(ManagerRegistry $doctrine,$aid,$rid)
    {
        $doctrine->getRepository(ActorRole::class)->delete($aid,$rid);
        return $this->redirect("/actor/edit/".$aid);
    }


    public function  process_edit(ManagerRegistry $doctrine,$aid)
    {
        if($aid <1)
        {
            $actor = new Actor();
            $actorroles = array();
        }
        else
        {
            $actor = $doctrine->getRepository(Actor::class)->findOne($aid);
            $actorroles =  $doctrine->getRepository(ActorRole::class)->findRoles($aid);
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {

            $actor->setSurname($request->request->get('_surname'));
            $actor->setForename($request->request->get('_forename'));
            $actor->setSpecifier($request->request->get('_specifier'));
            $actor->setText($request->request->get('_text'));
            $actor->setBirthdate($request->request->get('_birthdate'));
            $actor->setDeathdate($request->request->get('_deathdate'));
            $actor->setKeywords($request->request->get('_keywords'));
            $actor->setContributor("paul");
            $now = new \DateTime();
            $actor->setUpdateDt($now);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($actor);
            $entityManager->flush();
            $aid = $actor->getActorid();
            $par = $request->request->get('_role');

            dump($par);
            if(!is_null($par))
            {
                foreach($par as $key=>$arole)
                {
                    dump($key);
                    dump($arole);
                    $ref = $arole["'roleref'"];
                    if($ref==null || $ref==0)
                    {
                        $nrole = new role();
                        $nrole->setActorRef($aid);
                        $nrole->setRole($arole["'role'"]);
                        $nrole->setName($arole["'name'"]);
                        if($nrole->getName())
                        {
                            $entityManager->persist($nrole);
                            $entityManager->flush();
                        }
                    }else
                    {
                        $nrole = $roles[$key];
                        #$nrole->setText($arole["'text'"]);
                        $nrole->setRole($arole["'role'"]);;
                        $entityManager->persist($nrole);
                        $entityManager->flush();
                    }
                }
            }
            return $this->redirect("/actor/edit/".$aid);

        }

        return $this->render('actor/edit.html.twig', array(

            'actor' => $actor,
            'roles'=>$roles,
            'returnlink' => "/actor/show/".$gid,
            'typelist' =>['baptism','marriage', 'burial'],
        ));

    }

    public function  process_editrole(ManagerRegistry $doctrine,$gid,$aref)
    {

        $actor = $doctrine->getRepository(Actor::class)->findOne($gid);
        $role =  $doctrine->getRepository(ActorRole::class)->findOne($gid,$aref);
        $predicates =  $doctrine->getRepository("App:Predicate")->findChildren($gid,$aref);

        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {
            $actor->setContributor("paul");
            $now = new \DateTime();
            $actor->setUpdateDt($now);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($actor);
            $entityManager->flush();
            $rrole = $request->request->get('_role');
            dump($rrole[$aref]);
            # $role->setText( $rrole[$aref]["'text'"]) ;
            $role->setRole( $rrole[$aref]["'role'"]);
            $role->setName( $rrole[$aref]["'name'"]);
            dump($role);
            $entityManager->persist($role);
            $entityManager->flush();
            $preds =  $rrole[$aref]["'predicates'"];
            $predlist = explode(";", $preds);
            dump($predlist);
            if($predlist)
            {
                foreach( $predlist as $predref => $opred)
                {
                    dump($opred);
                    if(array_key_exists($predref, $predicates))
                    {
                        $npredicate= $predicates[$predref];
                        $npredicate->setActorid($gid);
                        $npredicate->setroleref($aref);
                        $npredicate->setPredicateref($predref);
                        $npredicate->setVerb($opred["'verb'"]);
                        $npredicate->setObject($opred["'object'"]);
                        $entityManager->persist($npredicate);
                        $entityManager->flush();
                    }
                    else
                    {
                        if($opred["'verb'"])
                        {
                            $npredicate= new predicate();
                            $npredicate->setActorid($gid);
                            $npredicate->setroleref($aref);
                            $npredicate->setPredicateref($predref);
                            $npredicate->setVerb($opred["'verb'"]);
                            $npredicate->setObject($opred["'object'"]);
                            $entityManager->persist($npredicate);
                            $entityManager->flush();
                        }
                    }
                }
            }
            return $this->redirect("/actor/editrole/".$gid."/".$aref);
        }
        return $this->render('actor/edit.html.twig', array(

            'actor' => $actor,
            'roles'=>$roles,
            'returnlink' => "/actor/show/".$gid,
        ));

    }


    public function  process_newrelationship(ManagerRegistry $doctrine,$a1ref)
    {

        $actor1 = $doctrine->getRepository(Actor::class)->findOne($a1ref);
        $reln = new Relation();
        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {
            $reln->setActor1ref($a1ref);
            $relation = $request->request->get('_relationship');
            $reln->setRelation($relation);
            $actor2 = $request->request->get('_actor2ref');
            $reln->setActor2ref($actor2);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($reln);
            $entityManager->flush();

            dump($reln);

            return $this->redirect("/actor/editroles/".$a1ref);
        }
        return $this->render('actor/edit.html.twig', array(

            'actor' => $actor,
            'roles'=>$roles,
            'returnlink' => "/actor/show/".$gid,
        ));

    }

    public function  updatelifeevents(ManagerRegistry $doctrine,$aid)
    {

        $actor = $doctrine->getRepository(Actor::class)->findOne($aid);
        $roles =  $doctrine->getRepository(ActorRole::class)->findRoles($aid);
        $entityManager = $doctrine->getManager();
        dump($roles);
        // $lifeevents =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        //   dump( $lifeevents);
        $lifeevents = array();
        foreach($roles as &$role)
        {
            $froles[$role->getRoleref()]=$role;
            $slevents = $this->templates->getLifeEvents($aid,$role->glimpse->getType(),$role->{"role"},$role->glimpse->getDate());
            LifeEvent::mergeLifeevents($lifeevents,$slevents);
        }

        dump($lifeevents);

        foreach($lifeevents as $key=>$lifeevent)
        {
            $em = $doctrine->getManager();
            $em->persist($lifeevent);
            $em->flush();
        }


        return $this->redirect("/actor/editroles/".$aid);


    }





    public function showall(ManagerRegistry $doctrine)
    {

        $pfield =   $this->lib->getCookieFilter();
        $actors = $doctrine->getRepository(Actor::class)->findAll();

        return $this->render(
            'actor/showall.html.twig',
            [
            'actors'=>$actors,
            'filter'=>$pfield,
            'returnlink'=>"returnlink",
            ]
        );
    }


    public function clearfilter()
    {
        //$this->lib->setCookieFilter("fred");
        return $this->filter();

    }





    public function filter(ManagerRegistry $doctrine,)
    {
        $request = $this->requestStack->getCurrentRequest();
        $pfield = $request->query->get('filter');
        //  $pfield = $request->request->get('filter');
        dump($pfield);
        if (is_null($pfield))
        {
            $pfield =   $this->lib->getCookieFilter();
            dump($pfield);
        }
        if (!$pfield)
        {
            # $pfield =   $this->lib->getCookieFilter();
            dump($pfield);
        }
        if (!$pfield)
        {
            $this->lib->setCookieFilter("");
            $actors = $doctrine->getRepository(Actor::class)->findAll();
        }
        else
        {
            $this->lib->setCookieFilter($pfield);
            $filter = "%".$pfield."%";
            $actors = $doctrine->getRepository(Actor::class)->filterf($filter);
        }
        return $this->render(
            'actor/showall.html.twig',
            [
            'actors'=>$actors,
            'filter'=>$pfield,
            'returnlink'=>"returnlink",
            ]
        );
    }



    public function addrole(ManagerRegistry $doctrine,$aid, $rid)
    {

        $arole = new ActorRole();
        $arole->setActorref($aid);
        $arole->setRoleRef($rid);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($arole);
        $entityManager->flush();
        return $this->redirect("/actor/editroles/".$aid);
    }

    public function deleterole(ManagerRegistry $doctrine,$aid, $rid)
    {

        $em = $doctrine->getManager();
        $ar = $doctrine->getRepository(ActorRole::class)->findone($aid, $rid);
        $em->remove($ar);
        $em->flush();
        return $this->redirect("/actor/editroles/".$aid);
    }
}
