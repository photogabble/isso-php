<?php
namespace App\Http\Controllers;

use Adbar\Dot;
use Doctrine\ORM\EntityManagerInterface;
use Photogabble\Tuppence\App;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class InfoController
 *
 * Port of isso python isso.views.__init__.Info class
 * @see https://github.com/posativ/isso/blob/master/isso/views/__init__.py#L51
 */
class InfoController extends Controller
{

    /**
     * Is moderation enabled?
     *
     * @var bool
     */
    private $moderation;

    public function __construct(EntityManagerInterface $entityManager, App $app)
    {
        parent::__construct($entityManager, $app);

        /** @var Dot $config */
        $config = $app->getContainer()->get('config');
        $this->moderation = $config->get('moderation.enabled');
    }

    /**
     * @example output
     * {
     *   "origin": "https://posativ.org",
     *   "host": "https://posativ.org/isso/api",
     *   "version": "0.10.6",
     *   "moderation": false
     * }
     *
     * @see https://github.com/posativ/isso/blob/master/isso/views/__init__.py#L51-L66
     * @param ServerRequestInterface $request
     * @param array $args
     * @return JsonResponse
     */
    public function show(ServerRequestInterface $request, array $args = [])
    {
        return new JsonResponse([
            'origin' => (string) $request->getUri()->withPath(''),
            'host' => $request->getUri()->getHost(),
            'version' => '1.0',
            'moderation' => (bool) $this->moderation,
        ]);
    }
}