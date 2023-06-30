<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 */
class Role
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $roleid;

    /**
     * @ORM\Column(type="integer")
     */
    private $glimpseref;


 /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $role;


     /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $name;

     /**
     * @ORM\Column(type="text", nullable=true)
     */
    public $predicates ;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $contributor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedt;

    public function getPredicates()
    {
      $preds =explode(";", $this->predicates);
      foreach($preds as $index => $pred)
          $preds[$index] = trim($pred);
       return $preds;
    }

      public function getPredicatestr()
    {
       return $this->predicates;
    }

     public function setPredicatestr($predstr)
    {
       $this->predicates = $predstr;
    }

     public function setPredicates($predicates)
    {
       if (is_array($predicates) )
       {
        $this->predicatesr="";
        foreach($predicates as $index => $predicate)
        {
          $this->predicates .= ";".trim($predicates);
        }
       }
        else
        {
         $this->predicates =$predicates;
        }

    }

    public function getRoleId(): ?int
    {
        return $this->roleid;
    }

    public function setRoleId(int $ref): self
    {
        $this->roleid= $ref;

        return $this;
    }


   public function getGlimpseRef(): ?int
    {
        return $this->glimpseref;
    }

    public function setGlimpseRef(int $ref): self
    {
        $this->glimpseref= $ref;

        return $this;
    }


    public function getRole(): ?string
    {
        return $this->role;
    }


    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }


    public function getName(): ?string
    {
        return $this->name;
    }


    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

      public function getContributor(): ?string
    {
        return $this->contributor;
    }

    public function setContributor(?string $contributor): self
    {
        $this->contributor = $contributor;

        return $this;
    }

    public function getUpdateDt(): ?\DateTimeInterface
    {
        return $this->updatedt;
    }



    public function setUpdateDt(?\DateTimeInterface $updatedt): self
    {
        $this->updatedt = $updatedt;

        return $this;
    }




}
