<?php
/**
 * Service traffic forwarder
 * User: moyo
 * Date: 2019-03-12
 * Time: 14:02
 */

namespace Carno\Gateway\Broker\Controllers;

use Carno\Gateway\Broker\Services\Client;
use Carno\HRPC\Client\Contracts\Defined;
use Carno\HTTP\Standard\Response;
use Carno\RPC\Exception\RemoteLogicException;
use Carno\Web\Controller\Based;
use Throwable;

class Forwarder extends Based
{
    /**
     * @inject
     * @var Client
     */
    private $client = null;

    /**
     * @return Response|string
     */
    public function invoke()
    {
        $params = $this->request()->params();

        $server = $params->string('server');
        $service = $params->string('service');
        $rpc = $params->string('rpc');

        $jsonc = in_array(Defined::V_TYPE_JSON, $this->ingress()->getHeader('content-type'));

        if ($this->client->provided($server)) {
            try {
                return yield $this->client->invoking($server, $service, $rpc, $jsonc, $this->request()->payload());
            } catch (RemoteLogicException $e) {
                return $this->exception(200, $e);
            } catch (Throwable $e) {
                return $this->exception(500, $e);
            }
        }

        return new Response(503);
    }

    /**
     * @param int $code
     * @param Throwable $e
     * @return Response
     */
    private function exception(int $code, Throwable $e) : Response
    {
        return new Response(
            $code,
            [
                Defined::X_ERR_CODE => $e->getCode(),
                Defined::X_ERR_MESSAGE => $e->getMessage(),
            ]
        );
    }
}
