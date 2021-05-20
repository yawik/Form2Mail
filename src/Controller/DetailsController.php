<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Core\Entity\ImageSet;
use Jobs\Repository\Job as JobRepository;
use Jobs\View\Helper\JobUrl;
use Laminas\Http\Response;
use Laminas\View\Helper\ServerUrl;
use Laminas\View\Model\JsonModel;
use Organizations\ImageFileCache\Manager as ImageFileCacheManager;
use Organizations\Repository\Organization as OrganizationRepository;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class DetailsController extends SendMailController
{
    private $jobs;
    private $orgs;
    private $jobLink;
    private $absoluteUrl;
    private $orgLogo;

    public function __construct(
        JobRepository $jobs,
        OrganizationRepository $orgs,
        JobUrl $jobLinkHelper,
        ServerUrl $serverUrl,
        ImageFileCacheManager $organizationLogoCacheManager
    ) {
        $this->jobs = $jobs;
        $this->orgs = $orgs;
        $this->jobLink = $jobLinkHelper;
        $this->absoluteUrl = $serverUrl;
        $this->orgLogo = $organizationLogoCacheManager;
    }

    public function indexAction()
    {
        $jobId = $this->params()->fromQuery('job');
        $orgId = null;

        if (!$jobId) {
            $orgId = $this->params()->fromQuery('org');

            if (!$orgId) {
                return $this->createErrorModel(self::ERROR_NO_REF, Response::STATUS_CODE_400);
            }
        }

        if ($jobId) {
            $job = $this->jobs->findOneBy(['applyId' => $jobId]) ?? $this->jobs->find($jobId);

            if (!$job) {
                return $this->createErrorModel(self::ERROR_NO_ENTITY, Response::STATUS_CODE_400, ['job' => $jobId]);
            }

            $org = $job->getOrganization();
            $logo = $org->getImages()->getOriginal();
            $thumb = $org->getImages()->get(ImageSet::THUMBNAIL, false);

            return new JsonModel([
                'success' => true,
                'payload' => [
                    'title' => $job->getTitle(),
                    'uri' => ($this->jobLink)($job, ['linkOnly' => true, 'absolute' => true]),
                    'organization' => [
                        'name' => $org->getName(),
                        'logo' => $logo ? ($this->absoluteUrl)($this->orgLogo->getUri($logo)) : null,
                        'thumbnail' => $thumb ? ($this->absoluteUrl)($this->orgLogo->getUri($thumb)) : null,
                        'ref' => $org->getId(),
                    ]
                ]
            ]);
        }

        $org = $this->orgs->find($orgId);

        if (!$org) {
            return $this->createErrorModel(self::ERROR_NO_ENTITY, Response::STATUS_CODE_400, ['org' => $orgId]);
        }

        $logo = $org->getImages()->getOriginal();
        $thumb = $org->getImages()->get(ImageSet::THUMBNAIL, false);

        return new JsonModel([
            'success' => true,
            'payload' => [
                'name' => $org->getOrganizationName()->getName(),
                'logo' => $logo ? ($this->absoluteUrl)($this->orgLogo->getUri($logo)) : null,
                'thumbnail' => $thumb ? ($this->absoluteUrl)($this->orgLogo->getUri($thumb)) : null,
            ]
        ]);
    }
}
