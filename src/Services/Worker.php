<?php
/**
 * Component of discovery worker
 * User: moyo
 * Date: 2019-03-12
 * Time: 14:15
 */

namespace Carno\Gateway\Broker\Services;

use Carno\Channel\Channel;
use Carno\Channel\Worker as Observer;
use Carno\Console\Component;
use Carno\Console\Contracts\Application;
use Carno\Console\Contracts\Bootable;
use Carno\Consul\Types\Agent;
use Carno\Container\DI;

class Worker extends Component implements Bootable
{
    /**
     * @param Application $app
     */
    public function starting(Application $app) : void
    {
        /**
         * @var Client $clients
         */

        DI::set(Client::class, $clients = DI::object(Client::class));

        $app->starting()->add(static function () use ($clients) {
            (new Discovery(DI::get(Agent::class)))
                ->watching($notify = new Channel)
            ;

            new Observer($notify, static function (array $services) use ($clients) {
                $clients->reconfigure(...$services);
            });
        });
    }
}
