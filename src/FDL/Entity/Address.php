<?php

namespace FDL\Entity;

/**
 * Address
 *
 * @ORM\Table(name="md_address")
 * @ORM\Entity(repositoryClass="MyDriver\ApplicationBundle\Repository\AddressRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Address
{
    const SALUTATION_MR = 'Mr.';
    const SALUTATION_MRS = 'Mrs.';
    const SALUTATION_COMPANY = 'Company';

    const COUNTRY_GERMANY = 'DE';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="companyName", type="string", length=255, nullable=true)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="salutation", type="string", length=7, nullable=true)
     */
    private $salutation;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="text")
     */
    private $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="text", nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=255)
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastUsed", type="datetime", nullable=true)
     */
    private $lastUsed;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="AbstractCharacter", mappedBy="addresses")
     */
    private $characters;

    /**
     * Only the owner is allowed to change the address
     *
     * @var AbstractCharacter
     *
     * @ORM\ManyToOne(targetEntity="AbstractCharacter", inversedBy="ownedAddresses")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    private $owner;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->characters = [];
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param $companyName
     * @return $this
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @param $salutation
     * @return $this
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set address2
     *
     * @param string $address2
     * @return Address
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set zip
     *
     * @param string $zip
     * @return Address
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastUsed()
    {
        return $this->lastUsed;
    }

    /**
     * @param \DateTime $lastUsed
     * @return $this
     */
    public function setLastUsed(\DateTime $lastUsed)
    {
        $this->lastUsed = $lastUsed;

        return $this;
    }

    /**
     * @return AbstractCharacter[]
     */
    public function getCharacters()
    {
        return $this->characters->toArray();
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address1
     *
     * @param string $address1
     * @return Address
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * @param AbstractCharacter $character
     */
    public function addCharacter(AbstractCharacter $character)
    {
        $this->addBidirectionalManyToMany($this->characters, $character, 'addAddress');
    }

    /**
     * @param AbstractCharacter $character
     */
    public function removeCharacter(AbstractCharacter $character)
    {
        $this->removeBidirectionalManyToMany($this->characters, $character, 'removeAddress');
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return AbstractCharacter
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param AbstractCharacter $owner
     */
    public function setOwner(AbstractCharacter $owner)
    {
        $this->setBidirectionalManyToOne('owner', $owner, 'addOwnedAddress');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAddress1() . ', ' . $this->getZip() . ' ' . $this->getCity();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        // Logic comes here :)
        // Set creator as owner of address
    }
}
