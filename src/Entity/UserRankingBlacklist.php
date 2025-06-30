<?php

namespace UserRankingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserRankingBundle\Repository\UserRankingBlacklistRepository;

#[ORM\Table(name: 'user_ranking_blacklist', options: ['comment' => '排行榜黑名单'])]
#[ORM\Entity(repositoryClass: UserRankingBlacklistRepository::class)]
#[ORM\UniqueConstraint(name: 'user_ranking_blacklist_uniq_1', columns: ['list_id', 'user_id'])]
class UserRankingBlacklist implements Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => true, 'comment' => '是否有效'])]
    private ?bool $valid = true;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserRankingList $list = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(name: 'user_id', type: Types::STRING, length: 64, options: ['comment' => '用户ID'])]
    private ?string $userId = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '拉黑原因'])]
    private ?string $reason = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '解封时间'])]
    private ?\DateTimeImmutable $unblockTime = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeImmutable $expireTime = null;

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
        return $this->unblockTime === null || $now < $this->unblockTime;
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

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
