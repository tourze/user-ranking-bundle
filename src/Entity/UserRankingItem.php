<?php

namespace UserRankingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
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

    #[Groups(['restful_read'])]
    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserRankingList $list = null;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(options: ['comment' => '排名'])]
    private ?int $number = null;

    /**
     * @var string|null 因为考虑兼容旧系统，所以暂时改成存ID没外键
     */
    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'USER ID'])]
    private ?string $userId = null;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(length: 500, nullable: true, options: ['comment' => '上榜理由'])]
    private ?string $textReason = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '分数'])]
    private ?int $score = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '固定排名'])]
    private ?bool $fixed = false;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '推荐人头像'])]
    private ?string $recommendThumb = null;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '推荐理由'])]
    private ?string $recommendReason = null;

    public function __toString(): string
    {
        return "{$this->getList()?->getTitle()} - {$this->getNumber()}";
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

    public function getList(): ?UserRankingList
    {
        return $this->list;
    }

    public function setList(?UserRankingList $list): self
    {
        $this->list = $list;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getTextReason(): ?string
    {
        return $this->textReason;
    }

    public function setTextReason(?string $textReason): self
    {
        $this->textReason = $textReason;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function isFixed(): ?bool
    {
        return $this->fixed;
    }

    public function setFixed(?bool $fixed): self
    {
        $this->fixed = $fixed;

        return $this;
    }

    public function getRecommendThumb(): ?string
    {
        return $this->recommendThumb;
    }

    public function setRecommendThumb(?string $recommendThumb): self
    {
        $this->recommendThumb = $recommendThumb;

        return $this;
    }

    public function getRecommendReason(): ?string
    {
        return $this->recommendReason;
    }

    public function setRecommendReason(?string $recommendReason): self
    {
        $this->recommendReason = $recommendReason;

        return $this;
    }
}
