<?php
/**
 * Ingress router
 * User: moyo
 * Date: 2019-03-12
 * Time: 13:58
 */

namespace Carno\Gateway\Broker\Routers;

use Carno\Gateway\Broker\Controllers\Forwarder;
use Carno\Web\Router\Configure;
use Carno\Web\Router\Setup;

class Ingress extends Configure
{
    /**
     * @inject
     * @var Forwarder
     */
    private $forwarder = null;

    /**
     * @param Setup $setup
     */
    protected function setup(Setup $setup) : void
    {
        $setup->post('/{server}/{service}/{rpc}', [$this->forwarder, 'invoke']);
    }
}
