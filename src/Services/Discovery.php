<?php
/**
 * Services discovery for consul
 * User: moyo
 * Date: 2019-03-12
 * Time: 14:36
 */

namespace Carno\Gateway\Broker\Services;

use Carno\Channel\Chan;
use Carno\Consul\Chips\AgentRequired;
use Carno\Consul\Chips\GWatcher;
use function Carno\Coroutine\go;
use Carno\Gateway\Broker\Consul\CatalogServicesLister;

class Discovery
{
    use AgentRequired, GWatcher;

    /**
     * @param Chan $notify
     */
    public function watching(Chan $notify) : void
    {
        $ig = function () {
            return new CatalogServicesLister($this->agent);
        };

        $do = function (CatalogServicesLister $lister) use ($notify) {
            yield $notify->send(yield $lister->result());
        };

        $this->nwProcess($notify->closed(), $ig, $do, 'Catalog services lister interrupted', []);
    }

    /**
     * @param Chan $notify
     */
    public function listing(Chan $notify) : void
    {
        $csl = new CatalogServicesLister($this->agent);

        go(static function () use ($csl, $notify) {
            yield $notify->send(yield $csl->result());
        });
    }
}
