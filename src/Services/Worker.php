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
use Carno\RPC\Contracts\Client\Cluster;
use Carno\Timer\Timer;

class Worker extends Component implements Bootable
{
    /**
     * interval ms for refresh catalog services
     */
    private const CSL_REFRESH_INV = 55000;

    /**
     * @var array
     */
    protected $dependencies = [Cluster::class];

    /**
     * @param Application $app
     */
    public function starting(Application $app) : void
    {
        /**
         * @var Client $clients
         */

        DI::set(Client::class, $clients = DI::object(Client::class, DI::get(Cluster::class)));

        $timer = new class {
            /**
             * @var string
             */
            private $id = null;

            /**
             * @param string $id
             */
            public function started(string $id) : void
            {
                $this->id = $id;
            }

            /**
             */
            public function stop() : void
            {
                $this->id && Timer::clear($this->id);
            }
        };

        $app->starting()->add(static function () use ($clients, $timer) {
            ($discovery = new Discovery(DI::get(Agent::class)))
                ->watching($notify = new Channel)
            ;

            new Observer($notify, static function (array $services) use ($clients) {
                $clients->reconfigure(...$services);
            });

            $timer->started(Timer::loop(self::CSL_REFRESH_INV, static function () use ($discovery, $notify) {
                $discovery->listing($notify);
            }));
        });

        $app->stopping()->add(static function () use ($timer) {
            $timer->stop();
        });
    }
}
