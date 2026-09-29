<?php

namespace App\Twig\Extension;

use App\Service\MyLibrary;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $templatelist = [];
    private $agelist = [];
    private $relationlist = [];

    public function __construct(MyLibrary $lib, string $templatedir)
    {
        $this->lib = $lib;
        $configDirectories = [$templatedir];
        $fileLocator = new FileLocator($configDirectories);
        $gstructyml = $fileLocator->locate('glimpsetypes.yml', null, false);
        $this->templatelist = Yaml::parseFile($gstructyml[0]);
        $ageyml = $fileLocator->locate('agelist3.yml', null, false);
        $this->agelist = Yaml::parseFile($ageyml[0]);
        $relationyml = $fileLocator->locate('relations.yml', null, false);
        $this->relationlist = Yaml::parseFile($relationyml[0]);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('calAge', [$this, 'calcAge']),
            new TwigFunction('FormatRole', [$this, 'FormatRole']),
            new TwigFunction('FormatEvent', [$this, 'FormatEvent']),
            new TwigFunction('FormatEventFull', [$this, 'FormatEventFull']),
            new TwigFunction('FormatRoleFull', [$this, 'FormatRoleFull']),
            new TwigFunction('FormatRelation', [$this, 'FormatRelation']),
            new TwigFunction('FormatEventfromEvent', [$this, 'FormatEventfromEvent']),
            new TwigFunction('ideLink', [$this, 'idelink']),
            new TwigFunction('json_to_list', [$this, 'json_to_list']),

        ];
    }

    public function calcAge($bdate, string $gdate)
    {
        try {
            if (is_null($bdate)) {
                return 0;
            }
            if (strlen($bdate) < 1) {
                return 0;
            }
            if (!is_numeric($bdate[0])) {
                $bdate = substr($bdate, 1);
            }
            $bdate2 = substr($bdate.'-01-01', 0, 10);
            if (!is_numeric($gdate[0])) {
                $gdate = substr($gdate, 1);
            }
            $gdate2 = substr($gdate.'-01-01', 0, 10);


            if(str_contains($bdate2,"no date")) return 0;

            try
            {
                $date1 = new \DateTime($bdate2);
                $year1 = (int) $date1->format('Y');
            } catch (Throwable  $e)
            {
                return '=-=-=';
            }
            try
            {
                $date2 = new \DateTime($gdate2);
            } catch (Throwable  $e) {
                return '=-=-=';
            }

            $year2 = (int) $date2->format('Y');

            return $year2 - $year1;
        } catch (Exception $e) {
            return '****';
        }
    }

    public function getEventFormat($aglimpse, $templatelist)
    {
        //   dump($templatelist);
        return 'event format ';
    }

    public function FormatRole($roleref, $glimpse)
    {
        $type = $glimpse->getType();
        $roles = $glimpse->roles;
        $role = $roles[$roleref]->getRole();
        $fmt = $this->templatelist[$type][$role]['format'];
        $fmt = str_replace('#location', $glimpse->getLocation(), $fmt);
        $fmt = str_replace('#date', $glimpse->getDate(), $fmt);
        $krole = $roles[$roleref];
        $fmt = str_replace('#'.$krole->getRole(), $krole->getName(), $fmt);
        foreach ($roles as $key => $arole) {
            $fmt = str_replace('#'.$arole->getRole(), $arole->getName(), $fmt);
        }
        $fmt = str_replace('#mother', '', $fmt);

        return $fmt;
    }

    public function FormatRelation($arelation)
    {
        $type = $arelation->getRelation();
        if (isset($this->relationlist[$type]['format'])) {
            $fmt = $this->relationlist[$type]['format'];
        } else {
            $substitutions = $this->relationlist[$type];
            if (1 == count($substitutions)) {
                $subkey = array_key_first($substitutions);
                $fmt = $this->relationlist[$subkey]['format'];
            } else {
                $mask = $arelation->{'mask'};
                foreach ($substitutions as $key => $sub) {
                    if ('format' == $key or 'condition' == $key) {
                    } else {
                        if ($this->lib->matchMask($mask, $sub['condition'])) {
                            $fmt = $this->relationlist[$key]['format'];
                            $found = true;
                        }
                    }
                }
            }
        }
    if (!isset($fmt))
    {
       return " Error in formating ".$arelation->getRelationid()." with key ".$type;
    }
        $fmt = str_replace('#date', $arelation->getDate(), $fmt);
        $actor1text = $arelation->{'actor1'}->getLabel();
        $actor2text = $arelation->{'actor2'}->getLabel();
        $fmt = str_replace('#actor1', $actor1text, $fmt);
        $fmt = str_replace('#actor2', $actor2text, $fmt);

        return $fmt;
    }

    public function FormatEventfromEvent($event)
    {
        if (null == $event) {
            return '++null++';
        }
        $type = $event->getEventType();
        $role = $event->getRole();
        $fmt = $this->templatelist[$type]['format'];
        $fmt = str_replace('#location', $event->getLocation(), $fmt);
        $fmt = str_replace('#date', $event->getDate(), $fmt);

        return $fmt;
    }

    public function FormatEvent($glimpse)
    {
        if (null == $glimpse) {
            return '++null++';
        }
        $type = $glimpse->getType();
        $roles = $glimpse->roles;
        $fmt = $this->templatelist[$type]['format'];
        $fmt = str_replace('#location', $glimpse->getLocation(), $fmt);
        $fmt = str_replace('#date', $glimpse->getDate(), $fmt);
        if (!is_null($roles)) {
            foreach ($roles as $key => $arole) {
                $fmt = str_replace('#'.$arole->getRole(), $arole->getName(), $fmt);
            }
        }
        $fmt = str_replace('#mother', '', $fmt);

        return $fmt;
    }

    public function FormatEventFull($glimpse, $rolekey)
    {
        $type = $glimpse->getType();
        $roles = $glimpse->roles;
        $fmt = $this->templatelist[$type]['format'];
        $fmt = str_replace('#location', $glimpse->getLocation(), $fmt);
        $fmt = str_replace('#date', $glimpse->getDate(), $fmt);
        $ps = '';
        if (!is_null($roles)) {
            foreach ($roles as $key => $arole) {
                $aname = $arole->getName();
                if ($key == $rolekey) {
                    // $aname = strtoupper($aname);
                    $aname = "<span class='relation' >".$aname.'</span>';
                }
                if (str_contains($fmt, '#'.$arole->getRole())) {
                    $fmt = str_replace('#'.$arole->getRole(), $aname, $fmt);
                } else {
                    $ps .= ' '.$arole->getRole().':'.$aname;
                }
            }
            $ps = str_replace('#mother', '', $ps);
            $fmt .= $ps;
        }

        return $fmt;
    }

    public function FormatRoleFull($glimpse, $rolekey)
    {
        $type = $glimpse->getType();
        $roles = $glimpse->roles;
        if (null == $roles) {
            return '==+==';
        }
        $roletype = $roles[$rolekey]->getRole();
        $fmt = $this->templatelist[$type][$roletype]['format'];
        $fmt = str_replace('#location', $glimpse->getLocation(), $fmt);
        $fmt = str_replace('#date', $glimpse->getDate(), $fmt);
        if (!is_null($roles)) {
            $arole = $roles[$rolekey];
            $aname = $arole->getName();
            $aname = "<span class='relation' >".$aname.'</span>';
            if (str_contains($fmt, '#'.$arole->getRole())) {
                $fmt = str_replace('#'.$arole->getRole(), $aname, $fmt);
            }
            foreach ($roles as $key => $arole) {
                $aname = $arole->getName();
                if ($key != $rolekey) {
                    if (str_contains($fmt, '#'.$arole->getRole())) {
                        $fmt = str_replace('#'.$arole->getRole(), $aname, $fmt);
                    }
                }
            }
        }
        $fmt = str_replace('#mother', '', $fmt);

        return $fmt;
    }

    // Source - https://stackoverflow.com/a/73573691
    // Posted by Thomas Landauer
    // Retrieved 2026-04-26, License - CC BY-SA 4.0

    public function ideLink(string $filepath, int $line = 0): string
    {
        return $this->fileLinkFormatter->format($this->kernel_project_dir.DIRECTORY_SEPARATOR.$filepath, $line);
    }

// Source - https://stackoverflow.com/a/47560097
// Posted by Ryan Griggs
// Retrieved 2026-09-29, License - CC BY-SA 3.0

// Return a string of separated values from a JSON string
// Can optionally specify a separator.  If none provided, ", " is used.
//$function = new Twig_SimpleFunction('json_to_list', function($json, $separator = ", ")
//$function = new Twig_SimpleFunction('json_to_list',

 public function json_to_list($json, $separator = ", ")
{
    $result = "";
    $array = json_decode($json, true);
    foreach ($array as $item)
    {
        if ($result != "") { $result .= $separator; }
        $result .= $item;
    }
    return $result;
}




}
