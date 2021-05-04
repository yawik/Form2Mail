<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Core\Mail\MailService;
use JsonException;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class SendMailController extends AbstractActionController
{
    const ERROR_NO_POST = 'NO_POST';
    const ERROR_INVALID_JSON = 'INVALID_JSON';

    private static $errors = [
        self::ERROR_NO_POST => 'Must use POST request',
        self::ERROR_INVALID_JSON => 'Invalid json',
    ];

    private $mails;

    public function __construct(MailService $mails)
    {
        $this->mails = $mails;
    }

    public function indexAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->getResponse()->getHeaders()->addHeaderLine('Allow', Request::METHOD_POST);
            return $this->createErrorModel(self::ERROR_NO_POST, Response::STATUS_CODE_405);
        }

        $data = $this->getRequest()->getContent();
        try {
            $json = Json::decode($data, Json::TYPE_ARRAY);
        } catch (\Laminas\Json\Exception\ExceptionInterface $e) {
            return $this->createErrorModel(self::ERROR_INVALID_JSON, Response::STATUS_CODE_400);
        }



        return new JsonModel([
            'success' => true,
            'message' => 'Mail send successfully',
            'payload' => $json
        ]);
    }

    private function createErrorModel(string $type, $code = null)
    {
        $this->getResponse()->setStatusCode($code ?? Response::STATUS_CODE_500);
        return new JsonModel([
            'success' => false,
            'message' => self::$errors[$type] ?? 'An unknown error occured.',
        ]);
    }
}
