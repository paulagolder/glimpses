<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Persistence\ManagerRegistry;

use App\Service\Templates;
use App\Service\MyLibrary;

use App\Entity\Source;
use App\Entity\Glimpse;

class SourceController extends AbstractController
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

    public function  new()
    {
        $source = new Source();
        $source->setSourceId(0);
        return $this->render('source/edit.html.twig', array(
            'source' => $source,
            'returnlink' => "/source/showall",
            ));
    }

    public function  edit(ManagerRegistry $doctrine,$sid)
    {
        $source = $doctrine->getRepository(Source::class)->getOne($sid);
        return $this->render('source/edit.html.twig', array(
            'source' => $source,
            'returnlink' => "/source/show/".$sid,
            ));
    }

    public function setfilter()
    {
        $request = $this->requestStack->getCurrentRequest();
        $pfield = $request->query->get('filter');
        if (is_null($pfield))
        {
            $this->lib->clearCookieFilter("source");
        } else
        {
            $this->lib->setCookieFilter('source', $pfield);
        }
        return $this->redirect("/source/showall/");
    }

    public function clearfilter(ManagerRegistry $doctrine)
    {
        $pfield = "";
        $this->lib->clearCookieFilter("source");
        return $this->redirect("/source/showall/");
    }


    public function  selectinput(ManagerRegistry $doctrine,$location)
    {
        $sources = $doctrine->getRepository(Source::class)->findbyLocation($location);

           return $this->render('source/showall.html.twig',
           [
            'sources'=>$sources,
            ]
               );
   }

    public function  delete(ManagerRegistry $doctrine,$sid)
    {
         $doctrine->getRepository(Source::class)->delete($sid);
        return $this->redirect("/source/showall/");
    }

    public function  process_edit(ManagerRegistry $doctrine,$sid)
    {
        if($sid <1)
        {
            $source= new Source();
        }
        else
        {
            $source = $doctrine->getRepository(Source::class)->getOne($sid);
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {
            $source->setLanguage($request->request->get('_language'));
            $source->setRegion($request->request->get('_region'));
            $source->setTitle($request->request->get('_title'));
            $source->setPeriod($request->request->get('_period'));
            $source->setUrl($request->request->get('_url'));
            $source->setGlimpsetypes($request->request->get('_glimpsetypes'));
            $entityManager = $doctrine->getManager();
            $entityManager->persist($source);
            $entityManager->flush();
            $sid = $source->getSourceid();
            return $this->redirect("/source/edit/".$sid);
        }
        return $this->render('source/edit.html.twig', array(

            'source' => $source,
            'returnlink' => "/source/show/".$sid,
            ));
    }

public function showone(ManagerRegistry $doctrine,$sid)
{
    $source = $doctrine->getRepository(Source::class)->getOne($sid);
    return $this->render(
        'source/show.html.twig',
        [
        'source'=>$source,
        'returnlink'=>"/glimpses",
        ]
        );
}

public function showall(ManagerRegistry $doctrine)
{
    $pfield =   $this->lib->getCookieFilter('source');
 dump($pfield);
            if (is_null($pfield))
            {
                 $sources  = $doctrine->getRepository(Source::class)->findAll();
            }
            else
            {
                $filter = "%".$pfield."%";
                 $sources  = $doctrine->getRepository(Source::class)->filterf($filter);
            }

    foreach($sources as $key=> &$source)
    {
     $stats =   $doctrine->getRepository(Glimpse::class)->Countglimpses($source->getSourceid());
     $source->stats = $stats;
    }
    dump($sources);

    return $this->render('source/showall.html.twig',
        [
        'sources'=>$sources,
        'filter'=>$pfield,
        'returnlink'=>"returnlink",
        ]
        );
    }
}