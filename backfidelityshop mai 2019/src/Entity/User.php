<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, message="Un compte existe déjà avec ce nom d'utilisateur")
 * @UniqueEntity(fields={"email"}, message="Un compte existe déjà avec cette adresse email")
 * @UniqueEntity(fields={"tel"}, message="Un compte existe déjà avec ce numero")
 */

class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $apiToken;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", unique=true, length=30, nullable=true)
     */
    private $tel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $zip;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Shop", mappedBy="user_id")
     */
    private $shops;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Transactions", mappedBy="user_id")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Licence", mappedBy="vendor")
     */
    private $licences;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Shop", mappedBy="customers")
     */
    private $MyShops;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     */
    private $created;

    /**
     * @ORM\Column(type="string", length=5, nullable=true,
     *     options={"default": "be"})
     */

    private $countrycode;

    /**
     * @ORM\Column(type="boolean", nullable=true,
     *     options={"default": 1} )
     */
    private $etat;


    public function __construct()
    {
        $this->shops = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->my_shops = new ArrayCollection();
        $this->licences = new ArrayCollection();
        $this->MyShops = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id):self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return Collection|Shop[]
     */
    public function getShops(): Collection
    {
        return $this->shops;
    }

    public function addShop(Shop $shop): self
    {
        if (!$this->shops->contains($shop)) {
            $this->shops[] = $shop;
            $shop->setUserId($this);
        }

        return $this;
    }

    public function removeShop(Shop $shop): self
    {
        if ($this->shops->contains($shop)) {
            $this->shops->removeElement($shop);
            // set the owning side to null (unless already changed)
            if ($shop->getUserId() === $this) {
                $shop->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transactions[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transactions $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->addUserId($this);
        }

        return $this;
    }

    public function removeTransaction(Transactions $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            $transaction->removeUserId($this);
        }

        return $this;
    }

    /**
     * @return Collection|Licence[]
     */
    public function getLicences(): Collection
    {
        return $this->licences;
    }

    public function addLicence(Licence $licence): self
    {
        if (!$this->licences->contains($licence)) {
            $this->licences[] = $licence;
            $licence->setVendor($this);
        }

        return $this;
    }

    public function removeLicence(Licence $licence): self
    {
        if ($this->licences->contains($licence)) {
            $this->licences->removeElement($licence);
            // set the owning side to null (unless already changed)
            if ($licence->getVendor() === $this) {
                $licence->setVendor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Shop[]
     */
    public function getMyShops(): Collection
    {
        return $this->MyShops;
    }

    public function addMyShops(Shop $myShop): self
    {
        if (!$this->MyShops->contains($myShop)) {
            $this->MyShops[] = $myShop;
            $myShop->addCustomer($this);
        }

        return $this;
    }

    public function removeMyShops(Shop $myShop): self
    {
        if ($this->MyShops->contains($myShop)) {
            $this->MyShops->removeElement($myShop);
            $myShop->removeCustomer($this);
        }

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCountrycode():?string
    {
        return $this->countrycode;

    }

    public function setCountrycode(?string $countrycode): self
    {
        $this->countrycode = $countrycode;

        return $this;
    }

    public function getEtat()
    {
        return $this->etat;
    }

    public function setEtat($etat):self
    {
        $this->etat = $etat;
        return $this;
    }
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
    }
    public function getApiToken()
    {
        return $this->apiToken;
    }

}
