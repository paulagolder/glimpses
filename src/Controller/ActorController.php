<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
use Doctrine\DBAL\DBALException;
use Symfony\Component\Config\FileLocator;

class ActorController extends AbstractController
{

    private $requestStack;
    private $lib;
    private $templates;

    public function __construct(MyLibrary $lib, Templates $templates, RequestStack $request_stack, string $templatedir)
    {
        $this->requestStack = $request_stack;
        $this->lib = $lib;
        $this->templates = $templates;
    }

    public function showall(ManagerRegistry $doctrine)
    {
        $filter = $this->lib->getCookieFilter('actor');
        if (is_null($filter) or $filter == "")
        {
            $actors = $doctrine->getRepository(Actor::class)->findAll();
        } else
        {
            $actors = $doctrine->getRepository(Actor::class)->filterf($filter);
        }
        return $this->render(
                        'actor/showall.html.twig',
                        [
                            'actors' => $actors,
                            'filter' => $filter,
                            'returnlink' => "returnlink",
                        ]
                );
    }

    public function showone(ManagerRegistry $doctrine, $aid)
    {
        $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
        $allactors = $doctrine->getRepository(Actor::class)->findAllIndexed();
        $actorroles = $doctrine->getRepository(ActorRole::class)->getActorRoles($aid);
        $rolelist = array();
        foreach ($actorroles as $actorrole)
        {
            $roleid = $actorrole->getRoleref();
            $role = $doctrine->getRepository(Role::class)->getOne($roleid);
            $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($role->getGlimpseref());
            if($glimpse != null)
            {
            $roles = $doctrine->getRepository(Role::class)->findChildren($role->getGlimpseRef());
            $glimpse->{"roles"} = $roles;
            $rolelist[$roleid] = $glimpse;
            }
        }
        $lifeevents = $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        $relations = $doctrine->getRepository(Relation::class)->findByActor($aid);
        $nrole = new ActorRole();
        $nrole->setActorref($aid);
        $roles[] = $nrole;
        return $this->render('actor/show.html.twig', array(
                    'actor' => $actor,
                    'roles' => $rolelist,
                    'lifeevents' => $lifeevents,
                    'relations' => $relations,
                    'actors' => $allactors,
                    'returnlink' => "/actor/showall/" . $aid,
                    'typelist' => ['baptism', 'marriage', 'burial'],
        ));
    }

    public function setfilter(ManagerRegistry $doctrine)
    {
        $request = $this->requestStack->getCurrentRequest();
        $pfield = $request->query->get('filter');
        if (is_null($pfield))
        {
            $this->lib->clearCookieFilter("actor");
        } else
        {
           // $this->lib->setCookieFilter('actor', $pfield);
        }
        return $this->redirect("/actor/showall/");
    }

    public function clearfilter(ManagerRegistry $doctrine)
    {
        $pfield = "";
        $this->lib->clearCookieFilter("actor");
        return $this->redirect("/actor/showall/");
    }

    public function edit(ManagerRegistry $doctrine, $aid)
    {
        $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
        $roles = $doctrine->getRepository(ActorRole::class)->getRoles($aid);
        $nrole = new ActorRole();
        $nrole->setActorref($aid);
        $roles[] = $nrole;
        return $this->render('actor/edit.html.twig', array(
                    'actor' => $actor,
                    'roles' => $roles,
                    'returnlink' => "/actor/show/" . $aid,
                    'typelist' => ['baptism', 'marriage', 'burial'],
        ));
    }

    public function editroles(ManagerRegistry $doctrine, $aid)
    {
        $em = $doctrine->getManager();
        $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
        $actors = $doctrine->getRepository(Actor::class)->findAllIndexed();

        $gfilter = $actor->getForename() . "+" . $actor->getSurname();
        if ($actor->getKeywords())
        {
            $gfilter .= ",".$actor->getKeywords();
        }
        $lifeevents = $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        $relations = $doctrine->getRepository(Relation::class)->findByActor($aid);
        foreach ($relations as &$relation)
        {
            $relation->{"actor1"} = $em->getRepository(Actor::class)->getOne($relation->getActor1ref());
            $relation->{"actor2"} = $em->getRepository(Actor::class)->getOne($relation->getActor2ref());
        }
        $aroles = $doctrine->getRepository(ActorRole::class)->getActorRoles($aid);
        dump($aroles);
        $froles = array();
        foreach ($aroles as &$arole)
        {
            $role= $doctrine->getRepository(Role::class)->getOne($arole->getRoleRef());
            $glimpse = $doctrine->getRepository(Glimpse::class)->getOne( $role->getGlimpseRef());
            $broles = $doctrine->getRepository(Role::class)->findChildren($role->getGlimpseRef());
            $glimpse->{"roles"} = $broles;
            $froles[$arole->getRoleRef()] = $glimpse;
        }
        dump($froles);
        $sroles = $doctrine->getRepository(Role::class)->filterf($gfilter);
        $cglimpses = array();
        foreach ($sroles as $key=> $srole)
        {
           // $key = $srole->getRoleId();
            if (!array_key_exists($key, $cglimpses) && !array_key_exists($key, $froles))
            {
                $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($srole->getGlimpseId());
                if (!is_null($glimpse))
                {
                    $roles = $doctrine->getRepository(Role::class)->findChildren($srole->getGlimpseId());
                    $glimpse->{"roles"} = $roles;
                    $cglimpses[$key] = $glimpse;
                }
            }
        }
        dump($cglimpses);
        $duplicates = $doctrine->getRepository(Actor::class)->findAllMatching($actor);
        return $this->render('actor/editroles.html.twig', array(
                    'actor' => $actor,
                    'actors' => $actors,
                    'lifeevents' => $lifeevents,
                    'relations' => $relations,
                    'roles' => $froles,
                    'returnlink' => "/actor/show/" . $aid,
                    'cglimpses' => $cglimpses,
                    'duplicates' => $duplicates,
                    'typelist' => ['baptism', 'marriage', 'burial', 'inventory', 'will'],
        ));
    }

    public function new(ManagerRegistry $doctrine)
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
                    'returnlink' => "/actor/show/" . $aid,
        ));
    }

    public function newactor(ManagerRegistry $doctrine, $gid, $rref)
    {
        $role = $doctrine->getRepository(Role::class)->find($rref);
        $glimpse = $doctrine->getRepository(Glimpse::class)->find($gid);
        $actor = new Actor();
        $names = explode(" ", $role->getName() . "  ?");
        $actor->setForename($names[0]);
        $actor->setSurname($names[1]);
        $specifier = $role->getRole() . " " . $glimpse->getDate() . " " . $role->getPredicatestr();
        $actor->setContributor("paul");
        $now = new \DateTime();
        $actor->setUpdateDt($now);
        $actor->setSpecifier($specifier);
        if($glimpse->getType() == "baptism" or $glimpse->getType() == "birth" ) $actor->setBirthdate($glimpse->getDate());
        if($glimpse->getType() == "burial" or $glimpse->getType() == "death" ) $actor->setDeathdate($glimpse->getDate());
        $oaid =  $doctrine->getRepository(Actor::class)->exists($actor);
         $entityManager = $doctrine->getManager();
        if($oaid)
        {
          dump("++duplicate++".$oaid);
          $aid =$oaid;
        }else
        {
          $entityManager->persist($actor);
          $entityManager->flush();
          $aid = $actor->getActorid();
        }
    dump($aid);
    dump($rref);
        $actorrole =  $doctrine->getRepository(ActorRole::class)->findOne($aid,$rref);
        if($actorrole)
        {

        }
        else
        {
           $actorrole = new ActorRole();
           $actorrole->setActorref($aid);
           $actorrole->setRoleRef($rref);
           $entityManager->persist($actorrole);
           $entityManager->flush();
        }
        return $this->redirect("/glimpse/edit/".$gid);
    }

    public function newrelationship(ManagerRegistry $doctrine, $aid)
    {
        $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
        $actors = $doctrine->getRepository(Actor::class)->findAll();
        $relationships = ["father", "mother", "son", "daughter", "wife", "husband", "sister", "brother"];
        $relations = $doctrine->getRepository(Relation::class)->findByActor($aid);
        return $this->render('actor/newrelationship.html.twig', array(
                    'actor' => $actor,
                    'actors' => $actors,
                    'relations' => $relations,
                    'relationships' => $relationships,
                    'returnlink' => "/actor/show/" . $aid,
        ));
    }

    public function delete(ManagerRegistry $doctrine, $aid)
    {
        $roles = $doctrine->getRepository(Actor::class)->delete($aid);
        return $this->redirect("/actor/showall/");
    }

    public function merge(ManagerRegistry $doctrine, $aid, $daid)
    {
        $actor1 = $doctrine->getRepository(Actor::class)->getOne($aid);
        $actor2 = $doctrine->getRepository(Actor::class)->getOne($daid);
        $actor1->merge($actor2);
        $lifeevents1 = $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        $lifeevents2 = $doctrine->getRepository(LifeEvent::class)->findAllEvents($daid);

        LifeEvent::merge($lifeevents1, $lifeevents2);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($actor1);
        $entityManager->flush();
        $roles = $doctrine->getRepository(ActorRole::class)->getRolesIndexed($aid);
        $roles2 = $doctrine->getRepository(ActorRole::class)->getRolesIndexed($daid);
        dump($roles2);
        foreach ($roles2 as $rid => $role)
        {
            if (!array_key_exists($rid, $roles))
            {
                $role->setActorRef($aid);
                $entityManager->persist($role);
                $entityManager->flush();
            }
        }
        $doctrine->getRepository(ActorRole::class)->deleteByActor($daid);
        $doctrine->getRepository(LifeEvent::class)->deleteAll($daid);
        $doctrine->getRepository(Actor::class)->delete($daid);
        return $this->redirect("/actor/editroles/" . $aid);
    }

    public function compare(ManagerRegistry $doctrine, $aid, $daid)
    {
        $actor1 = $doctrine->getRepository(Actor::class)->getOne($aid);
        $actor2 = $doctrine->getRepository(Actor::class)->getOne($daid);
        $lifeevents1 = $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        $lifeevents2 = $doctrine->getRepository(LifeEvent::class)->findAllEvents($daid);
        $roles1 = $doctrine->getRepository(ActorRole::class)->getRoles($aid);
        $roles2 = $doctrine->getRepository(ActorRole::class)->getRoles($daid);
        foreach($roles1 as &$role)
        {
           $aglimpse = $doctrine->getRepository(Glimpse::class)->getOne($role->getGlimpseRef());
           $aglimpse->{"roles"} = $doctrine->getRepository(Role::class)->findChildren($aglimpse->getGlimpseId());
           $role->{"glimpse"} = $aglimpse;
        }
        //   LifeEvent::merge($lifeevents1,$lifeevents2 );
        //    return $this->redirect("/actor/editroles/".$aid);
        // $lifeevents1 =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        //   $relations1 = $doctrine->getRepository(Relation::class)->findByActor($aid);
        //   $lifeevents2 =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($daid);
        //   $relations2 = $doctrine->getRepository(Relation::class)->findByActor($daid);

        return $this->render(
                        'actor/match.html.twig',
                        [
                            'actor1' => $actor1,
                            'actor2' => $actor2,
                            'lifeevents1' => $lifeevents1,
                            'lifeevents2' => $lifeevents2,
                            'roles1' => $roles1,
                            'roles2' => $roles2,
                            'returnlink' => "/actor/show/$aid",
                        ]
                );
    }

    public function delete_role(ManagerRegistry $doctrine, $aid, $rid)
    {
        $doctrine->getRepository(ActorRole::class)->delete($aid, $rid);
        return $this->redirect("/actor/edit/" . $aid);
    }

    public function process_edit(ManagerRegistry $doctrine, $aid)
    {
        if ($aid < 1)
        {
            $actor = new Actor();
            $actorroles = array();
        } else
        {
            $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
            $actorroles = $doctrine->getRepository(ActorRole::class)->getRoles($aid);
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
             $actor->setGender($request->request->get('_gender'));
            $actor->setContributor("paul");
            $now = new \DateTime();
            $actor->setUpdateDt($now);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($actor);
            $entityManager->flush();
            $aid = $actor->getActorid();
            $par = $request->request->get('_role');

            dump($par);
            if (!is_null($par))
            {
                foreach ($par as $key => $arole)
                {
                    dump($key);
                    dump($arole);
                    $ref = $arole["'roleref'"];
                    if ($ref == null || $ref == 0)
                    {
                        $nrole = new role();
                        $nrole->setActorRef($aid);
                        $nrole->setRole($arole["'role'"]);
                        $nrole->setName($arole["'name'"]);
                        if ($nrole->getName())
                        {
                            $entityManager->persist($nrole);
                            $entityManager->flush();
                        }
                    } else
                    {
                        $nrole = $roles[$key];
                        #$nrole->setText($arole["'text'"]);
                        $nrole->setRole($arole["'role'"]);
                        ;
                        $entityManager->persist($nrole);
                        $entityManager->flush();
                    }
                }
            }
            return $this->redirect("/actor/showone/" . $aid);
        }

        return $this->render('actor/edit.html.twig', array(
                    'actor' => $actor,
                    'roles' => $roles,
                    'returnlink' => "/actor/show/" . $gid,
                    'typelist' => ['baptism', 'marriage', 'burial'],
        ));
    }

    public function process_editrole(ManagerRegistry $doctrine, $gid, $aref)
    {
        $actor = $doctrine->getRepository(Actor::class)->getOne($gid);
        $role = $doctrine->getRepository(ActorRole::class)->getOne($gid, $aref);
        $predicates = $doctrine->getRepository("App:Predicate")->findChildren($gid, $aref);

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
            $role->setRole($rrole[$aref]["'role'"]);
            $role->setName($rrole[$aref]["'name'"]);
            dump($role);
            $entityManager->persist($role);
            $entityManager->flush();
            $preds = $rrole[$aref]["'predicates'"];
            $predlist = explode(";", $preds);
            dump($predlist);
            if ($predlist)
            {
                foreach ($predlist as $predref => $opred)
                {
                    dump($opred);
                    if (array_key_exists($predref, $predicates))
                    {
                        $npredicate = $predicates[$predref];
                        $npredicate->setActorid($gid);
                        $npredicate->setroleref($aref);
                        $npredicate->setPredicateref($predref);
                        $npredicate->setVerb($opred["'verb'"]);
                        $npredicate->setObject($opred["'object'"]);
                        $entityManager->persist($npredicate);
                        $entityManager->flush();
                    } else
                    {
                        if ($opred["'verb'"])
                        {
                            $npredicate = new predicate();
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
            return $this->redirect("/actor/editrole/" . $gid . "/" . $aref);
        }
        return $this->render('actor/edit.html.twig', array(
                    'actor' => $actor,
                    'roles' => $roles,
                    'returnlink' => "/actor/show/" . $gid,
        ));
    }

    public function process_newrelationship(ManagerRegistry $doctrine, $a1ref)
    {
        $actor1 = $doctrine->getRepository(Actor::class)->getOne($a1ref);
        $reln = new Relation();
        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {
            $reln->setActor1ref($a1ref);
            $relation = $request->request->get('_relationship');
            $actor2 = $request->request->get('_actor2ref');
            $clues = $request->request->get('_relationclue');
              $date = $request->request->get('_date');
            if(($actor2 =="Choose Actor") or ($relation == "Choose One") )
               return $this->redirect("/actor/editroles/" . $a1ref);
         //   actor/newrelationshipwithglimpse/142/64
            $reln->setRelation($relation);
            $reln->setActor2ref($actor2);
            $reln->setClues($clues);
            $reln->setDate($date);
            dump($reln);
            $getrelation =  $doctrine->getRepository(Relation::class)->findbyKey($a1ref,$relation,$actor2);
             if($getrelation == null)
             {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($reln);
            $entityManager->flush();

            }
            return $this->redirect("/actor/editroles/" . $a1ref);
        }
        return $this->render('actor/edit.html.twig', array(
                    'actor' => $actor,
                    'roles' => $roles,
                    'returnlink' => "/actor/show/" . $gid,
        ));
    }

    public function newrelationshipwithglimpse(ManagerRegistry $doctrine, $aid, $gid)
    {
        $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
        $actors = $doctrine->getRepository(Actor::class)->findAll();
        $actorsindexed = $doctrine->getRepository(Actor::class)->findAllIndexed();
        $relationships = ["father", "mother", "son", "daughter", "wife", "husband", "sister", "brother", "resident"];
        $eventtypes =["birth","marriage","resident","death","burial","grant_of_probate"];
        $lifeevents = $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        dump($lifeevents);
        $relations = $doctrine->getRepository(Relation::class)->findByActor($aid);
        $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($gid);
        $glimpse->{"roles"} = $doctrine->getRepository(Role::class)->findChildren($gid);
        $roles = $glimpse->{"roles"};

        foreach($roles as &$arole)
        {
          $roleactors = $doctrine->getRepository(ActorRole::class)->getActorIds($arole->getRoleid());

          $actorlist = array();
          foreach($roleactors as $anactor)
          {
          $anactorid = $anactor["actorref"];
            $anactor = $doctrine->getRepository(Actor::class)->getOne($anactorid);
           $actorlist[$anactorid] = $anactor->getLabel();
          }
           $arole->{"actors"} = $actorlist;
        }
        dump($glimpse);
        return $this->render('actor/newrelationship.html.twig', array(
                    'actor' => $actor,
                    'actorsindexed' => $actorsindexed,
                    'actors' => $actors,
                    'glimpse' => $glimpse,
                    'relations' => $relations,
                    'lifeevents' => $lifeevents,
                    'returnlink' => "/actor/show/" . $aid,
        ));
    }

    public function updatelifeevents(ManagerRegistry $doctrine, $aid)
    {
        $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
        $roles = $doctrine->getRepository(ActorRole::class)->findRoles($aid);
        foreach($roles as &$role)
        {
           $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($role->getGlimpseRef());
           $glimpse->{"roles"} = $doctrine->getRepository(Role::class)->findChildren($glimpse->getGlimpseId());
           $role->{"glimpse"} = $glimpse;
        }
        $entityManager = $doctrine->getManager();
        dump($roles);
        // $lifeevents =  $doctrine->getRepository(LifeEvent::class)->findAllEvents($aid);
        //   dump( $lifeevents);
        $lifeevents = array();
        foreach ($roles as &$role)
        {
            $froles[$role->getRoleId()] = $role;
            $slevents = $this->templates->getLifeEvents($aid, $role->glimpse->getType(), $role->getRole(), $role->glimpse->getDate());
            dump($slevents);
            $lifeevents[]=reset($slevents);
        }
        dump($lifeevents);
        foreach ($lifeevents as $key => $lifeevent)
        {
            $em = $doctrine->getManager();
            $em->persist($lifeevent);
            $em->flush();
        }
        return $this->redirect("/actor/editroles/" . $aid);
    }

     public function process_newevent(ManagerRegistry $doctrine, $a1ref)
    {
        $actor1 = $doctrine->getRepository(Actor::class)->getOne($a1ref);
        $reln = new LifeEvent($a1ref,"");
        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {
            $reln->setActorRef($a1ref);
            $glimpseid = $request->request->get('_eventclue');
            $roleid = $request->request->get('_selrole');
            $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($glimpseid);
            $glimpse->{"roles"} = $doctrine->getRepository(Role::class)->findChildren($glimpseid);
            $reln->setEventtype($glimpse->getType());
            $reln->setDate($glimpse->getDate());
            $reln->setLocation($glimpse->getLocation());
            $text =$this->lib->FormatEvent($glimpse);
            $reln->setSubject($text);
            $role =  $doctrine->getRepository(Role::class)->getOne($roleid);
            $reln->setRole($role->getRole().":".$role->getName());
            $reln->setclues($glimpseid);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($reln);
            $entityManager->flush();
            return $this->redirect("/actor/editroles/" . $a1ref);
        }
        return $this->render('actor/edit.html.twig', array(
                    'actor' => $actor,
                    'roles' => $roles,
                    'returnlink' => "/actor/show/" . $gid,
        ));
    }

    public function process_event(ManagerRegistry $doctrine, $a1ref)
    {

              $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
              $actors = $doctrine->getRepository(Actor::class)->findAll();

              return $this->render('actor/newrelationship.html.twig', array(
                          'actor' => $actor,
                          'actors' => $actors,
                          'relations' => $relations,
                          'relationships' => $relationships,
                          'returnlink' => "/actor/show/" . $aid,
              ));

    }

    public function addrole(ManagerRegistry $doctrine, $aid, $rid)
    {
        $arole = new ActorRole();
        $arole->setActorref($aid);
        $arole->setRoleRef($rid);
        dump($arole);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($arole);
        try
        {
            $entityManager->flush();
        } catch (DBALException $e)
        {
            // ....
        }
        return $this->redirect("/actor/editroles/" . $aid);
    }

    public function deleterole(ManagerRegistry $doctrine, $aid, $rid)
    {
        $em = $doctrine->getManager();
        $ar = $doctrine->getRepository(ActorRole::class)->findOne($aid, $rid);
        $em->remove($ar[0]);
        $em->flush();
        return $this->redirect("/actor/editroles/" . $aid);
    }

    public function deleteevent(ManagerRegistry $doctrine, $leid)
    {
        $le = $doctrine->getRepository(LifeEvent::class)->getone($leid);
        $ar = $doctrine->getRepository(LifeEvent::class)->delete($leid);
        return $this->redirect("/actor/editroles/" . $le->getActorref());
    }

    public function deleterelation(ManagerRegistry $doctrine,$aid, $rid)
    {
        $reln = $doctrine->getRepository(Relation::class)->getone($rid);
        $ar = $doctrine->getRepository(Relation::class)->delete($rid);
        return $this->redirect("/actor/editroles/" . $aid);
    }

     public function maketree(ManagerRegistry $doctrine,$aid)
    {
        $actor = $doctrine->getRepository(Actor::class)->getOne($aid);
        $relnlist = $doctrine->getRepository(Relation::class)->findbyActor($aid);
        $wives=array();
        $husbands=array();
        $children=array();
        $parents=array();
        dump($relnlist);
        foreach($relnlist as $areln)
        {
        $relation = $areln->getRelation();
        if($areln->getActor1ref() == $aid)
        {
          $a2id = $areln->getActor2ref();
          $actor2 = $doctrine->getRepository(Actor::class)->getOne($a2id);
          if ($relation== "groom") $wives[ $a2id ] = $actor2;
          if ($relation== "husband") $wives[ $a2id ] = $actor2;
          if ($relation== "father") $children[ $a2id ] = $actor2;
          if ($relation== "mother") $children[ $a2id ] = $actor2;
          if ($relation== "bride") $husbands[ $a2id ] = $actor2;
          if ($relation== "wife") $husbands[ $a2id ] = $actor2;
          if ($relation== "son") $parents[ $a2id ] = $actor2;
           if ($relation== "child") $parents[ $a2id ] = $actor2;
        }
        else
        {
          $a1id = $areln->getActor2ref();
          $actor1 = $doctrine->getRepository(Actor::class)->getOne($a1id);
           if ($relation== "groom") $wives[  $a1id ] =  $actor1;
           if ($relation== "husband") $wives[  $a1id ] =  $actor1;
           if ($relation== "child") $children[  $a1id ] =  $actor1;
           if ($relation== "mother") $children[ $a1id ] = $actor1;
           if ($relation== "father") $children[ $a1id ] = $actor1;
           if ($relation== "bride") $husbands[  $a1id ] =  $actor1;
           if ($relation== "wife") $husbands[  $a1id ] =  $actor1;
           if ($relation== "son") $parents[ $a1id ] = $actor1;
            if ($relation== "child") $parents[ $a1id ] = $actor1;
        }
    }
      dump($wives);
      dump($husbands);
      dump($children);
       dump($parents);
    return $this->render('actor/maketree.html.twig', array(
                                  'actor' => $actor,
                                  'wives' => $wives,
                                  'husbands' => $husbands,
                                  'children' => $children,
                                  'parents' => $parents,
                                  'returnlink' => "/actor/show/" . $aid,
                      ));
    }
}

