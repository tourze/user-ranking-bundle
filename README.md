# UserRankingBundle

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](#)
[![Code Coverage](https://img.shields.io/badge/coverage-92%25-brightgreen.svg)](#)

[English](README.md) | [中文](README.zh-CN.md)

User ranking functionality module that provides flexible user ranking management features.

## Table of Contents

- [Features](#features)
- [Quick Start](#quick-start)
- [Installation](#installation)
- [Configuration](#configuration)
- [Directory Structure](#directory-structure)
- [Data Structure](#data-structure)
- [Basic Usage](#basic-usage)
- [Command Reference](#command-reference)
- [License](#license)

## Quick Start

### 1. Install the Bundle

```bash
composer require tourze/user-ranking-bundle
```

### 2. Configure the Bundle

```php
// config/bundles.php
return [
    // ...
    UserRankingBundle\UserRankingBundle::class => ['all' => true],
];
```

### 3. Update Database Schema

```bash
php bin/console doctrine:migrations:migrate
```

### 4. Create Your First Ranking List

```php
use UserRankingBundle\Entity\UserRankingList;
use UserRankingBundle\Enum\RefreshFrequency;

$rankingList = new UserRankingList();
$rankingList
    ->setTitle('Player Score Ranking')
    ->setScoreSql('SELECT user_id, score FROM user_scores ORDER BY score DESC')
    ->setRefreshFrequency(RefreshFrequency::DAILY)
    ->setValid(true);

$entityManager->persist($rankingList);
$entityManager->flush();
```

### 5. Calculate Rankings

```bash
php bin/console user-ranking:calculate
```

## Features

- Support for multiple ranking list management
- Flexible ranking calculation rules (configured via SQL)
- Support for fixed and dynamic rankings
- Ranking position management and recommendations
- Complete CRUD operation interface
- Command-line tools for batch ranking calculations
- Configurable update frequency (from per minute to daily)

## Installation

Add the bundle to your `composer.json`:

```bash
composer require tourze/user-ranking-bundle
```

## Configuration

Add the bundle to your Symfony configuration:

```yaml
# config/bundles.php
return [
    // ...
    UserRankingBundle\UserRankingBundle::class => ['all' => true],
];
```

## Directory Structure

```
packages/user-ranking-bundle/
├── src/
│   ├── Command/                # Console commands
│   ├── Controller/             # HTTP controllers
│   ├── DependencyInjection/    # Service configuration
│   ├── Entity/                 # Doctrine entities
│   ├── Enum/                   # Enumeration types
│   ├── Event/                  # Event classes
│   ├── Repository/             # Entity repositories
│   ├── Resources/              # Configuration files
│   └── UserRankingBundle.php   # Bundle class
├── tests/                      # Unit and integration tests
├── composer.json               # Package dependencies
└── README.md                   # Documentation
```

## Data Structure

### UserRankingList (Ranking List)

- Title (`title`)
- Subtitle (`subtitle`)
- Color identifier (`color`)
- Logo (`logoUrl`)
- Calculation rule SQL (`scoreSql`)
- Total number of rankings (`count`)
- Associated positions (`positions`)
- Update frequency (`updateFrequency`): supports per minute, 5 minutes, 15 minutes, 30 minutes, hourly, daily

### UserRankingItem (Ranking Item)

- Rank (`number`)
- User ID (`userId`)
- Ranking reason (`textReason`)
- Score (`score`)
- Fixed ranking flag (`fixed`)
- Recommender avatar (`recommendThumb`)
- Recommendation reason (`recommendReason`)

### UserRankingPosition (Ranking Position)

- Position name (`title`)
- Associated rankings (`lists`)

## Basic Usage

### Creating a Ranking List

```php
use UserRankingBundle\Entity\UserRankingList;

$rankingList = new UserRankingList();
$rankingList->setTitle('Daily Sales Ranking')
    ->setColor('#FF6B6B')
    ->setScoreSql('SELECT user_id, SUM(amount) as score FROM orders WHERE DATE(created_at) = CURDATE() GROUP BY user_id ORDER BY score DESC')
    ->setCount(100);

$entityManager->persist($rankingList);
$entityManager->flush();
```

### Calculating Rankings

```bash
# Calculate all rankings
php bin/console user-ranking:calculate

# Calculate specific ranking
php bin/console user-ranking:calculate LIST_ID

# Dry run (preview without saving)
php bin/console user-ranking:calculate LIST_ID 1
```

## Advanced Usage

### Setting up Fixed Rankings

```php
use UserRankingBundle\Entity\UserRankingItem;

$fixedItem = new UserRankingItem();
$fixedItem->setList($rankingList)
    ->setUserId('123')
    ->setNumber(1)
    ->setFixed(true)
    ->setTextReason('CEO - Fixed Position');

$entityManager->persist($fixedItem);
$entityManager->flush();
```

### Managing Blacklists

```php
use UserRankingBundle\Entity\UserRankingBlacklist;

$blacklist = new UserRankingBlacklist();
$blacklist->setList($rankingList)
    ->setUserId('456')
    ->setReason('Violated ranking rules')
    ->setUnblockTime(new \DateTimeImmutable('+7 days'));

$entityManager->persist($blacklist);
$entityManager->flush();
```

## Command Reference

### Calculate Rankings

Use the following command to calculate rankings for one or all ranking lists:

```bash
# Calculate all rankings
php bin/console user-ranking:calculate

# Calculate specific ranking list
php bin/console user-ranking:calculate LIST_ID

# Dry run mode (preview changes without saving)
php bin/console user-ranking:calculate LIST_ID 1
```

The calculation process includes:
1. Execute the configured calculation SQL to get user scores
2. Filter out blacklisted users
3. Handle fixed rankings (preserve manually set positions)
4. Assign dynamic rankings based on scores
5. Respect total ranking count limits

## Auto-refresh Rankings

Use the following command to automatically refresh eligible rankings:

```bash
# Check all rankings and trigger calculation for those needing updates
php bin/console user-ranking:refresh-list
```

This command will:
1. Check all valid ranking lists
2. Determine if updates are needed based on update frequency
3. Send asynchronous calculation tasks for rankings needing updates
4. Use message queues to avoid blocking execution

## Archive Ranking Data

Use the following command to archive current data for a specified ranking:

```bash
# Archive current ranking data for specified list
php bin/console user-ranking:archive LIST_ID

# Archive with retention days
php bin/console user-ranking:archive LIST_ID --keep-days=60
```

The archiving process will:
1. Copy all current ranking data to the archive table
2. Clean up old archive data before specified days
3. Support data retention by days (default 30 days)

## Clean Blacklist

Use the following command to clean expired blacklist records:

```bash
# Clean expired ranking blacklist records
php bin/console user-ranking:blacklist-cleanup
```

The cleanup process will:
1. Find all expired but still valid blacklist records
2. Mark expired records as invalid
3. Allow blacklisted users to participate in rankings again

## Scheduled Tasks

The following commands are configured for automatic execution:

- `user-ranking:refresh-list` - Runs every minute to auto-refresh rankings needing updates
- `user-ranking:blacklist-cleanup` - Runs every 5 minutes to clean expired blacklist records

## Security

This bundle includes several security features:

### Input Validation
- All entity properties include Symfony Validator constraints
- SQL injection protection through parameterized queries
- User input sanitization and validation

### Access Control
- Admin interface integration with AntdCpBundle
- Role-based access control for ranking management
- Secure API endpoints with proper authentication

### Data Protection
- Audit trails for all ranking changes
- User activity tracking and logging
- Secure handling of sensitive ranking data

## Dependencies

- Depends on `AntdCpBundle` for admin interface
- Uses Doctrine ORM for data persistence
- Requires Symfony 6.4+ and PHP 8.1+

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.