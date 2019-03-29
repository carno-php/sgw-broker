<?php
/**
 * Consul services lister
 * User: moyo
 * Date: 2019-03-12
 * Time: 14:19
 */

namespace Carno\Gateway\Broker\Consul;

use Carno\Consul\APIs\AbstractWatcher;
use Carno\Consul\Chips\SVersions;
use Carno\Consul\Types\Service;
use Carno\HTTP\Standard\Response;
use Carno\Promise\Promised;

class CatalogServicesLister extends AbstractWatcher
{
    use SVersions;

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $uri = '/catalog/services';

    /**
     * @return Promised|Service[]
     */
    public function result()
    {
        return $this->perform($this->getCanceller())->then(function (Response $response) {
            $found = $response->getStatusCode() === 200
                ? $this->decodeResponse((string)$response->getBody())
                : []
            ;

            $services = [];
            foreach ($found as $service => $tags) {
                if (!in_array($service, ['consul'])) {
                    $services[] = new Service($service);
                }
            }

            $this->assignVIndex($this, $response);
            $this->setVIndex($this->getVersion());

            return $services;
        });
    }
}
