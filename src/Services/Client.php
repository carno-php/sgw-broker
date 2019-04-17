<?php
/**
 * Services client manager
 * User: moyo
 * Date: 2019-03-12
 * Time: 15:21
 */

namespace Carno\Gateway\Broker\Services;

use Carno\Consul\Types\Service;
use function Carno\Coroutine\ctx;
use Carno\RPC\Client as RPClient;
use Carno\RPC\Contracts\Client\Cluster;
use Carno\RPC\Protocol\Request;
use Carno\RPC\Protocol\Response;
use Closure;

class Client
{
    /**
     * @var Cluster
     */
    private $cluster = null;

    /**
     * @var Closure
     */
    private $invoker = null;

    /**
     * @var array
     */
    private $instances = [];

    /**
     * Client constructor.
     * @param Cluster $cluster
     */
    public function __construct(Cluster $cluster)
    {
        $this->cluster = $cluster;
        $this->invoker = RPClient::layers()->handler();
    }

    /**
     * @param Service ...$services
     */
    public function reconfigure(Service ...$services) : void
    {
        $local = array_keys($this->instances);

        foreach ($services as $service) {
            if (isset($this->instances[$named = $service->name()])) {
                unset($local[array_search($named, $local)]);
                continue;
            } else {
                $this->cluster->joining($named);
                $this->instances[$named] = true;
            }
        }

        foreach ($local as $named) {
            $this->cluster->leaving($named);
            unset($this->instances[$named]);
        }
    }

    /**
     * @param string $server
     * @return bool
     */
    public function provided(string $server) : bool
    {
        return isset($this->instances[$server]);
    }

    /**
     * @param string $server
     * @param string $service
     * @param string $rpc
     * @param bool $json
     * @param string $payload
     * @return string
     */
    public function invoking(string $server, string $service, string $rpc, bool $json, string $payload)
    {
        $rpc =
            (new Request($server, $service, $rpc))
                ->setJsonc($json)
                ->setPayload($payload)
        ;

        /**
         * @var Response $resp
         */
        $resp = yield ($this->invoker)($rpc, clone yield ctx());

        return $resp->getPayload();
    }
}
