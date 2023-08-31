<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;



class AppExtension extends AbstractExtension
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
            new TwigFunction('FormatRole',[$this, 'FormatRole'] ),
            new TwigFunction('FormatEvent',[$this, 'FormatEvent'] ),
        ];
    }


    public function calcAge( $bdate, string $gdate)
    {
        if( is_null($bdate)) return 0;
       $bdate2 = substr($bdate."-01-01",0,10);
       $gdate2 = substr($gdate."-01-01",0,10);
       $date1 = new \DateTime($bdate2);
       $date2 = new \DateTime($gdate2);
       $year1 =  (int)$date1->format('Y');
       $year2 = (int) $date2->format('Y');
        return  $year2-$year1 ;
    }



    public function getEventFormat($aglimpse,$templatelist)
    {
                    dump($glimpse);
                   dump($templatelist);
                   return "event format ";
    }

    public function FormatRole($roleref,$glimpse)
    {
        dump($this->templatelist);
        $type= $glimpse->getType();
        $roles= $glimpse->roles;
        $role= $roles[$roleref]->getRole();
        dump($type);
        dump($role);
        $fmt =  $this->templatelist[$type][$role]["format"];
        $fmt = str_replace("#location", $glimpse->getLocation(), $fmt);
        $fmt = str_replace("#date", $glimpse->getDate(), $fmt);
        $krole = $roles[$roleref];
        $fmt = str_replace("#".$krole->getRole(), $krole->getName(), $fmt);
        foreach($roles as $key=>$arole)
        {
            $fmt = str_replace("#".$arole->getRole(), $arole->getName(), $fmt);
        }
        return $fmt;
    }

    public function FormatEvent($glimpse)
    {
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
}
