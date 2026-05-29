<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class MyLibrary
{
     private  $templatelist=array();
     private  $agelist=array();
     private  $requestStack ;
     private  $relationlist=array();


    public function __construct(  RequestStack $request_stack,$templatedir)
    {

        $this->requestStack = $request_stack;
        $configDirectories = [$templatedir];
        $fileLocator = new FileLocator($configDirectories);
        $gstructyml = $fileLocator->locate('glimpsetypes.yml', null, false);
        $this->templatelist =   Yaml::parseFile($gstructyml[0]);
        $ageyml = $fileLocator->locate('agelist3.yml', null, false);
        $this->agelist =   Yaml::parseFile($ageyml[0]);
        $relationyml = $fileLocator->locate('relations.yml', null, false);
        $this->relationlist =   Yaml::parseFile($relationyml[0]);
    }

    public function xgetCookieRegion()
    {
     $request = new Request();
     $cookies = $request->cookies;
     $reg = $_COOKIE["glimpses_region"];
      if($reg) return $reg;
      else return '' ;
    }

    public function getCookieFilter($type)
    {
      $request = $this->requestStack->getCurrentRequest();
     //$request = new Request();
     $cookies = $request->cookies;
     $reg="";
     if ($cookies->has($type.'_filter'))
     {
       $reg =$cookies->get($type."_filter");
     }
      if($reg) return $reg;
      else return '' ;
    }

    public function setCookieSource($sourceid)
    {
       $cookie = new Cookie
       (
            'glimpses_source',    // Cookie name.
            $sourceid,    // Cookie value.
           time() + ( 24 * 60 * 60)  // Expires 1 day .
        );
        $res = new Response();
        $res->headers->setCookie($cookie);
        $res->send();
    }

    public function getCookieSource()
    {
     $request = new Request();
     $cookies = $request->cookies;
     $reg = $_COOKIE["glimpses_source"];
      if($reg) return $reg;
      else return 0 ;
    }

    public function setCookieRegion($region)
    {
        $path ="localhost";
        $cookie = new Cookie
        (
            'glimpses_region',    // Cookie name.
            $region,    // Cookie value.
            time() + ( 24 * 60 * 60),  // Expires 1 day .
            $path
        );
        $res = new Response();
        $res->headers->setCookie( $cookie );
        $res->send();
    }

     public function setCookieFilter($type,$filter)
    {
        $cookie = new Cookie
        (
            $type.'_filter',    // Cookie name.
            $filter,    // Cookie value.
            time() + ( 24 * 60 * 60)  // Expires 1 day .
        );
        $res = new Response();
        $res->headers->setCookie( $cookie );
        $res->send();
    }

   public function clearCookieFilter($type)
    {
        $cookie = new Cookie
        (
            $type.'_filter',    // Cookie name.
            "",    // Cookie value.
            time() + ( 24 * 60 * 60)  // Expires 1 day .
        );
        $res = new Response();
        $res->headers->setCookie( $cookie );
        $res->send();
    }

     static public function formatDate($date, $lang)
    {
       setlocale(LC_TIME, "");
       if($lang =="EN" | $lang=="en" )
           setlocale (LC_TIME, 'en_EN.utf-8');
       else
           setlocale (LC_TIME, 'fr_FR.utf-8','fr_FR');

       if(substr($date,5,4)=="0000")
           return substr($date,0,4);

      else if(substr($date,7,2)=="00")
      {
        $ddate = substr($date,0,6)."01";
        $dfdate = strtotime($ddate);
          return  strftime('%B %G', $dfdate);
      }
       if (($timestamp = strtotime($date)) === false)
       {
         return " ".$date;
       } else
       {
          $dfdate = strtotime($date);
          return strftime('%A %d %B %G', $dfdate);
       }
    }

    protected function makeLikeParam($search, $pattern = '%%%s%%')
    {
      /**
       * Function defined in-line so it doesn't show up for type-hinting on
       * classes that implement this trait.
       *
       * Makes a string safe for use in an SQL LIKE search query by escaping all
       * special characters with special meaning when used in a LIKE query.
       *
       * Uses ! character as default escape character because \ character in
       * Doctrine/DQL had trouble accepting it as a single \ and instead kept
       * trying to escape it as "\\". Resulted in DQL parse errors about "Escape
       * character must be 1 character"
       *
       * % = match 0 or more characters
       * _ = match 1 character
       *
       * Examples:
       *      gloves_pink   becomes  gloves!_pink
       *      gloves%pink   becomes  gloves!%pink
       *      glo_ves%pink  becomes  glo!_ves!%pink
       *
       * @param string $search
       * @return string
       */
      $sanitizeLikeValue = function ($search) {
        $escapeChar = '!';

        $escape = [
        '\\' . $escapeChar, // Must escape the escape-character for regex
        '\%',
        '\_',
        ];
        $pattern = sprintf('/([%s])/', implode('', $escape));

        return preg_replace($pattern, $escapeChar . '$0', $search);
      };

      return sprintf($pattern, $sanitizeLikeValue($search));
    }

     static public function parseFilter($filterstring)
     {
        $filterlist = explode(",", $filterstring);

         return $filterlist;
     }

    public function FormatEvent($glimpse)
    {
    //dump($this->templatelist);
        if($glimpse == null) return "++null++";
        $type= $glimpse->getType();
        $roles= $glimpse->roles;
        $fmt =  $this->templatelist[$type]["format"];
        $fmt = str_replace("#location", $glimpse->getLocation(), $fmt);
        $fmt = str_replace("#date", $glimpse->getDate(), $fmt);
        if(!is_null($roles))
        {
          foreach($roles as $key=>$arole)
          {
              $fmt = str_replace("#".$arole->getRole(), $arole->getName(), $fmt);
          }
        }
        return $fmt;
    }

 public function FormatEventFull($glimpse,$rolekey)
    {
        $type= $glimpse->getType();
        $roles= $glimpse->roles;
        $fmt =  $this->templatelist[$type]["format"];
        $fmt = str_replace("#location", $glimpse->getLocation(), $fmt);
        $fmt = str_replace("#date", $glimpse->getDate(), $fmt);
        $ps ="";
        if(!is_null($roles))
        {
        foreach($roles as $key=>$arole)
        {
           $aname = $arole->getName();
        if($key==$rolekey)
        {
          //$aname = strtoupper($aname);
          $aname = "<span class='relation' >".$aname."</span>";
        }
            if (str_contains($fmt, "#".$arole->getRole()))
            {
               $fmt = str_replace("#".$arole->getRole(), $aname, $fmt);
            }else
            {
               $ps .= " ".$arole->getRole().":". $aname;
            }
        }
        $fmt .= $ps;
        }
        return $fmt;
    }



    public function normaliseRelation($arelation)
    {
            $newrelation = $arelation;
            $type= $arelation->getRelation();
            $substitutions = $this->relationlist[$type];
             if( count($substitutions)==1)
             {
                      $subkey= array_key_first($substitutions);
                      if($subkey=="format") return $newrelation;
                      $newrelation->setRelation($subkey);
                      return  $newrelation;
             }
             else
             {
                 $mask= $arelation->{"mask"};
                 foreach($substitutions as $key=> $sub)
                 {
                     if($key=="format" or $key =="condition" or $key=="inverse")
                     {

                     }
                     else
                     {
                       if($this->matchMask($mask,$sub["condition"]))
                       {
                          $newrelation->setRelation($key);
                          return $newrelation;
                       }
                     }
                 }

             }
             return $newrelation;
       }




    public function invertRelation($arelation)
    {
            $newrelation = $arelation;
            $type= $arelation->getRelation();
            $substitutions = $this->relationlist[$type];
             if( count($substitutions)==1)
             {
                      $subkey= array_key_first($substitutions);
                      if($subkey=="format") return $newrelation;
                      $newrelation->setRelation($subkey);
                      return  $this->invertRelation($newrelation);
             }
             else
             {
                  $mask = $arelation->{"mask"};
                 if( !array_key_exists("inverse",$substitutions)) return $newrelation;
                 $inversions = $substitutions["inverse"];
                 if(!is_array($inversions))
                 {
                   $invrelation = $newrelation->getInverse();
                   $invrelation->setRelation($inversions);
                   return $invrelation;
                 }else
                 {
                 foreach($inversions as $key=> $sub)
                 {
                 $found = $this->matchMask($mask,$sub["condition"]);
                  if($found)
                  {
                     $invrelation = $newrelation->getInverse();
                       $invrelation->setRelation($key);
                     return $invrelation;
                  }
                 }
             }
             return $newrelation;
               }

             }



    public function matchMask($mask, $candidate)
    {
      $maskarray= explode(" ",$mask);
      $candidatearray= explode(" ",$candidate);
       if($candidatearray[0]!="X")
       {
        if($maskarray[0]!=$candidatearray[0]) return false;
       }
       if($candidatearray[1]!="X")
       {
           if($maskarray[1]!=$candidatearray[1]) return false;
       }
        return true;
    }

}