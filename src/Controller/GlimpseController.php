<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\MyLibrary;
use Doctrine\Persistence\ManagerRegistry;

use App\Service\Templates;

use App\Entity\Source;
use App\Entity\Glimpse;
use App\Entity\Role;
use App\Entity\Predicate;
use App\Form\glimpseForm;
use Symfony\Component\Config\FileLocator;

class GlimpseController extends AbstractController
{

    private $requestStack ;
    private $templatesrc;
    private $lib;

 //   private  $templatelist=array();
 //   private  $agelist=array();






    public function __construct( Templates $templates ,MyLibrary $lib, RequestStack $request_stack ,string $templatedir)
    {
        $this->templatesrc = $templates;
        $this->requestStack = $request_stack;
        $this->lib = $lib;
    //    $ageyml = $fileLocator->locate('agelist3.yml', null, false);
    //    $this->agelist =   Yaml::parseFile($ageyml[0]);
      //  $this->requestStack = $request_stack;
    }


    public function  dataentry(ManagerRegistry $doctrine,)
    {
        $glimpse = new Glimpse();
        $glimpse->setType($type);
        $glimpse->setContributor("paul");
       // $now = new \DateTime();
      //  $glimpse->setUpdateDt($now);
        $glimpse->setGlimpseId(0);
        $roles = array();
        $roletemplate =  $this->templatesrc->getTemplates($type);
        foreach($roletemplate as $key => $card)
        {
            dump($card);
            $role = new role();
            $role->setRole($key);
            $roles[]=$role;
        }

        return $this->render('glimpse/edit.html.twig', array(

            'glimpse' => $glimpse,
            'roles'=>$roles,
            'returnlink' => "/glimpse/new",
            'typelist' =>['baptism','marriage', 'burial'],
        ));

    }


    public function  new(ManagerRegistry $doctrine,$type)
    {
        $sourceid = $this->lib->getCookieSource();
        $source =   $doctrine->getRepository(Source::class)->findOne($sourceid);
        $glimpse = new Glimpse();
        $glimpse->{"source"}=$source->getTitle();
        $glimpse->setLocation($source->getRegion());
        $glimpse->setSourceId($sourceid);
        $glimpse->setLanguage($source->getlanguage());
        $glimpse->setType($type);
        $glimpse->setContributor("paul");
        $now = new \DateTime();
        $glimpse->setUpdateDt($now);
        $glimpse->setGlimpseId(0);
        $roles = array();
        $typelist = $this->templatesrc->getTypes();
        dump($typelist);
        if($type!="X")
        {
            $roletemplate =  $this->templatesrc->getTemplates($type);
            dump( $roletemplate );
            $ir=0;
            foreach($roletemplate as $key => $card)
            {
                   dump($key);
                dump($card);
                $nr=1;
                if(is_array($card))
                {
                    if(array_key_exists("cardinality",$card ))
                        $nr = $card["cardinality"];
                    else
                        $nr=1;
                }
                else
                {
                    $nr=$card;
                }
                if($nr<1) $nr=1;
                dump($nr);
                for ($r = 1; $r <= $nr; $r++)
                {
                    dump($r);
                    $role = new role();
                    $role->setRole($key);
                    $roles[$ir]=$role;
                    $ir++;
                    if($ir>10) break;
                }
            }
            dump($roles);
            return $this->render('glimpse/edit.html.twig', array(

                'glimpse' => $glimpse,
                'source'=>$source,
                'roles'=>$roles,
                'returnlink' => "/glimpses",
                'typelist' =>$typelist,
            ));
        }
        else
        {
            return $this->render('glimpse/selecttypes.html.twig', array(
                'returnlink' => "/glimpse/new",
                'typelist' =>$typelist,
            ));

        }
    }

    public function  startinput(ManagerRegistry $doctrine,$sourceid)
    {

        $typelist = $this->templatesrc->getTypes();
        dump($typelist);
        $source =  $doctrine->getRepository(Source::class)->findOne($sourceid);
        dump($source);
        $this->lib->setCookieSource($sourceid);
        {
            return $this->render('source/startinput.html.twig', array(
                'returnlink' => "/glimpse/new",
                'source'=>$source,
                'typelist' =>$typelist,
            ));

        }
    }

    public function  edit(ManagerRegistry $doctrine,$gid)
    {
        $glimpse = $doctrine->getRepository(Glimpse::class)->findOne($gid);
        $source =   $doctrine->getRepository(Source::class)->findOne($glimpse->getSourceid());
        $roles =  $doctrine->getRepository(Role::class)->findChildren($gid);
        $nrole = new role();
        $nrole->setGlimpseref($gid);
        $roles[]=$nrole;
        $glimpse->{"source"}= $source->getTitle();
        return $this->render('glimpse/edit.html.twig', array(

            'glimpse' => $glimpse,
            'source'=>$source,
            'roles'=>$roles,
            'returnlink' => "/glimpse/show/".$gid,
            'typelist' =>['baptism','marriage', 'burial'],
        ));

    }

    public function  edit_role(ManagerRegistry $doctrine,$gid,$pref)
    {
        $glimpse = $doctrine->getRepository(Glimpse::class)->findOne($gid);
        $roles =  $doctrine->getRepository(Role::class)->findChildren($gid);
        dump($roles);
        foreach ($roles as $key=> $role)
        {
            $aref = $role->getroleid();
            /*        $predicates =  $doctrine->getRepository("App:Predicate")->findChildren($gid,$aref);
             d ump($predicate*s);
             $npredicate = new predicate();
             $npredicate->setGlimpseid($gid);
             $npredicate->setroleref($aref);
             $npredicate->setVerb("");
             $npredicate->setObject("");
             #$predicates[]=$npredicate;
             $predicates="";
             $role->setPredicates($predicates); */
            dump($role );
        }


        dump($roles);
        return $this->render('glimpse/editrole.html.twig', array(

            'glimpse' => $glimpse,
            'roles'=>$roles,
            'activerole'=>$pref,
            'returnlink' => "/glimpse/edit/".$gid,
        ));

    }

    public function  delete(ManagerRegistry $doctrine,$gid)
    {
        $roles =  $doctrine->getRepository(Glimpse::class)->delete($gid);
        return $this->redirect("/glimpse/showall/");
    }

    public function  delete_role($gid,$pref)
    {
        $roles =  $doctrine->getRepository(Role::class)->delete($gid,$pref);
        return $this->redirect("/glimpse/edit/".$gid);
    }


    public function  delete_predicate($gid,$aref,$pref)
    {
        $roles =  $doctrine->getRepository("App:Predicate")->delete($gid,$aref,$pref);
        return $this->redirect("/glimpse/editrole/".$gid."/".$aref);
    }


    public function  process_edit(ManagerRegistry $doctrine,$gid)
    {
        if($gid <1)
        {
            $glimpse = new Glimpse();
            $sourceid = $this->lib->getCookieSource();
            dump($sourceid);
            $source =   $doctrine->getRepository(Source::class)->findOne($sourceid);
          //  $glimpse->setSource($source->getTitle());
            $glimpse->setLocation($source->getRegion());
            $glimpse->setSourceId($sourceid);
            $glimpse->setLanguage($source->getlanguage());
            $glimpse->{"source"} = $source->getTitle();
            $roles = array();
        }
        else
        {
            $glimpse = $doctrine->getRepository(Glimpse::class)->findOne($gid);

              $source =   $doctrine->getRepository(Source::class)->findOne($glimpse->getSourceid());
            $roles =  $doctrine->getRepository(Role::class)->findChildren($gid);
              $glimpse->{"source"} = $source->getTitle();
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {

            $glimpse->setText($request->request->get('_text'));
            # $glimpse->setLanguage($request->request->get('_language'));
            $glimpse->setLocation($request->request->get('_location'));
            $glimpse->setType($request->request->get('_type'));
            $glimpse->setDate($request->request->get('_gdate'));
            $glimpse->setRef($request->request->get('_ref'));
            $glimpse->setText($request->request->get('_text'));
            $glimpse->setContributor("paul");
            $now = new \DateTime();
            $glimpse->setUpdateDt($now);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($glimpse);
            $entityManager->flush();
            $gid = $glimpse->getGlimpseid();
            dump($request->request->all()["_role"]);
            $rqroles=$request->request->all()["_role"];
           // $roles = $rqroles[array_key_first($rqroles)];
            dump($rqroles);
         //   $par = $request->getParameter('_role');
            foreach($rqroles as $key=>$arole)
            {
                $ref = $arole["'ref'"];
                if($ref==null || $ref==0)
                {
                    $nrole = new role();
                    $nrole->setGlimpseref($gid);
                    #$nrole->setText($arole["'text'"]);
                    $nrole->setRole(trim($arole["'role'"]));
                    $nrole->setName($arole["'name'"]);
                    $nrole->setPredicates($arole["'predicates'"]);
                    if($nrole->getName())
                    {
                        $entityManager->persist($nrole);
                        $entityManager->flush();
                    }
                      dump($nrole);
                }else
                {
                    $nrole = $roles[$key];
                    #$nrole->setText($arole["'text'"]);
                    $nrole->setRole(trim($arole["'role'"]));
                    $nrole->setName($arole["'name'"]);
                    $nrole->setPredicates($arole["'predicates'"]);
                    $entityManager->persist($nrole);
                    $entityManager->flush();
                      dump($nrole);
                }
            }
            return $this->redirect("/glimpse/edit/".$gid);

        }
dump($glimpse);
        return $this->render('glimpse/edit.html.twig', array(

            'glimpse' => $glimpse,
            'roles'=>$roles,
            'returnlink' => "/glimpse/show/".$gid,
            'typelist' =>['baptism','marriage', 'burial', 'will', 'note'],
        ));

    }

    public function  process_editrole(ManagerRegistry $doctrine,$gid,$aref)
    {

        $glimpse = $doctrine->getRepository(Glimpse::class)->findOne($gid);
        $role =  $doctrine->getRepository(Role::class)->findOne($gid,$aref);
        //  $predicates =  $doctrine->getRepository("App:Predicate")->findChildren($gid,$aref);
           $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {

        dump($request);
        dump($request->request->all()["_role"]);
        $rqroles=$request->request->all()["_role"];
        $rrole = $rqroles[$aref];
        dump($rrole);
       // $par = $request->getParameter('_role');


            $glimpse->setContributor("paul");
            $now = new \DateTime();
            $glimpse->setUpdateDt($now);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($glimpse);
            $entityManager->flush();

          //  $rrole = $request->$request->get('_role');
            dump($rrole);
            # $role->setText( $rrole[$aref]["'text'"]) ;
            $role->setRole($rrole["'role'"]);
            $role->setName($rrole["'name'"]);
            dump($role);
            $entityManager->persist($role);
            $entityManager->flush();
            $preds =  $rrole["'predicates'"];
            $role->setPredicates($preds);
            $entityManager->persist($role);
            $entityManager->flush();
            //   $predlist = explode(";", $preds);
            /*   dump($predlist);
             i f($predlist)  *
             {
             foreach( $predlist as $predref => $opred)
             {
             dump($opred);
             if(array_key_exists($predref, $predicates))
             {

             $npredicate= $predicates[$predref];
             $npredicate->setGlimpseid($gid);
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
        $npredicate->setGlimpseid($gid);
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
        */

            return $this->redirect("/glimpse/edit/".$gid);


        }

        return $this->render('glimpse/edit.html.twig', array(

            'glimpse' => $glimpse,
            'roles'=>$roles,
            'returnlink' => "/glimpse/show/".$gid,
        ));

    }


    public function show(ManagerRegistry $doctrine,$gid)
    {

        $glimpse = $doctrine->getRepository(Glimpse::class)->findOne($gid);
        $roles =  $doctrine->getRepository(Role::class)->findChildren($gid);
        $source =   $doctrine->getRepository(Source::class)->findOne($glimpse->getSourceid());
        return $this->render(
            'glimpse/show.html.twig',
            [
            'glimpse'=>$glimpse,
            'roles'=>$roles,
            'source'=>$source,
            'returnlink'=>"returnlink",
            ]
        );
    }

    public function xshowAll()
    {

        $glimpses = $doctrine->getRepository(Glimpse::class)->findAll();
        $filter =    $this->lib->getCookieFilter();
        return $this->render(
            'glimpse/showall.html.twig',
            [
            'filter'=>$filter,
            'glimpses'=>$glimpses,
            'returnlink'=>"returnlink",
            ]
        );
    }

    public function showall(ManagerRegistry $doctrine)
    {

        $pfield =   $this->lib->getCookieFilter();
        if (!$pfield)
        {
            $glimpses = $doctrine->getRepository(Glimpse::class)->findAll();
        }
        else
        {
            $filter = $pfield;
            $glimpses = $doctrine->getRepository(Glimpse::class)->filterf($filter);
        }
        foreach( $glimpses as &$glimpse)
        {
            $glimpse->{"roles"} =    $doctrine->getRepository(Role::class)->findChildren($glimpse->getGlimpseId());

        }
        return $this->render(
            'glimpse/showall.html.twig',
            [
            'glimpses'=>$glimpses,
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





    public function filter(ManagerRegistry $doctrine)
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
            $glimpses = $doctrine->getRepository(Glimpse::class)->findAll();
        }
        else
        {
            $this->lib->setCookieFilter($pfield);
            $filter = "%".$pfield."%";
            $glimpses = $doctrine->getRepository(Glimpse::class)->filterf($filter);
            dump($glimpses);
        }


        foreach($glimpses as &$glimpse)
        {
              $glimpse->{"roles"} =  $doctrine->getRepository(Role::class)->findChildren($glimpse->getGlimpseId());

        }
        return $this->render(
            'glimpse/showall.html.twig',
            [
            'glimpses'=>$glimpses,
            'filter'=>$pfield,
            'returnlink'=>"returnlink",
            ]
        );
    }

    public function showregion(ManagerRegistry $doctrine,$region)
    {

        $glimpses = $doctrine->getRepository(Glimpse::class)->viewregion($region);

        return $this->render(
            'glimpse/showregion.html.twig',
            [
            'region'=>$region,
            'glimpses'=>$glimpses,
            'returnlink'=>"returnlink",
            ]
        );
    }

    public function showsource(ManagerRegistry $doctrine,$sourceid)
    {
        $source = $doctrine->getRepository(Source::class)->findOne($sourceid);
        $glimpses = $doctrine->getRepository(Glimpse::class)->viewsource($sourceid);
        $typelist = $this->templatesrc->getTypes();
        return $this->render('glimpse/showsource.html.twig',
            [
            'typelist'=>$typelist,
            'source'=>$source,
            'glimpses'=>$glimpses,
            'returnlink'=>"/source/show/$sourceid",
            ]
        );
    }

}
