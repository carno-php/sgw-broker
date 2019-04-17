<?php
/**
 * Services client test
 * User: moyo
 * Date: 2019-04-16
 * Time: 17:18
 */

namespace Carno\Gateway\Broker\Tests\Services;

use Carno\Cluster\Classify\Selector;
use Carno\Cluster\Resources;
use Carno\Consul\Types\Service;
use Carno\Gateway\Broker\Services\Client;
use Carno\HRPC\Client\Clustered;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testReconfigure()
    {
        $cluster = new Clustered($resources = new Resources($classify = new Selector));
        $client = new Client($cluster);

        $server = 'ns.g.s';

        $this->assertFalse($client->provided($server));

        $client->reconfigure(new Service($server));
        $this->assertTrue($client->provided($server));

        $client->reconfigure();
        $this->assertFalse($client->provided($server));
    }
}
