<?php

namespace UserRankingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserRankingBundle\Repository\UserRankingPositionRepository;

#[ORM\Table(name: 'user_ranking_position', options: ['comment' => '排行榜位置'])]
#[ORM\Entity(repositoryClass: UserRankingPositionRepository::class)]
class UserRankingPosition implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => true, 'comment' => '是否有效'])]
    private ?bool $valid = true;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 50, unique: true, nullable: true, options: ['comment' => '名称'])]
    private ?string $title = null;

    #[Ignore]
    #[ORM\ManyToMany(targetEntity: UserRankingList::class, mappedBy: 'positions', fetch: 'EXTRA_LAZY')]
    private Collection $lists;

    public function __construct()
    {
        $this->lists = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->getId() === null) {
            return '';
        }

        return "{$this->getTitle()}";
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, UserRankingList>
     */
    public function getLists(): Collection
    {
        return $this->lists;
    }

    public function addList(UserRankingList $list): self
    {
        if (!$this->lists->contains($list)) {
            $this->lists->add($list);
            $list->addPosition($this);
        }

        return $this;
    }

    public function removeList(UserRankingList $list): self
    {
        if ($this->lists->removeElement($list)) {
            $list->removePosition($this);
        }

        return $this;
    }
}
