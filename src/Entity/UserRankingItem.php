<?php

namespace UserRankingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserRankingBundle\Repository\UserRankingItemRepository;

#[ORM\Table(name: 'user_ranking_item', options: ['comment' => '用户排行'])]
#[ORM\Entity(repositoryClass: UserRankingItemRepository::class)]
#[ORM\UniqueConstraint(name: 'user_ranking_item_uniq_1', columns: ['list_id', 'number'])]
#[ORM\UniqueConstraint(name: 'user_ranking_item_uniq_2', columns: ['list_id', 'user_id'])]
class UserRankingItem implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => true, 'comment' => '是否有效'])]
    #[Assert\NotNull]
    private ?bool $valid = true;

    #[Groups(groups: ['restful_read'])]
    #[ORM\ManyToOne(inversedBy: 'items', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserRankingList $list = null;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(options: ['comment' => '排名'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $number = null;

    /**
     * @var string|null 因为考虑兼容旧系统，所以暂时改成存ID没外键
     */
    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => 'USER ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $userId = null;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(length: 500, nullable: true, options: ['comment' => '上榜理由'])]
    #[Assert\Length(max: 500)]
    private ?string $textReason = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '分数'])]
    #[Assert\PositiveOrZero]
    private ?int $score = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '固定排名'])]
    #[Assert\NotNull]
    private ?bool $fixed = false;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '推荐人头像'])]
    #[Assert\Length(max: 255)]
    private ?string $recommendThumb = null;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '推荐理由'])]
    #[Assert\Length(max: 65535)]
    private ?string $recommendReason = null;

    public function __toString(): string
    {
        return "{$this->getList()?->getTitle()} - {$this->getNumber()}";
    }

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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function getTextReason(): ?string
    {
        return $this->textReason;
    }

    public function setTextReason(?string $textReason): void
    {
        $this->textReason = $textReason;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function isFixed(): ?bool
    {
        return $this->fixed;
    }

    public function setFixed(?bool $fixed): void
    {
        $this->fixed = $fixed;
    }

    public function getRecommendThumb(): ?string
    {
        return $this->recommendThumb;
    }

    public function setRecommendThumb(?string $recommendThumb): void
    {
        $this->recommendThumb = $recommendThumb;
    }

    public function getRecommendReason(): ?string
    {
        return $this->recommendReason;
    }

    public function setRecommendReason(?string $recommendReason): void
    {
        $this->recommendReason = $recommendReason;
    }
}
