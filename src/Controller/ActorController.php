<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\MyLibrary;

use App\Service\Templates;
use Doctrine\Persistence\ManagerRegistry;


use App\Entity\Actor;
use App\Entity\ActorRole;
use App\Entity\Role;
use App\Entity\Glimpse;


use Symfony\Component\Config\FileLocator;

class ActorController extends AbstractController
{

    private $requestStack ;
    private $lib;
     private $templates;

    public function __construct( MyLibrary $lib, Templates $templates,  RequestStack $request_stack)
    {
        $this->requestStack = $request_stack;
        $this->lib = $lib;
        $this->templates = $templates;
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
        $request = $this->requestStack->getCurrentRequest();
       // $gfilter = $this->lib->getCookieFilter();
        $actor = $doctrine->getRepository(Actor::class)->findOne($aid);
            dump($actor);
        $roles =  $doctrine->getRepository(ActorRole::class)->findRoles($aid);
        dump($roles);
        dump($this->templates->getAgelist());
        foreach($roles as $role)
        {
            dump($role);
           $dates = $this->templates->getDates($role->glimpse->getType(),$role->glimpse->getDate(),$role->role->getRole());
        }
       // $filter = "%".$gfilter."%";
      //  $glimpses = $doctrine->getRepository(Glimpse::class)->filterf($filter);
      //  dump($glimpses);
            $glimpses = array();

        return $this->render('actor/editroles.html.twig', array(

            'actor' => $actor,
            'groles'=>$roles,
            'returnlink' => "/actor/show/".$aid,
            'glimpses' => $glimpses,
             'typelist' =>['baptism','marriage', 'burial'],
            ));

    }


     public function  new(ManagerRegistry $doctrine)
    {
        $actor = new Actor();
            $actor->setName("name...");

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


    public function  delete(ManagerRegistry $doctrine,$aid)
    {

        $roles =  $doctrine->getRepository(Actor::class)->delete($aid);
        return $this->redirect("/actor/showall/");

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

            $actor->setName($request->request->get('_name'));
            $actor->setSpecifier($request->request->get('_specifier'));
            $actor->setText($request->request->get('_text'));
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


public function show(ManagerRegistry $doctrine,$aid)
{
    $actor = $doctrine->getRepository(Actor::class)->findOne($aid);
    dump($actor);
    $roles =  $doctrine->getRepository(ActorRole::class)->findroles($aid);
    dump($roles);
    return $this->render(
        'actor/show.html.twig',
        [
        'actor'=>$actor,
        'roles'=>$roles,
        'returnlink'=>"returnlink",
        ]
        );
}



public function showall(ManagerRegistry $doctrine)
    {

        $pfield =   $this->lib->getCookieFilter();
        //dump($pfield);
       // if (!$pfield)
        {
              $actors = $doctrine->getRepository(Actor::class)->findAll();
        }
       // else
       // {

       //  $filter = "%".$pfield."%";
       //   $actors = $doctrine->getRepository(Actor::class)->filterf($filter);

       // }
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
