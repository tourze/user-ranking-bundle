<?php

declare(strict_types=1);

namespace UserRankingBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use UserRankingBundle\UserRankingBundle;

/**
 * @internal
 */
#[CoversClass(UserRankingBundle::class)]
#[RunTestsInSeparateProcesses]
final class UserRankingBundleTest extends AbstractBundleTestCase
{
}
