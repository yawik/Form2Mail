<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
abstract class AbstractApiResponseController extends AbstractActionController
{

    protected function createSuccessModel(string $message, ?array $extras = null)
    {
        $result = [
            'success' => true,
            'message' => $message,
        ];

        if ($extras) {
            $result['extras'] = $extras;
        }

        return new JsonModel($result);
    }

    protected function createErrorModel(string $message, $code = null, ?array $extras = null)
    {
        if (is_array($code)) {
            $extras = $code;
            $code = null;
        }

        $this->getResponse()->setStatusCode($code ?? Response::STATUS_CODE_500);

        $result = [
            'success' => false,
            'message' => $message,
        ];

        if ($extras) {
            $result['extras'] = $extras;
        }

        return new JsonModel($result);
    }
}
