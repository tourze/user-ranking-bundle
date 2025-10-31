<?php

namespace UserRankingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserRankingBundle\Repository\UserRankingPositionRepository;

#[ORM\Table(name: 'user_ranking_position', options: ['comment' => '排行榜位置'])]
#[ORM\Entity(repositoryClass: UserRankingPositionRepository::class)]
class UserRankingPosition implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => true, 'comment' => '是否有效'])]
    #[Assert\NotNull]
    private ?bool $valid = true;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 50, unique: true, nullable: true, options: ['comment' => '名称'])]
    #[Assert\Length(max: 50)]
    private ?string $title = null;

    /** @var Collection<int, UserRankingList> */
    #[Ignore]
    #[ORM\ManyToMany(targetEntity: UserRankingList::class, mappedBy: 'positions', fetch: 'EXTRA_LAZY')]
    private Collection $lists;

    public function __construct()
    {
        $this->lists = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return "{$this->getTitle()}";
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return Collection<int, UserRankingList>
     */
    public function getLists(): Collection
    {
        return $this->lists;
    }

    public function addList(UserRankingList $list): void
    {
        if (!$this->lists->contains($list)) {
            $this->lists->add($list);
            $list->addPosition($this);
        }
    }

    public function removeList(UserRankingList $list): void
    {
        if ($this->lists->removeElement($list)) {
            $list->removePosition($this);
        }
    }
}
