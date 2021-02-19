<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $idnumber;

    /**
     * @ORM\ManyToOne(targetEntity=Position::class, inversedBy="users")
     */
    private $position_id;

    /**
     * @ORM\ManyToOne(targetEntity=Department::class, inversedBy="users")
     */
    private $department_id;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getIdnumber(): ?string
    {
        return $this->idnumber;
    }

    public function setIdnumber(string $idnumber): self
    {
        $this->idnumber = $idnumber;

        return $this;
    }

    public function getPositionId(): ?Position
    {
        return $this->position_id;
    }

    public function setPositionId(?Position $position_id): self
    {
        $this->position_id = $position_id;

        return $this;
    }

    public function getDepartmentId(): ?Department
    {
        return $this->department_id;
    }

    public function setDepartmentId(?Department $department_id): self
    {
        $this->department_id = $department_id;

        return $this;
    }
    public function __toString(){
        return $this->getId();
    }

    public function __construct($firstname, $lastname, $idnumber, $position, $department)
    {    
    $this->firstname=  $firstname;  
    $this->lastname=  $lastname;  
    $this->idnumber=$idnumber;   
    $this->position=$position;    
    $this->department =$department;
    }

}