<?php

// src/Service/Templates.php

namespace App\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;

use App\Service\Templates;
use App\Entity\Glimpse;
use App\Entity\LifeEvent;

class Templates
{

  private  $templatelist=array();
  private  $agelist=array();


  public function __construct(string $templatedir)
  {
    $configDirectories = [$templatedir];
    $fileLocator = new FileLocator($configDirectories);
    $gstructyml = $fileLocator->locate('glimpsetypes.yml', null, false);
    $this->templatelist =   Yaml::parseFile($gstructyml[0]);
    $ageyml = $fileLocator->locate('agelist3.yml', null, false);
    $this->agelist =   Yaml::parseFile($ageyml[0]);
  }


  public function getTemplates($type)
  {
    dump($this->templatelist[$type]);
    return $this->templatelist[$type];;
  }

  public function getTypes()
  {
    return array_keys($this->templatelist);
  }

  public function getFormat($type,$role)
  {
    return $this->templatelist[$type][$role]["format"];
  }

  public function getRoleFormat($glimpse,$role,$roles)
  {
    dump($roles);
    dump($glimpse);
    dump($role);
    dump($this->templatelist);
    dump($glimpse->getType());
    dump($role->getRole());
    $fmt =  $this->templatelist[$glimpse->getType()][$role->getRole()]["format"];
    dump($fmt);
    $fmt = str_replace("#location", $glimpse->getLocation(), $fmt);
    $fmt = str_replace("#date", $glimpse->getDate(), $fmt);
    foreach($roles as $key=>$arole)
    {
      $fmt = str_replace("#".$arole->role->getRole(), $arole->role->getName(), $fmt);
    }
    dump($fmt);
    return $fmt;
  }

  public function getEventformat($glimpse,$roles)
  {
    dump($roles);
    dump($glimpse);
    if(array_key_exists("format", $this->templatelist[$glimpse->getType()]))
    {
      $fmt =  $this->templatelist[$glimpse->getType()]["format"];
      if($fmt)
      {
        $fmt = str_replace("#location", $glimpse->getLocation(), $fmt);
        $fmt = str_replace("#date", $glimpse->getDate(), $fmt);
        foreach($roles as $key=>$arole)
        {
          $fmt = str_replace("#".$arole->getRole(), $arole->getName(), $fmt);
        }
        dump($fmt);
        return $fmt;
      }
    }
    else
      return "no format found";
  }


  public function getAgelist()
  {
    return $this->agelist;
  }

  public function getLifeEvents($actorid,$gtype,$role,$gdate)
  {
    $dt = new \DateTimeImmutable($gdate);
    $dt0 = $dt;
    $dt1 = $dt;
    $dt2 = $dt;
    dump($actorid." ".$gtype." ".$role." ".$gdate);
    $ags = $this->agelist[$gtype][$role];
    dump($dt);
    $lifeevents = array();
    dump($lifeevents);
    foreach($ags as $ag)
    {
      $dt0 = $dt;
      $dt1 = $dt;
      $dt2 = $dt;
      $t = $ag["type"];
      $v = $ag["value"] +0;
      $i=1;
      if($t=="minage")
      {
        $dt0 = $dt0->modify(-$v." year");
        $newlifeevent = new LifeEvent($actorid,"birth");
        $newlifeevent->setHighDate($dt0->format("Y-m-d"));
      }
      if($t=="maxage")
      {
        $dt1 = $dt1->modify(-$v." year");
        $newlifeevent = new LifeEvent($actorid,"birth");
        $newlifeevent->setLowDate($dt1->format("Y-m-d"));
      }
      if($t=="minle")
      {
        $dt1 = $dt1->modify($v." year");
        $newlifeevent = new LifeEvent($actorid,"death");
        $newlifeevent->setLowDate($dt1->format("Y-m-d"));
      }
      if($t=="maxle")
      {
        $dt1 = $dt1->modify($v." year");
        $newlifeevent = new LifeEvent($actorid,"death");
        $newlifeevent->setHighDate($dt1->format("Y-m-d"));
      }
      if($t=="dead")
      {
        //$dt1 = $dt1->modify($v." year");
        $newlifeevent = new LifeEvent($actorid,"death");
        $newlifeevent->setHighDate($dt0->format("Y-m-d"));
        $newlifeevent->setLowDate($dt0->format("Y-m-d"));
      }

       $this->mergeLifeevents($lifeevents,$newlifeevent);
      $i=$i+1;
    }
    dump($lifeevents);
    return $lifeevents;
  }

  public function mergeLifeevents(&$lifeevents,$alifeevent)
  {
    $eventtype= $alifeevent->getEventtype();

    if(!array_key_exists($eventtype, $lifeevents))
    {
      $lifeevents[$eventtype]= $alifeevent;
    }
    else
    {
    dump($alifeevent);

          $ldate=   new \DateTime($lifeevents[$eventtype]->getLowdate());
          $nldate=   new \DateTime($alifeevent->getLowdate());
          if($nldate>$ldate) $lifeevents[$eventtype]->setLowdate($alifeevent->getLowdate());
          $hdate=  new \DateTime($lifeevents[$eventtype]->getHighDate());
          $nhdate=   new \DateTime($alifeevent->getHighdate());
          if($nhdate<$hdate) $lifeevents[$eventtype]->setHighDate($alifeevent->getHighdate());
    }
      dump($lifeevents);

  }


   public function updateLifeEvents(&$learray,$lifeevents)
   {
     foreach($lifeevents as $key=>$lifeevent)
     {
       if(!array_key_exists($key,$learray))
       {
          $learray[$key] = new LifeEvent($lifeevent->getActorref(),$key);
       }
       else
       {
         $learray[$key] = $lifeevent;
      }
    }

    }

}
