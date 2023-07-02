<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
           // new TwigFilter('filter_name', [AppExtensionRuntime::class, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('calAge', [$this, 'calcAge']),
            new TwigFunction('RoleFormat',[$this, 'getRoleFormat'] ),
            new TwigFunction('EventFormat',[$this, 'getEventFormat'] ),
        ];
    }


    public function calcAge(string $bdate, string $gdate)
    {
       $bdate2 = substr($bdate."-01-01",0,10);
       $gdate2 = substr($gdate."-01-01",0,10);
        dump($bdate2);
        dump($gdate2);

        $date1 = new \DateTime($bdate2);
        $date2 = new \DateTime($gdate2);


        dump($date1->format('Y'));
        dump($date2->format('Y'));
      $year1 =  (int)$date1->format('Y');
      $year2 = (int) $date2->format('Y');
      dump($year1);
      dump($year2);
        return  $year2-$year1 ;
    }

    public function getRoleFormat($aglimpse, $roles)
    {
        return "role format ";
    }

    public function getEventFormat($aglimpse, $roles)
    {
        return "event format ";
    }
}
