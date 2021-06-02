<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Laminas\Http\Client;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class ExtractEmailsController extends SendMailController
{

    private $httpClient;

    public function __construct()
    {

    }

    public function setHttpClient(Client $client)
    {
        $client->reset(true);
        $client->setUri('');
        $this->httpClient = $client;
    }

    public function getHttpClient(?string $url = null): Client
    {
        if (!$this->httpClient) {
            $this->setHttpClient(new Client());
        }

        $client = clone $this->httpClient;
        $url && $client->setUri($url);

        return $client;
    }

    public function indexAction()
    {
        $url = $this->params()->fromPost('uri') ?? $this->params()->fromQuery('uri');

        if (!$url) {
            return $this->createErrorModel('Missing job ad URI', Response::STATUS_CODE_400);
        }

        try {
            $response = $this->getHttpClient($url)->send();
        } catch (\Throwable $e) {
            return $this->createErrorModel(
                'Could not fetch job ad content',
                Response::STATUS_CODE_500,
                ['message' => $e->getMessage()]
            );
        }
        if ($response->getStatusCode() !== Response::STATUS_CODE_200) {
            return $this->createErrorModel(
                'External server error',
                Response::STATUS_CODE_502,
                [
                    'uri' => $url,
                    'status' => $response->getStatusCode(),
                    'body' => $response->getBody()
                ]
            );
        }
        $content = $response->getBody();

        preg_match_all("/\b\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\b/", $content, $matches);
        $mails = $matches[0];
        $strtolower = (function_exists('mb_strtolower') ? 'mb_' : '') . 'strtolower';
        $mails = array_map($strtolower, $mails);
        $mails = array_unique($mails);
        return new JsonModel([
            'success' => true,
            'message' => count($mails) . ' emails extracted.',
            'emails' => $mails
        ]);
    }
}
