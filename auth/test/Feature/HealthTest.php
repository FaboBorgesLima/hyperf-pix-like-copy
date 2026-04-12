<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Tests\Feature;

use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class HealthTest extends HttpTestCase
{
    public function testHealth(): void
    {
        $result = $this->client->get('/health');

        $this->assertIsArray($result);
    }
}
