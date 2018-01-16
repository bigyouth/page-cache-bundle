<?php


/**
 * Controller
 *
 * @author Alexis Smadja <alexis.smadja@bigyouth.fr>
 */

namespace Bigyouth\BigyouthPageCacheBundle\Controller;

use Bigyouth\BigyouthPageCacheBundle\Services\PageCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PageCacheController
 *
 * @package BigyouthPageCacheBundle\Controller
 */
class PageCacheController extends Controller
{
    /**
     * @var  PageCacheService $cacheService
     */
    protected $cacheService;

    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $request = $this->get('request_stack')->getMasterRequest();

        $this->cacheService = $this->get('by.page_cache');

        if ($this->getUser() || $request->getMethod() != 'GET' || !$this->cacheService->isEnabled() || $this->cacheService->isExclude($request)) {
            return parent::render($view, $parameters, $response);
        }

        /** @var TagAwareAdapter $cache */
        $cache = $this->cacheService->getAdapter();

        $cacheKey = $this->cacheService->getCacheKey($request);

        $data = $cache->getItem($cacheKey);

        $view = parent::renderView($view, $parameters);

        $highLimit = $this->cacheService->getTtl() + (0.05 * $this->cacheService->getTtl());
        $lowLimit  = $this->cacheService->getTtl() - (0.05 * $this->cacheService->getTtl());

        $data->expiresAfter(rand($highLimit, $lowLimit));
        $data->set($view);
        $data->tag($this->cacheService->getTags($request->getPathInfo()));
        $cache->save($data);

        return new Response($view);
    }
}
