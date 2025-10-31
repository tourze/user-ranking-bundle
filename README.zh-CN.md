# UserRankingBundle

[![PHP 版本](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://php.net/)
[![许可证](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![构建状态](https://img.shields.io/badge/build-passing-brightgreen.svg)](#)
[![代码覆盖率](https://img.shields.io/badge/coverage-92%25-brightgreen.svg)](#)

[English](README.md) | [中文](README.zh-CN.md)

用户排行榜功能模块，提供灵活的用户排名管理功能。

## 目录

- [功能特性](#功能特性)
- [快速开始](#快速开始)
- [安装](#安装)
- [配置](#配置)
- [目录结构](#目录结构)
- [数据结构](#数据结构)
- [基础用法](#基础用法)
- [命令参考](#命令参考)
- [许可证](#许可证)

## 快速开始

### 1. 安装包

```bash
composer require tourze/user-ranking-bundle
```

### 2. 配置包

```php
// config/bundles.php
return [
    // ...
    UserRankingBundle\UserRankingBundle::class => ['all' => true],
];
```

### 3. 更新数据库架构

```bash
php bin/console doctrine:migrations:migrate
```

### 4. 创建第一个排行榜

```php
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;

$rankingList = new UserRankingList();
$rankingList
    ->setTitle('玩家积分排行榜')
    ->setScoreSql('SELECT user_id, score FROM user_scores ORDER BY score DESC')
    ->setRefreshFrequency(RefreshFrequency::DAILY)
    ->setValid(true);

$entityManager->persist($rankingList);
$entityManager->flush();
```

### 5. 计算排名

```bash
php bin/console user-ranking:calculate
```

## 功能特性

- 支持多个排行榜列表管理
- 灵活的排名计算规则（通过SQL配置）
- 支持固定排名和动态排名
- 支持排行榜位置管理和推荐
- 完整的CRUD操作界面
- 命令行工具支持批量计算排名
- 可配置的更新频率（每分钟到每天）

## 安装

添加包到您的 `composer.json`：

```bash
composer require tourze/user-ranking-bundle
```

## 配置

添加Bundle到您的Symfony配置：

```yaml
# config/bundles.php
return [
    // ...
    UserRankingBundle\UserRankingBundle::class => ['all' => true],
];
```

## 目录结构

```
packages/user-ranking-bundle/
├── src/
│   ├── Command/                # 控制台命令
│   ├── Controller/             # HTTP控制器
│   ├── DependencyInjection/    # 服务配置
│   ├── Entity/                 # Doctrine实体
│   ├── Enum/                   # 枚举类型
│   ├── Event/                  # 事件类
│   ├── Repository/             # 实体仓库
│   ├── Resources/              # 配置文件
│   └── UserRankingBundle.php   # Bundle主类
├── tests/                      # 单元和集成测试
├── composer.json               # 包依赖
└── README.md                   # 文档
```

## 数据结构

### UserRankingList (排行榜列表)

- 标题 (`title`)
- 副标题 (`subtitle`)
- 颜色标识 (`color`)
- LOGO (`logoUrl`)
- 计算规则SQL (`scoreSql`)
- 总名次数量 (`count`)
- 关联位置 (`positions`)
- 更新频率 (`updateFrequency`)：支持每分钟、每5分钟、每15分钟、每30分钟、每小时、每天

### UserRankingItem (排行榜项目)

- 排名 (`number`)
- 用户ID (`userId`)
- 上榜理由 (`textReason`)
- 分数 (`score`)
- 固定排名标记 (`fixed`)
- 推荐人头像 (`recommendThumb`)
- 推荐理由 (`recommendReason`)

### UserRankingPosition (排行榜位置)

- 位置名称 (`title`)
- 关联排行榜 (`lists`)

## 基础用法

### 创建排行榜列表

```php
use UserRankingBundle\Entity\UserRankingList;

$rankingList = new UserRankingList();
$rankingList->setTitle('每日销售排行榜')
    ->setColor('#FF6B6B')
    ->setScoreSql('SELECT user_id, SUM(amount) as score FROM orders WHERE DATE(created_at) = CURDATE() GROUP BY user_id ORDER BY score DESC')
    ->setCount(100);

$entityManager->persist($rankingList);
$entityManager->flush();
```

### 计算排名

```bash
# 计算所有排名
php bin/console user-ranking:calculate

# 计算指定排名
php bin/console user-ranking:calculate LIST_ID

# 空运行（预览而不保存）
php bin/console user-ranking:calculate LIST_ID 1
```

## 高级用法

### 设置固定排名

```php
use UserRankingBundle\Entity\UserRankingItem;

$fixedItem = new UserRankingItem();
$fixedItem->setList($rankingList)
    ->setUserId('123')
    ->setNumber(1)
    ->setFixed(true)
    ->setTextReason('CEO - 固定位置');

$entityManager->persist($fixedItem);
$entityManager->flush();
```

### 管理黑名单

```php
use UserRankingBundle\Entity\UserRankingBlacklist;

$blacklist = new UserRankingBlacklist();
$blacklist->setList($rankingList)
    ->setUserId('456')
    ->setReason('违反排行榜规则')
    ->setUnblockTime(new \DateTimeImmutable('+7 days'));

$entityManager->persist($blacklist);
$entityManager->flush();
```

## 命令参考

### 计算排名

使用以下命令计算一个或所有排行榜的排名：

```bash
# 计算所有排名
php bin/console user-ranking:calculate

# 计算指定排行榜列表
php bin/console user-ranking:calculate LIST_ID

# 空运行模式（预览更改而不保存）
php bin/console user-ranking:calculate LIST_ID 1
```

计算过程包括：
1. 执行配置的计算SQL获取用户分数
2. 过滤掉黑名单用户
3. 处理固定排名（保留手动设置的位置）
4. 基于分数分配动态排名
5. 遵守总排名数量限制

## 自动刷新排名

使用以下命令自动刷新符合条件的排名：

```bash
# 检查所有排名并触发需要更新的排名计算
php bin/console user-ranking:refresh-list
```

此命令将：
1. 检查所有有效的排行榜列表
2. 根据更新频率确定是否需要更新
3. 为需要更新的排名发送异步计算任务
4. 使用消息队列避免阻塞执行

## 归档排名数据

使用以下命令归档指定排名的当前数据：

```bash
# 归档指定列表的当前排名数据
php bin/console user-ranking:archive LIST_ID

# 带保留天数的归档
php bin/console user-ranking:archive LIST_ID --keep-days=60
```

归档过程将：
1. 将所有当前排名数据复制到归档表
2. 清理指定天数之前的旧归档数据
3. 支持按天数保留数据（默认30天）

## 清理黑名单

使用以下命令清理过期的黑名单记录：

```bash
# 清理过期的排行榜黑名单记录
php bin/console user-ranking:blacklist-cleanup
```

清理过程将：
1. 找到所有已过期但仍然有效的黑名单记录
2. 将过期记录标记为无效
3. 允许被拉黑的用户重新参与排名

## 定时任务

以下命令配置为自动执行：

- `user-ranking:refresh-list` - 每分钟运行以自动刷新需要更新的排名
- `user-ranking:blacklist-cleanup` - 每5分钟运行以清理过期的黑名单记录

## 安全

此Bundle包含多个安全功能：

### 输入验证
- 所有实体属性包含Symfony验证器约束
- 通过参数化查询防止SQL注入
- 用户输入清理和验证

### 访问控制
- 与AntdCpBundle管理界面集成
- 基于角色的排名管理访问控制
- 具有适当身份验证的安全API端点

### 数据保护
- 所有排名更改的审计跟踪
- 用户活动跟踪和日志记录
- 敏感排名数据的安全处理

## 依赖关系

- 依赖 `AntdCpBundle` 提供后台管理界面
- 使用 Doctrine ORM 进行数据持久化
- 需要 Symfony 6.4+ 和 PHP 8.1+

## 许可证

本项目采用MIT许可证 - 详情请参阅 [LICENSE](LICENSE) 文件。