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

class Templates
{

 private  $templatelist=array();
  private  $agelist=array();


    public function __construct($templatedir)
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

    public function getDates($gtype,$gdate,$role)
    {
      dump($gtype."-".$gdate."-".$role);
      return ;
    }


}
