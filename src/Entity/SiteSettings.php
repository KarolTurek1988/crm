<?php

namespace App\Entity;

use App\Repository\SiteSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteSettingsRepository::class)]
class SiteSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $teacherName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $teacherDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $teacherPhoto = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeacherName(): ?string
    {
        return $this->teacherName;
    }

    public function setTeacherName(string $teacherName): static
    {
        $this->teacherName = $teacherName;

        return $this;
    }

    public function getTeacherDescription(): ?string
    {
        return $this->teacherDescription;
    }

    public function setTeacherDescription(?string $teacherDescription): static
    {
        $this->teacherDescription = $teacherDescription;

        return $this;
    }

    public function getTeacherPhoto(): ?string
    {
        return $this->teacherPhoto;
    }

    public function setTeacherPhoto(?string $teacherPhoto): static
    {
        $this->teacherPhoto = $teacherPhoto;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
