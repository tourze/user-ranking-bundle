<?php

namespace UserRankingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserRankingBundle\Repository\UserRankingBlacklistRepository;

#[ORM\Table(name: 'user_ranking_blacklist', options: ['comment' => '排行榜黑名单'])]
#[ORM\Entity(repositoryClass: UserRankingBlacklistRepository::class)]
#[ORM\UniqueConstraint(name: 'user_ranking_blacklist_uniq_1', columns: ['list_id', 'user_id'])]
class UserRankingBlacklist implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => true, 'comment' => '是否有效'])]
    #[Assert\NotNull]
    private ?bool $valid = true;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserRankingList $list = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(name: 'user_id', type: Types::STRING, length: 64, options: ['comment' => '用户ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private ?string $userId = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '拉黑原因'])]
    #[Assert\Length(max: 65535)]
    private ?string $reason = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(name: 'unblock_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '解封时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $unblockTime = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 65535)]
    private ?string $comment = null;

    #[ORM\Column(name: 'expire_time', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $expireTime = null;

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getList(): ?UserRankingList
    {
        return $this->list;
    }

    public function setList(?UserRankingList $list): void
    {
        $this->list = $list;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    public function getUnblockTime(): ?\DateTimeImmutable
    {
        return $this->unblockTime;
    }

    public function setUnblockTime(?\DateTimeImmutable $unblockTime): void
    {
        $this->unblockTime = $unblockTime;
    }

    public function isBlocked(\DateTimeInterface $now): bool
    {
        return null === $this->unblockTime || $now < $this->unblockTime;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): void
    {
        $this->expireTime = $expireTime;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
