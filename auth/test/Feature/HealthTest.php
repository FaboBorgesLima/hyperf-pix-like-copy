<?php

namespace Tests\Feature;


use HyperfTest\HttpTestCase;

class HealthTest extends HttpTestCase
{
    public function testHealth(): void
    {
        $result = $this->client->get('/health');

        $this->assertIsArray($result);
    }
};
