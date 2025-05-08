<?php

namespace UserRankingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use UserRankingBundle\Repository\UserRankingArchiveRepository;

#[AsPermission(title: '排行榜历史归档')]
#[Listable]
#[ORM\Table(name: 'user_ranking_archive', options: ['comment' => '用户排行历史归档'])]
#[ORM\UniqueConstraint(name: 'uniq_list_user_archive_time', columns: ['list_id', 'user_id', 'archive_time'])]
#[ORM\Entity(repositoryClass: UserRankingArchiveRepository::class)]
class UserRankingArchive implements \Stringable
{
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
    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    #[Groups(['restful_read'])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private UserRankingList $list;

    #[ListColumn(sorter: true)]
    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(options: ['comment' => '排名'])]
    private int $number;

    #[Groups(['admin_curd', 'restful_read'])]
    #[ListColumn(sorter: true)]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'USER ID'])]
    private string $userId;

    #[ListColumn]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '分数'])]
    private ?int $score = null;

    #[ListColumn]
    #[Groups(['admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '归档时间'])]
    private \DateTimeInterface $archiveTime;

    public function __toString(): string
    {
        if ($this->getId()) {
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
