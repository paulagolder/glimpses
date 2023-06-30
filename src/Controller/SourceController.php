<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Persistence\ManagerRegistry;

use App\Service\Templates;


use App\Entity\Source;
use App\Entity\Glimpse;



class SourceController extends AbstractController
{

    private $requestStack ;


    public function __construct( RequestStack $request_stack)
    {

        $this->requestStack = $request_stack;
    }


    public function  new()
    {
        $source = new Source();
        $source->setSourceId(0);
        return $this->render('source/edit.html.twig', array(

            'source' => $source,
            'returnlink' => "/source/all",

            ));

    }





    public function  edit(ManagerRegistry $doctrine,$sid)
    {
        $source = $doctrine->getRepository(Source::class)->findOne($sid);

        return $this->render('source/edit.html.twig', array(
            'source' => $source,
            'returnlink' => "/source/show/".$sid,
            ));

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



    public function  delete(ManagerRegistry $doctrine,$gid)
    {

        $roles =  $doctrine->getRepository(Source::class)->delete($gid);
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
            $source = $doctrine->getRepository(Source::class)->findOne($sid);
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() == 'POST')
        {

            $source->setLanguage($request->request->get('_language'));
            $source->setRegion($request->request->get('_region'));
            $source->setTitle($request->request->get('_title'));
            $source->setPeriod($request->request->get('_period'));
            $source->setUrl($request->request->get('_url'));


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

    $source = $doctrine->getRepository(Source::class)->findOne($sid);
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

    $sources = $doctrine->getRepository(Source::class)->getAll();
    foreach($sources as $key=> $source)
    {
     $stats =   $doctrine->getRepository(Glimpse::class)->Countglimpses($source->getSourceid());
     $source->stats = $stats;
    }
    dump($sources);

    return $this->render('source/showall.html.twig',
        [
        'sources'=>$sources,

        ]
        );
}

}
