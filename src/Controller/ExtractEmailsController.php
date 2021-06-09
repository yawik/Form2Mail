<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Form2Mail\Controller\Plugin\RegisterJob;
use Form2Mail\Entity\UserMetaData;
use Laminas\Http\Client;
use Laminas\Http\Response;
use Laminas\Uri\Uri;
use Laminas\View\Model\JsonModel;
use ML\JsonLD\JsonLD;

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
        $register = (bool) ($this->params()->fromPost('register') ?? $this->params()->fromQuery('register'));

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
        $urlObj = new Uri($url);
        $jsonLd = false;

        try {
            switch ($urlObj->getHost()) {
                case "jobs.kliniken.de":
                    $mails = $this->extractMailsFromKlinikenDe($content);
                    break;

                case "stellenmarkt.sueddeutsche.de":
                    $mails = $this->extractMailsFromSueddeutscheDe($content);
                    break;

                case "www.yourfirm.de":
                    $mails = $this->extractMailsFromYourfirmDe($content);
                    break;

                default:
                    $mails = $this->extractMailsFromHtml($content);
                    break;
            }
            if ($register && !$jsonLd && count($mails)) {
                $jsonLd = $this->extractJsonLd($content);
            }
        } catch (\Throwable $e) {
            return $this->createErrorModel(
                'Could not extract email addresses',
                Response::STATUS_CODE_500,
                ['message' => $e->getMessage()]
            );
        }
        $extras = [];
        $strtolower = (function_exists('mb_strtolower') ? 'mb_' : '') . 'strtolower';
        $mails = array_map($strtolower, $mails);
        $mails = array_unique($mails);

        if ($register && count($mails)) {
            try {
                $spec = [
                    'user' => [
                        'email' => $mails[0],
                    ],
                    'org' => ['name' => $jsonLd['org'] ?? 'Company_' . uniqid()],
                    'job' => [
                        'uri' => $url,
                        'title' => $jsonLd['title'],
                    ],
                ];
                $job = ($this->plugin(RegisterJob::class))($spec, ['allowMultiple' => true, 'userMetaType' => UserMetaData::TYPE_INVITED]);
                $extras = [
                    'register' => true,
                    'user' => $job->getUser()->getId(),
                    'job' => $job->getId(),
                    'org' => $job->getOrganization()->getId(),
                    'jsonLd' => $jsonLd,
                ];
            } catch (\Throwable $e) {
                return $this->createErrorModel(
                    'Register the user with job and organization failed.',
                    Response::STATUS_CODE_500,
                    ['message' => $e->getMessage()]
                );
            }
        }


        return new JsonModel([
            'success' => true,
            'message' => count($mails) . ' emails extracted.',
            'emails' => $mails,
            'extras' => $extras,
        ]);
    }

    private function fetchHtmlContent($url)
    {
        $response = $this->getHttpClient($url)->send();

        if ($response->getStatusCode() !== Response::STATUS_CODE_200) {
            throw new \DomainException('Fetching html failed from ' . $url);
        }

        return $response->getBody();
    }

    private function extractMailsFromHtml($content, $pattern = null)
    {
        $pattern = $pattern ?: "/\b\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\b/";
        preg_match_all($pattern, $content, $matches);
        return $matches[0];
    }

    private function extractMailsFromKlinikenDe($content)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML($content);

        $iframes = $doc->getElementsByTagName('iframe');
        $iframe = $iframes->item(0);
        $iframeSrc = $iframe->getAttribute('data-src');
        $iframeContent = $this->fetchHtmlContent($iframeSrc);

        return $this->extractMailsFromHtml($iframeContent);
    }

    private function extractMailsFromSueddeutscheDe($content)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML($content);

        $iframes = $doc->getElementsByTagName('iframe');
        foreach ($iframes as $iframe) {
            $src = $iframe->getAttribute('src');
            if (strpos($src, '/jobs') === 0) {
                return $this->extractMailsFromHtml(
                    $this->fetchHtmlContent(
                        "https://stellenmarkt.sueddeutsche.de$src"
                    )
                );
            }
        }
        return [];
    }

    private function extractMailsFromYourfirmDe($content)
    {
        preg_match('~"iframe_url": "([^"]+)"~s', $content, $match);

        if (!isset($match[1])) {
            return [];
        }

        return $this->extractMailsFromHtml($this->fetchHtmlContent($match[1]));
    }

    private function extractJsonLd($content)
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($content);

        $xpath = new \DOMXPath($doc);
        $tags = $xpath->query('//script[@type="application/ld+json"]');
        $rawJsonLd = count($tags) ? $tags->item(0)->nodeValue : null;
        $jsonLd = JsonLD::compact($rawJsonLd, 'http://schema.org');

        $title = $jsonLd->title ?? null;
        $org =
            property_exists($jsonLd, 'hiringOrganization') && property_exists($jsonLd->hiringOrganization, 'name')
            ? $jsonLd->hiringOrganization->name
            : null
        ;

        return compact('title', 'org');
    }
}
