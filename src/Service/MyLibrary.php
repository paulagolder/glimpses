<?php

// src/Service/MyLibrary.php

namespace App\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;



class MyLibrary
{




     private $requestStack ;


    public function __construct(  RequestStack $request_stack)
    {

        $this->requestStack = $request_stack;

    }



     public function getCookieRegion()
    {

     $request = new Request();
     $cookies = $request->cookies;
     $reg = $_COOKIE["glimpses_region"];
      if($reg) return $reg;
      else return '' ;
    }


    public function getCookieFilter()
    {
     $request = $this->requestStack->getCurrentRequest();

     $cookies = $request->cookies;
     $reg="";
     if ($cookies->has('glimpses_filter'))
     {
       $reg =$cookies->get("glimpses_filter");
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
        $res->headers->setCookie( $cookie );
        $res->send();
        dump($cookie);
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

     public function setCookieFilter($filter)
    {

        $cookie = new Cookie
        (
            'glimpses_filter',    // Cookie name.
            $filter,    // Cookie value.
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
}



