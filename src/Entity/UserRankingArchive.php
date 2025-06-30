<?php

namespace UserRankingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use UserRankingBundle\Repository\UserRankingArchiveRepository;

#[ORM\Table(name: 'user_ranking_archive', options: ['comment' => '用户排行历史归档'])]
#[ORM\UniqueConstraint(name: 'uniq_list_user_archive_time', columns: ['list_id', 'user_id', 'archive_time'])]
#[ORM\Entity(repositoryClass: UserRankingArchiveRepository::class)]
class UserRankingArchive implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }
    use TimestampableAware;

    #[Groups(groups: ['restful_read'])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private UserRankingList $list;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(options: ['comment' => '排名'])]
    private int $number;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::STRING, options: ['comment' => '用户ID'])]
    private string $userId;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '分数'])]
    private ?int $score = null;

    #[Groups(groups: ['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '归档时间'])]
    private \DateTimeInterface $archiveTime;

    public function __toString(): string
    {
        if ($this->getId() === null) {
            return '';
        }

        return (string) $this->getId();
    }

    public function getList(): UserRankingList
    {
        return $this->list;
    }

    public function setList(UserRankingList $list): self
    {
        $this->list = $list;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getArchiveTime(): \DateTimeInterface
    {
        return $this->archiveTime;
    }

    public function setArchiveTime(\DateTimeInterface $archiveTime): self
    {
        $this->archiveTime = $archiveTime;

        return $this;
    }
}
