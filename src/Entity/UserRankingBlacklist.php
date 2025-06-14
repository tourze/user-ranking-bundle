<?php

namespace UserRankingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use UserRankingBundle\Repository\UserRankingBlacklistRepository;

#[AsPermission(title: '排行榜黑名单')]
#[Listable]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Table(name: 'user_ranking_blacklist', options: ['comment' => '排行榜黑名单'])]
#[ORM\Entity(repositoryClass: UserRankingBlacklistRepository::class)]
#[ORM\UniqueConstraint(name: 'user_ranking_blacklist_uniq_1', columns: ['list_id', 'user_id'])]
class UserRankingBlacklist
{
    use TimestampableAware;

    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserRankingList $list = null;

    #[Groups(['admin_curd'])]
    #[ListColumn(title: '用户ID')]
    #[FormField(title: '用户ID')]
    #[ORM\Column(name: 'user_id', type: Types::STRING, length: 64, options: ['comment' => '用户ID'])]
    private ?string $userId = null;

    #[Groups(['admin_curd'])]
    #[ListColumn(title: '拉黑原因')]
    #[FormField(title: '拉黑原因')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '拉黑原因'])]
    private ?string $reason = null;

    #[Groups(['admin_curd'])]
    #[ListColumn(title: '解封时间')]
    #[FormField(title: '解封时间')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '解封时间'])]
    private ?\DateTimeImmutable $unblockTime = null;

    #[Groups(['admin_curd'])]
    #[ListColumn(title: '备注')]
    #[FormField(title: '备注')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $comment = null;

    #[ListColumn(title: '过期时间')]
    #[FormField(title: '过期时间')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeImmutable $expireTime = null;

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

    public function getList(): ?UserRankingList
    {
        return $this->list;
    }

    public function setList(?UserRankingList $list): self
    {
        $this->list = $list;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getUnblockTime(): ?\DateTimeImmutable
    {
        return $this->unblockTime;
    }

    public function setUnblockTime(?\DateTimeImmutable $unblockTime): self
    {
        $this->unblockTime = $unblockTime;

        return $this;
    }

    public function isBlocked(\DateTimeInterface $now): bool
    {
        return !$this->unblockTime || $now < $this->unblockTime;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): self
    {
        $this->expireTime = $expireTime;

        return $this;
    }
}
