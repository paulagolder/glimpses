<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\ActorRole;
use App\Entity\Glimpse;
use App\Entity\Predicate;
use App\Entity\Role;
use App\Entity\Source;
use App\Service\MyLibrary;
use App\Service\Templates;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GlimpseController extends AbstractController
{
    private $requestStack;
    private $templatesrc;
    private $lib;

    public function __construct(Templates $templates, MyLibrary $lib, RequestStack $request_stack, string $templatedir, string $jsonroot)
    {
        $this->templatesrc = $templates;
        $this->requestStack = $request_stack;
        $this->lib = $lib;
        $this->jsonroot =$jsonroot;
    }

    public function showone(ManagerRegistry $doctrine, $gid)
    {
        $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($gid);
        $roles = $doctrine->getRepository(Role::class)->findChildren($gid);
        if (null != $glimpse) {
            $source = $doctrine->getRepository(Source::class)->getOne($glimpse->getSourceid());
        } else {
            $source = '';
        }
        $duplicates = $doctrine->getRepository(Glimpse::class)->findDuplicates($glimpse);
        foreach ($duplicates as &$dup) {
            $dup->{'roles'} = $doctrine->getRepository(Role::class)->findChildren($dup->getGlimpseId());
        }

        return $this->render(
            'glimpse/show.html.twig',
            [
                'glimpse' => $glimpse,
                'roles' => $roles,
                'source' => $source,
                'duplicates' => $duplicates,
                'returnlink' => 'returnlink',
            ]
        );
    }

    public function clearfilter(ManagerRegistry $doctrine)
    {
        $pfield = '';
        $this->lib->clearCookieFilter('glimpse');

        return $this->redirect('/glimpse/showall/');
    }

    public function showall(ManagerRegistry $doctrine)
    {
        $pfield = $this->lib->getCookieFilter('glimpse');
        if (is_numeric($pfield)) {
            $glimpses[] = $doctrine->getRepository(Glimpse::class)->getOne($pfield);
        } else {
              $filterlist= $this->lib->parseFilter( $pfield);
            $glimpses = $doctrine->getRepository(Glimpse::class)->filterf($filterlist);
        }

        foreach ($glimpses as &$glimpse) {
            $glimpse->{'roles'} = $doctrine->getRepository(Role::class)->findChildren($glimpse->getGlimpseId());
        }

        return $this->render(
            'glimpse/showall.html.twig',
            [
                'glimpses' => $glimpses,
                'filter' => $pfield,
                'returnlink' => 'returnlink',
            ]
        );
    }

    public function showregion(ManagerRegistry $doctrine, $region)
    {
        $glimpses = $doctrine->getRepository(Glimpse::class)->viewregion($region);

        return $this->render(
            'glimpse/showregion.html.twig',
            [
                'region' => $region,
                'glimpses' => $glimpses,
                'returnlink' => 'returnlink',
            ]
        );
    }

    public function showsource(ManagerRegistry $doctrine, $sourceid)
    {
        $source = $doctrine->getRepository(Source::class)->getOne($sourceid);
        $glimpses = $doctrine->getRepository(Glimpse::class)->viewsource($sourceid);
        $typelist = $this->templatesrc->getTypes();

        return $this->render('glimpse/showsource.html.twig',
            [
                'typelist' => $typelist,
                'source' => $source,
                'glimpses' => $glimpses,
                'returnlink' => "/source/show/$sourceid",
            ]
        );
    }

    public function filter(ManagerRegistry $doctrine)
    {
        $request = $this->requestStack->getCurrentRequest();
        $pfield = $request->query->get('filter');

        if (is_null($pfield)) {
            $this->lib->clearCookieFilter('glimpse');
        } else {
            $this->lib->setCookieFilter('glimpse', $pfield);
        }
        if (!$pfield) {
            $glimpses = $doctrine->getRepository(Glimpse::class)->findAll();
        } else {
            if (is_numeric($pfield)) {
                $glimpses[] = $doctrine->getRepository(Glimpse::class)->getOne($pfield);
            } else {
                $filter = $pfield;
                 $filterlist= $this->lib->parseFilter($filter);
                $glimpses = $doctrine->getRepository(Glimpse::class)->filterf($filterlist);
            }
        }
        foreach ($glimpses as &$glimpse) {
            $glimpse->{'roles'} = $doctrine->getRepository(Role::class)->findChildren($glimpse->getGlimpseId());
        }

        return $this->render(
            'glimpse/showall.html.twig',
            [
                'glimpses' => $glimpses,
                'filter' => $pfield,
                'returnlink' => 'returnlink',
            ]
        );
    }

    public function dataentry(ManagerRegistry $doctrine)
    {
        $glimpse = new Glimpse();
        $glimpse->setType($type);
        $glimpse->setContributor('paul');
        $glimpse->setGlimpseId(0);
        $roles = [];
        $roletemplate = $this->templatesrc->getTemplates($type);
        foreach ($roletemplate as $key => $card) {
            // dump($card);
            $role = new Role();
            $role->setRole($key);
            $roles[] = $role;
        }

        return $this->render('glimpse/edit.html.twig', [
            'glimpse' => $glimpse,
            'roles' => $roles,
            'returnlink' => '/glimpse/new',
            'typelist' => ['baptism', 'marriage', 'burial'],
        ]);
    }

    public function new(ManagerRegistry $doctrine, $type)
    {
        $sourceid = $this->lib->getCookieSource();
        $source = $doctrine->getRepository(Source::class)->getOne($sourceid);
        $glimpse = new Glimpse();
        $glimpse->{'source'} = $source->getTitle();
        $glimpse->setLocation($source->getLocality());
        $glimpse->setSourceId($sourceid);
        $glimpse->setLanguage($source->getlanguage());
        $glimpse->setType($type);
        $glimpse->setContributor('paul');
        $now = new \DateTime();
        $glimpse->setUpdateDt($now);
        $glimpse->setGlimpseId(0);
        $roles = [];
        $typelist = $this->templatesrc->getTypes();
        // dump($typelist);
        if ('X' != $type) {
            $roletemplate = $this->templatesrc->getTemplates($type);
            // dump( $roletemplate );
            $ir = 0;
            foreach ($roletemplate as $key => $card) {
                // dump($key);
                // dump($card);
                $nr = 1;
                if (is_array($card)) {
                    if (array_key_exists('cardinality', $card)) {
                        $nr = $card['cardinality'];
                    } else {
                        $nr = 1;
                    }
                } else {
                    $nr = $card;
                }
                if ($nr < 1) {
                    $nr = 1;
                }
                // dump($nr);
                for ($r = 1; $r <= $nr; ++$r) {
                    // dump($r);
                    $role = new Role();
                    $role->setRole($key);
                    $roles[$ir] = $role;
                    ++$ir;
                    if ($ir > 10) {
                        break;
                    }
                }
            }

            return $this->render('glimpse/edit.html.twig', [
                'glimpse' => $glimpse,
                'source' => $source,
                'roles' => $roles,
                'returnlink' => '/glimpses',
                'typelist' => $typelist,
            ]);
        }

        return $this->render('glimpse/selecttypes.html.twig', [
            'returnlink' => '/glimpse/new',
            'typelist' => $typelist,
        ]);
    }

    public function startinput(ManagerRegistry $doctrine, $sourceid)
    {
        $source = $doctrine->getRepository(Source::class)->getOne($sourceid);
        $this->lib->setCookieSource($sourceid);

        return $this->render('source/startinput.html.twig', [
            'returnlink' => '/glimpse/new',
            'source' => $source,
        ]);
    }

    public function edit(ManagerRegistry $doctrine, $gid)
    {
        $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($gid);
        $source = $doctrine->getRepository(Source::class)->getOne($glimpse->getSourceid());
        $roles = $doctrine->getRepository(Role::class)->findChildren($gid);
        $allactors = $doctrine->getRepository(Actor::class)->findAll();
        foreach ($roles as &$role) {
            $actors = $doctrine->getRepository(ActorRole::class)->getActors($role->getRoleId());
            // dump($actors);
            $role->{'actors'} = $actors;
        }
        // dump($roles);
        $nrole = new Role();
        $nrole->setGlimpseref($gid);
        $roles[] = $nrole;
        $glimpse->{'source'} = $source->getTitle();

        return $this->render('glimpse/edit.html.twig', [
            'glimpse' => $glimpse,
            'source' => $source,
            'roles' => $roles,
            'actors' => $allactors,
            'returnlink' => '/glimpse/showone/'.$gid,
            'typelist' => ['baptism', 'marriage', 'burial'],
        ]);
    }

    public function edit_role(ManagerRegistry $doctrine, $gid, $pref)
    {
        $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($gid);
        $roles = $doctrine->getRepository(Role::class)->findChildren($gid);
        $allactors = $doctrine->getRepository(Actor::class)->findRoleMatches($roles[$pref]);

        return $this->render('glimpse/editrole.html.twig', [
            'glimpse' => $glimpse,
            'roles' => $roles,
            'activerole' => $pref,
            'actors' => $allactors,
            'returnlink' => '/glimpse/edit/'.$gid,
        ]);
    }

    public function delete(ManagerRegistry $doctrine, $gid)
    {
        $doctrine->getRepository(Glimpse::class)->delete($gid);
        $doctrine->getRepository(Role::class)->deleteAllinGlimpse($gid);

        return $this->redirect('/glimpse/showall/');
    }

    public function delete_role($gid, $pref)
    {
        $roles = $doctrine->getRepository(Role::class)->delete($gid, $pref);

        return $this->redirect('/glimpse/edit/'.$gid);
    }

    public function delete_predicate($gid, $aref, $pref)
    {
        $roles = $doctrine->getRepository('App:Predicate')->delete($gid, $aref, $pref);

        return $this->redirect('/glimpse/editrole/'.$gid.'/'.$aref);
    }

    public function process_edit(ManagerRegistry $doctrine, $gid)
    {
        if ($gid < 1) {
            $glimpse = new Glimpse();
            $sourceid = $this->lib->getCookieSource();
            $source = $doctrine->getRepository(Source::class)->getOne($sourceid);
            $glimpse->setLocation($source->getRegion());
            $glimpse->setSourceId($sourceid);
            $glimpse->setLanguage($source->getlanguage());
            $glimpse->{'source'} = $source->getTitle();
            $roles = [];
        } else {
            $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($gid);
            $source = $doctrine->getRepository(Source::class)->getOne($glimpse->getSourceid());
            $roles = $doctrine->getRepository(Role::class)->findChildren($gid);
            $glimpse->{'source'} = $source->getTitle();
        }

        $request = $this->requestStack->getCurrentRequest();
        if ('POST' == $request->getMethod()) {
            $glimpse->setText($request->request->get('_text'));
            // $glimpse->setLanguage($request->request->get('_language'));
            $glimpse->setLocation($request->request->get('_location'));
            $glimpse->setType($request->request->get('_type'));
            $glimpse->setDate($request->request->get('_gdate'));
            $glimpse->setRef($request->request->get('_ref'));
            $glimpse->setImage($request->request->get('_image'));
            $glimpse->setText($request->request->get('_text'));
            $glimpse->setContributor('paul');
            $now = new \DateTime();
            $glimpse->setUpdateDt($now);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($glimpse);
            $entityManager->flush();
            $gid = $glimpse->getGlimpseid();
            $rqroles = $request->request->all()['_role'];
            foreach ($rqroles as $key => $arole) {
                $ref = $arole["'ref'"];
                if (null == $ref || 0 == $ref) {
                    $nrole = new Role();
                    $nrole->setGlimpseref($gid);
                    $nrole->setRole(strtolower(trim($arole["'role'"])));
                    $nrole->setName($arole["'name'"]);
                    $nrole->setPredicates($arole["'predicates'"]);
                    if ($nrole->getName()) {
                        $entityManager->persist($nrole);
                        $entityManager->flush();
                    }
                } else {
                    $nrole = $roles[$key];
                    // $nrole->setText($arole["'text'"]);
                    $nrole->setRole(trim($arole["'role'"]));
                    $nrole->setName($arole["'name'"]);
                    $nrole->setPredicates($arole["'predicates'"]);
                    $entityManager->persist($nrole);
                    $entityManager->flush();
                }
            }

            return $this->redirect('/glimpse/edit/'.$gid);
        }

        // dump($glimpse);
        return $this->render('glimpse/edit.html.twig', [
            'glimpse' => $glimpse,
            'roles' => $roles,
            'returnlink' => '/glimpse/showone/'.$gid,
            'typelist' => ['baptism', 'marriage', 'burial', 'will', 'note'],
        ]);
    }

    public function process_editrole(ManagerRegistry $doctrine, $gid, $aref)
    {
        $entityManager = $doctrine->getManager();
        $glimpse = $doctrine->getRepository(Glimpse::class)->getOne($gid);
        $role = $doctrine->getRepository(Role::class)->getOne($aref);
        //  $predicates =  $doctrine->getRepository("App:Predicate")->findChildren($gid,$aref);
        $request = $this->requestStack->getCurrentRequest();
        if ('POST' == $request->getMethod()) {
            $selactor = $request->request->all()['_selactorref'];
            dump(' actor sell:'.$selactor);
            if (is_numeric($selactor)) {
                $actorrole = $doctrine->getRepository(ActorRole::class)->findOne($selactor, $aref);
                if ($actorrole) {
                } else {
                    $actorrole = new ActorRole();
                    $actorrole->setActorref($selactor);
                    $actorrole->setRoleRef($aref);
                    $entityManager->persist($actorrole);
                    $entityManager->flush();
                }
            }
            $doctrine->getRepository(ActorRole::class)->getActors($role->getRoleId());
            $rqroles = $request->request->all()['_role'];
            $rrole = $rqroles[$aref];
            $glimpse->setContributor('paul');
            $now = new \DateTime();
            $glimpse->setUpdateDt($now);

            $entityManager->persist($glimpse);
            $entityManager->flush();
            $role->setRole($rrole["'role'"]);
            $role->setName($rrole["'name'"]);
            $entityManager->persist($role);
            $entityManager->flush();
            $preds = $rrole["'predicates'"];
            $role->setPredicates($preds);
            $entityManager->persist($role);
            $entityManager->flush();

            return $this->redirect('/glimpse/edit/'.$gid);
        }

        return $this->render('glimpse/edit.html.twig', [
            'glimpse' => $glimpse,
            'roles' => $roles,
            'returnlink' => '/glimpse/showone/'.$gid,
        ]);
    }


function choosejson()
{
     return $this->render('glimpse/choosejson.html.twig', [
                'returnlink' => '/glimpse/showall/',
            ]);
    }

function loadjson($jfile)
{
    $json = file_get_contents( $this->jsonroot.$jfile);

    if ($json === false) {
        die('Error reading the JSON file');
    }
    $json_data = json_decode($json, true);
    dump($json_data);
    if ($json_data === null) {
        die('Error decoding the JSON file:'.$jfile);
    }
    foreach( $json_data as $jglimpse )
    {
    dump($jglimpse);
   // aglimpse= new Glimpse()

    }



 return $this->render('glimpse/loadjson.html.twig', [
                'returnlink' => '/glimpse/showall/',
                'filename' => $jfile,
                'jsondata'  => $json_data,
            ]);
}


function viewjson($jfile,$jid)
{
    $json = file_get_contents( $this->jsonroot.$jfile);
    if ($json === false) {
        die('Error reading the JSON file');
    }
    $json_data = json_decode($json, true);
    if ($json_data === null) {
        die('Error decoding the JSON file:'.$jfile);
    }

    dump($json_data[$jid]);
   // aglimpse= new Glimpse()


 return $this->render('glimpse/viewjson.html.twig', [
                'returnlink' => '/glimpse/showall/',
                'filename' => $jfile,
                'jsondata'  => $json_data[$jid],
            ]);
}

}
