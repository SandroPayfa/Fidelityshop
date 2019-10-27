<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShopRepository")
 */
class Shop
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string")
    * @Assert\File(mimeTypes={ "image/png", "image/jpeg" })
    */

    private $image;

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
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
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $pays;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $horaires;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tel;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebook;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagram;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $twitter;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $linkdin;

    public $points;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="shops")
     */
    private $user_id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transactions", mappedBy="shop")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Licence", mappedBy="shop")
     */
    private $licences;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="MyShops")
     */
    private $customers;


    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $spend_rate = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $reward_rate = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $threshold = 10;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $reduction_montant = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $reduction_points = 0;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->licences = new ArrayCollection();
        $this->customers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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


    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getHoraires(): ?string
    {
        return $this->horaires;
    }

    public function setHoraires(?string $horaires): self
    {
        $this->horaires = $horaires;

        return $this;
    }
    /******************************************************/

    public function getOJ1(): ?string
    {
        $arr= $this->horaires;
//        return $arr;
        $soArray = json_decode($arr, true);
        return $soArray[0]['o_h'];
//        return 'lundi';
    }
    public function setOJ1(?string $horaires): self
    {
        return $this;
    }
    public function setFJ1(?string $horaires): self
    {
        return $this;
    }
    public function setOJ2(?string $horaires): self
    {
        return $this;
    }
    public function setFJ2(?string $horaires): self
    {
        return $this;
    }
    public function setOJ3(?string $horaires): self
    {
        return $this;
    }
    public function setFJ3(?string $horaires): self
    {
        return $this;
    }
    public function setOJ4(?string $horaires): self
    {
        return $this;
    }
    public function setFJ4(?string $horaires): self
    {
        return $this;
    }
    public function setOJ5(?string $horaires): self
    {
        return $this;
    }
    public function setFJ5(?string $horaires): self
    {
        return $this;
    }
    public function setFJ6(?string $horaires): self
    {
        return $this;
    }public function setOJ6(?string $horaires): self
{
    return $this;
}
    public function setFJ7(?string $horaires): self
    {
        return $this;
    }
    public function setOJ7(?string $horaires): self
    {
        return $this;
    }

    public function getOJ2(): ?string
    {
        $arr= $this->horaires;
//        return $arr;
        $soArray = json_decode($arr, true);
        return $soArray[1]['o_h'];
    }
    public function getOJ3(): ?string
    {
        $arr= $this->horaires;
//        return $arr;
        $soArray = json_decode($arr, true);
        return $soArray[2]['o_h'];
    }
    public function getOJ4(): ?string
    {
        $arr= $this->horaires;
//        return $arr;
        $soArray = json_decode($arr, true);
        return $soArray[3]['o_h'];
    }
    public function getOJ5(): ?string
    {
        $arr= $this->horaires;
//        return $arr;
        $soArray = json_decode($arr, true);
        return $soArray[4]['o_h'];
    }
    public function getOJ6(): ?string
    {
        $arr= $this->horaires;
//        return $arr;
        $soArray = json_decode($arr, true);
        return $soArray[5]['o_h'];
    }
    public function getOJ7(): ?string
    {
        $arr= $this->horaires;
//        return $arr;
        $soArray = json_decode($arr, true);
        return $soArray[6]['o_h'];
    }
    public function getFJ1(): ?string
    {
        $arr= $this->horaires;
        $soArray = json_decode($arr, true);
        return $soArray[0]['c_h'];
    }
    public function getFJ2(): ?string
    {
        $arr= $this->horaires;
        $soArray = json_decode($arr, true);
        return $soArray[1]['c_h'];
    }
    public function getFJ3(): ?string
    {
        $arr= $this->horaires;
        $soArray = json_decode($arr, true);
        return $soArray[2]['c_h'];
    }
    public function getFJ4(): ?string
    {
        $arr= $this->horaires;
        $soArray = json_decode($arr, true);
        return $soArray[3]['c_h'];
    }
    public function getFJ5(): ?string
    {
        $arr= $this->horaires;
        $soArray = json_decode($arr, true);
        return $soArray[4]['c_h'];
    }
    public function getFJ6(): ?string
    {
        $arr= $this->horaires;
        $soArray = json_decode($arr, true);
        return $soArray[5]['c_h'];
    }
    public function getFJ7(): ?string
    {
        $arr= $this->horaires;
        $soArray = json_decode($arr, true);
        return $soArray[6]['c_h'];
    }

    /******************************************************/

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    // information du compte magasin 

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getLinkdin(): ?string
    {
        return $this->linkdin;
    }

    public function setLinkdin(?string $linkdin): self
    {
        $this->linkdin = $linkdin;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }





    // -------------------------------------

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

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
            $transaction->setShop($this);
        }

        return $this;
    }

    public function removeTransaction(Transactions $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getShop() === $this) {
                $transaction->setShop(null);
            }
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
            $licence->setShop($this);
        }

        return $this;
    }

    public function removeLicence(Licence $licence): self
    {
        if ($this->licences->contains($licence)) {
            $this->licences->removeElement($licence);
            // set the owning side to null (unless already changed)
            if ($licence->getShop() === $this) {
                $licence->setShop(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(User $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers[] = $customer;
        }

        return $this;
    }

    public function removeCustomer(User $customer): self
    {
        if ($this->customers->contains($customer)) {
            $this->customers->removeElement($customer);
        }

        return $this;
    }

    public function getSpendRate(): ?float
    {
        return $this->spend_rate;
    }

    public function setSpendRate(?float $spend_rate): self
    {
        $this->spend_rate = $spend_rate;

        return $this;
    }

    public function getRewardRate(): ?float
    {
        return $this->reward_rate;
    }

    public function setRewardRate(?float $reward_rate): self
    {
        $this->reward_rate = $reward_rate;

        return $this;
    }

    public function getThreshold(): ?int
    {
        return $this->threshold;
    }

    public function setThreshold(?int $threshold): self
    {
        $this->threshold = $threshold;

        return $this;
    }

    public function getReductionPoints(): ?float
    {
        return $this->reduction_points;
    }

    public function setReductionPoints(?float $reduction_points): self
    {
        $this->reduction_points = $reduction_points;

        return $this;
    }
    public function getReductionMontant(): ?float
    {
        return $this->reduction_montant;
    }

    public function setReductionMontant(?float $reduction_montant): self
    {
        $this->reduction_montant = $reduction_montant;
        return $this;
    }
}
