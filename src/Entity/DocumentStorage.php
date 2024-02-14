<?php

namespace App\Entity;

use App\Repository\DocumentStorageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentStorageRepository::class)
 */
class DocumentStorage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=DocumentFile::class, mappedBy="documentStorage")
     */
    private $files;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $public = false;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, DocumentFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(DocumentFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setDocumentStorage($this);
        }

        return $this;
    }

    public function removeFile(DocumentFile $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getDocumentStorage() === $this) {
                $file->setDocumentStorage(null);
            }
        }

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $isPublic): self
    {
        $this->public = $isPublic;

        return $this;
    }
}
