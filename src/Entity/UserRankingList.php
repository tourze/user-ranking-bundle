<?php

namespace UserRankingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\CurdAction;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\PictureColumn;
use Tourze\EasyAdmin\Attribute\Field\ImagePickerField;
use Tourze\LockServiceBundle\Model\LockEntity;
use UserRankingBundle\Enum\RefreshFrequency;
use UserRankingBundle\Repository\UserRankingListRepository;

#[Listable]
#[ORM\Table(name: 'user_ranking_list', options: ['comment' => '排行榜管理'])]
#[ORM\Entity(repositoryClass: UserRankingListRepository::class)]
class UserRankingList implements \Stringable, LockEntity
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '标题'])]
    private ?string $title = null;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '副标题'])]
    private ?string $subtitle = null;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '颜色'])]
    private ?string $color = '';

    #[ImagePickerField]
    #[PictureColumn]
    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'LOGO地址'])]
    private ?string $logoUrl = null;

    #[IndexColumn]
    #[ORM\Column(name: 'start_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '开始时间'])]
    private ?\DateTimeInterface $startTime = null;

    #[IndexColumn]
    #[ORM\Column(name: 'end_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '结束时间'])]
    private ?\DateTimeInterface $endTime = null;

    #[CurdAction(label: '用户列表')]
    #[ORM\OneToMany(targetEntity: UserRankingItem::class, mappedBy: 'list', orphanRemoval: true)]
    private Collection $items;

    /**
     * 具体每个排行榜商品的计算逻辑，不好去定，我们直接写sql吧
     * 返回每一行，第一个值是商品ID，第二个值是分数
     */
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '计算SQL'])]
    private ?string $scoreSql = null;

    #[Groups(['admin_curd'])]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '总名次'])]
    private ?int $count = null;

    #[ORM\ManyToMany(targetEntity: UserRankingPosition::class, inversedBy: 'lists', fetch: 'EXTRA_LAZY')]
    private Collection $positions;

    #[Groups(['admin_curd'])]
    #[ORM\Column(type: Types::STRING, nullable: true, enumType: RefreshFrequency::class, options: ['comment' => '更新频率'])]
    private ?RefreshFrequency $refreshFrequency = RefreshFrequency::EVERY_MINUTE;

    #[Groups(['admin_curd'])]
    private ?\DateTimeImmutable $refreshTime = null;

    #[TrackColumn]
    private ?bool $valid = false;

    #[CreatedByColumn]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    private ?string $updatedBy = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->positions = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getTitle()}";
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
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

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): self
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    /**
     * @return Collection<int, UserRankingItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(UserRankingItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setList($this);
        }

        return $this;
    }

    public function removeItem(UserRankingItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getList() === $this) {
                $item->setList(null);
            }
        }

        return $this;
    }

    public function getScoreSql(): ?string
    {
        return $this->scoreSql;
    }

    public function setScoreSql(?string $scoreSql): self
    {
        $this->scoreSql = $scoreSql;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): self
    {
        $this->count = $count;

        return $this;
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

    public function addPosition(UserRankingPosition $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions->add($position);
        }

        return $this;
    }

    public function removePosition(UserRankingPosition $position): self
    {
        $this->positions->removeElement($position);

        return $this;
    }

    public function retrieveLockResource(): string
    {
        return 'user_ranking_list_' . $this->getId();
    }

    public function getRefreshFrequency(): ?RefreshFrequency
    {
        return $this->refreshFrequency;
    }

    public function setRefreshFrequency(?RefreshFrequency $refreshFrequency): self
    {
        $this->refreshFrequency = $refreshFrequency;

        return $this;
    }

    public function getRefreshTime(): ?\DateTimeImmutable
    {
        return $this->refreshTime;
    }

    public function setRefreshTime(?\DateTimeImmutable $refreshTime): self
    {
        $this->refreshTime = $refreshTime;

        return $this;
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
        if (!$this->startTime && !$this->endTime) {
            return true;
        }

        // 如果只设置了开始时间，检查是否已经开始
        if ($this->startTime && !$this->endTime) {
            return $now >= $this->startTime;
        }

        // 如果只设置了结束时间，检查是否已经结束
        if (!$this->startTime && $this->endTime) {
            return $now <= $this->endTime;
        }

        // 如果都设置了，检查是否在时间范围内
        return $now >= $this->startTime && $now <= $this->endTime;
    }
}
