<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="lifeevent")
 * @ORM\Entity(repositoryClass="App\Repository\LifeEventRepository")
 */
class LifeEvent
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $lifeeventid;

    /**
     * @ORM\Column(type="integer")
     */
    private $actorref;




    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $eventtype;


    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $lowdate;


    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    public $highdate ;


    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    public $middate ;


    public function __construct($actorref,$type)
    {
        $this->setActorref($actorref);
        $this->seteventtype($type);
        $this->lowdate ="0000-01-01";
        $this->highdate ="9999-12-31";
    }


    public function initialise()
    {
        $this->lowdate ="0000-01-01";
        $this->highdate ="9999-12-31";
    }


    public function getlifeeventid(): ?int
    {
        return $this->lifeeventid;
    }

    public function setlifeeventid(int $ref): self
    {
        $this->lifeeventid= $ref;
        return $this;
    }


    public function getactorref(): ?int
    {
        return $this->actorref;
    }

    public function setactorref(int $ref): self
    {
        $this->actorref= $ref;
        return $this;
    }


    public function geteventtype(): ?string
    {
        return $this->eventtype;
    }


    public function seteventtype(string $text): self
    {
        $this->eventtype = $text;
        return $this;
    }


    public function gethighdate(): ?string
    {
        return $this->highdate ;
    }

    public function sethighdate($text): self
    {
        $this->highdate = $text;
        return $this;
    }

    public function getlowdate(): ?string
    {
        return $this->lowdate ;
    }


    public function setlowdate($text): self
    {
        $this->lowdate = $text;
        return $this;
    }

    public function getmiddate(): ?string
    {
        return $this->middate ;
    }


    public function setmiddate($text): self
    {
        $this->middate = $text;
        return $this;
    }


    public static  function merge(&$lifeevents,$newlifeevents)
    {
        dump($newlifeevents);
        foreach($newlifeevents as $eventtype => $lifeevent)
        {
            $yb = $lifeevent->getLowDate();
            $ya = $lifeevent->getHighDate();
            if( array_key_exists( $eventtype,$lifeevents))
            {
                $yl=  $lifeevents[$eventtype]->getLowdate();
                if($yb>$yl) $lifeevents[$eventtype]->setLowdate($yb);
                $yh=  $lifeevents[$eventtype]->getHighDate();
                if($ya<$yh) $lifeevents[$eventtype]->setHighDate($ya);
            }
            else
            {
                $lifeevents[$eventtype] = new LifeEvent($lifeevent->getActorref(),$eventtype);
                $lifeevents[$eventtype]->setLowDate($yb);
                $lifeevents[$eventtype]->setHighDate($ya);
            }
        }

    }


    public static function xsetDates($date,$agerule)
    {

        dump($date);
        dump($agerule);
        $age = intval($agerule['age']);
        $dir = $agerule['limit'];
        if($dir=="GT")
        {
            $ndate = $this->subtract($date ,$age);
            if($ndate > $this->lowdate) $this->lowdate = $ndate;
        }
        elseif($dir=="LT")
        {
            $ndate = $this->add($date ,$age);
            if($ndate < $this->highdate) $this->highdate = $ndate;
        }
        elseif($dir=="AL")
        {
            $ndate = $this->subtract($date ,$age);
            if($ndate < $this->highdate) $this->highdate = $ndate;
        }
        elseif($dir=="AM")
        {
            $ndate = $this->subtract($date ,$age);
            if($ndate > $this->lowdate) $this->lowdate = $ndate;
        }
        elseif($dir=="LE")
        {
            $ndate = $this->add($date ,$age);
            if($ndate > $this->highdate || $this->highdate=="9999-12-31") $this->highdate = $ndate;
        }
        dump($this);

    }

    public function subtract($date,$dif)
    {
        $darray = $this->parsedate($date);
        $totmonths = $darray[0] *12 + $darray[1];
        $ntotmonths = $totmonths - $dif*12;
        $ndate = $this->fuseMD($ntotmonths, $darray[2]);
        return $ndate;
    }

    public function add($date,$dif)
    {
        $darray = $this->parsedate($date);
        $totmonths = $darray[0] *12 + $darray[1];
        $ntotmonths = $totmonths + $dif*12;
        $ndate = $this->fuseMD($ntotmonths, $darray[2]);
        return $ndate;
    }

    public function parsedate($date)
    {
        $ndate= array();
        $ndate[0] = intval(substr($date, 0,4));
        $ndate[1] = intval(substr($date, 5,6));
        $ndate[2] = intval(substr($date, 8,9));
        return $ndate;
    }

    public function fuseMD($months,$days)
    {
        $years = intval($months/12);
        $resmonths = $months - $years *12;
        $syears = "".$years;
        $smonths  = substr("00".$resmonths,strlen("00".$resmonths)-2);
        $sdays = substr("00".$days,strlen("00".$days)-2);
        $ndate = $syears."-".$smonths."-".$sdays;
        return $ndate;
    }
}
