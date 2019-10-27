<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LicenceRepository")
 */
class Licence
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="licences")
     */
    private $vendor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $serial;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shop", inversedBy="licences")
     */
    private $shop;

    /**
     * @ORM\Column(type="integer")
     */
    private $terminal_id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $pin;

    /**
     * @ORM\Column(name="periode", type="string", columnDefinition="enum('3', '6', '12')")
     */
    private $periode;

    /**
     * @ORM\Column(name="actived", type="integer")
     */
    private $actived;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activated_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expired_at;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): ?User
    {
        return $this->vendor;
    }

    public function setVendor(?User $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getTerminalId(): ?int
    {
        return $this->terminal_id;
    }

    public function setTerminalId(?int $terminal_id): self
    {
        $this->terminal_id = $terminal_id;

        return $this;
    }
    public function getActived(): ?int
    {
        return $this->actived;
    }

    public function setActived(?int $actived): self
    {
        $this->actived = $actived;

        return $this;
    }

    public function getPin(): ?string
    {
        return $this->pin;
    }

    public function setPin(?string $pin): self
    {
        $this->pin = $pin;

        return $this;
    }

    public function getPeriode(): ?string
    {
        return $this->periode;
    }

    public function setPeriode(?string $periode): self
    {
        $this->periode = $periode;

        return $this;
    }

    public function getExpiredAt()
    {
        return $this->expired_at;
    }

    public function setExpiredAt($expired_at): self
    {
        $this->expired_at = $expired_at;

        return $this;
    }
    public function getActivatedAt()
    {
        return $this->activated_at;
    }

    public function setActivatedAt($activated_at): self
    {
        $this->activated_at = $activated_at;

        return $this;
    }
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }


}
