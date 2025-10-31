<?php

namespace UserRankingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\LockServiceBundle\Model\LockEntity;
use UserRankingBundle\Enum\RefreshFrequency;
use UserRankingBundle\Repository\UserRankingListRepository;

#[ORM\Table(name: 'user_ranking_list', options: ['comment' => '排行榜管理'])]
#[ORM\Entity(repositoryClass: UserRankingListRepository::class)]
class UserRankingList implements \Stringable, LockEntity
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '标题'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private ?string $title = null;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '副标题'])]
    #[Assert\Length(max: 100)]
    private ?string $subtitle = null;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '颜色'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $color = '';

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'LOGO地址'])]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $logoUrl = null;

    #[IndexColumn]
    #[ORM\Column(name: 'start_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '开始时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeInterface $startTime = null;

    #[IndexColumn]
    #[ORM\Column(name: 'end_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '结束时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeInterface $endTime = null;

    /** @var Collection<int, UserRankingItem> */
    #[ORM\OneToMany(mappedBy: 'list', targetEntity: UserRankingItem::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $items;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '计算SQL'])]
    #[Assert\Length(max: 65535)]
    private ?string $scoreSql = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '总名次'])]
    #[Assert\PositiveOrZero]
    private ?int $count = null;

    /** @var Collection<int, UserRankingPosition> */
    #[ORM\ManyToMany(targetEntity: UserRankingPosition::class, inversedBy: 'lists', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'user_ranking_list_position')]
    private Collection $positions;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(name: 'refresh_frequency', type: Types::STRING, nullable: true, enumType: RefreshFrequency::class, options: ['comment' => '更新频率'])]
    #[Assert\Choice(callback: [RefreshFrequency::class, 'cases'])]
    private ?RefreshFrequency $refreshFrequency = RefreshFrequency::EVERY_MINUTE;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(name: 'refresh_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '最后刷新时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $refreshTime = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => true, 'comment' => '是否有效'])]
    #[Assert\NotNull]
    private ?bool $valid = true;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->positions = new ArrayCollection();
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

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }

    /**
     * @return Collection<int, UserRankingItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(UserRankingItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setList($this);
        }
    }

    public function removeItem(UserRankingItem $item): void
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getList() === $this) {
                $item->setList(null);
            }
        }
    }

    public function getScoreSql(): ?string
    {
        return $this->scoreSql;
    }

    public function setScoreSql(?string $scoreSql): void
    {
        $this->scoreSql = $scoreSql;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): void
    {
        $this->count = $count;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return Collection<int, UserRankingPosition>
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    public function addPosition(UserRankingPosition $position): void
    {
        if (!$this->positions->contains($position)) {
            $this->positions->add($position);
        }
    }

    public function removePosition(UserRankingPosition $position): void
    {
        $this->positions->removeElement($position);
    }

    public function retrieveLockResource(): string
    {
        return 'user_ranking_list_' . $this->getId();
    }

    public function getRefreshFrequency(): ?RefreshFrequency
    {
        return $this->refreshFrequency;
    }

    public function setRefreshFrequency(?RefreshFrequency $refreshFrequency): void
    {
        $this->refreshFrequency = $refreshFrequency;
    }

    public function getRefreshTime(): ?\DateTimeImmutable
    {
        return $this->refreshTime;
    }

    public function setRefreshTime(?\DateTimeImmutable $refreshTime): void
    {
        $this->refreshTime = $refreshTime;
    }

    public function updateRefreshTime(): self
    {
        $this->refreshTime = new \DateTimeImmutable();

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function isInValidPeriod(\DateTimeInterface $now): bool
    {
        // 如果没有设置时间范围，则认为一直有效
        if (null === $this->startTime && null === $this->endTime) {
            return true;
        }

        // 如果只设置了开始时间，检查是否已经开始
        if (null !== $this->startTime && null === $this->endTime) {
            return $now >= $this->startTime;
        }

        // 如果只设置了结束时间，检查是否已经结束
        if (null === $this->startTime && null !== $this->endTime) {
            return $now <= $this->endTime;
        }

        // 如果都设置了，检查是否在时间范围内
        return $now >= $this->startTime && $now <= $this->endTime;
    }
}
