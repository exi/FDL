<?php

namespace FDL\Entity;

/**
 * Customer
 *
 * @ORM\Entity(repositoryClass="MyDriver\ApplicationBundle\Repository\CustomerRepository")
 */
class Customer
{

    private $user;
    private $firstName;
    private $lastName;
    private $ownedAddress = [];
    private $residentAddress;
    public function addOwnedAddress($address)
    {
        $this->ownedAddress[] = $address;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return array
     */
    public function getOwnedAddress()
    {
        return $this->ownedAddress;
    }

    /**
     * @param array $ownedAddress
     */
    public function setOwnedAddress($ownedAddress)
    {
        $this->ownedAddress = $ownedAddress;
    }

    /**
     * @return mixed
     */
    public function getResidentAddress()
    {
        return $this->residentAddress;
    }

    /**
     * @param mixed $residentAddress
     */
    public function setResidentAddress($residentAddress)
    {
        $this->residentAddress = $residentAddress;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
