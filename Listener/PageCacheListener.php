<?php

/**
 * Listener
 *
 * @author Alexis Smadja <alexis.smadja@bigyouth.fr>
 */

namespace Bigyouth\BigyouthPageCacheBundle\Listener;

use Bigyouth\BigyouthPageCacheBundle\Services\PageCacheService;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;


/**
 * Class PageCacheListener
 */
class PageCacheListener implements EventSubscriberInterface
{
    /**
     * @var  PageCacheService $cacheService
     */
    protected $cacheService;

    /**
     * @var  TokenStorage $tokenStorage
     */
    protected $tokenStorage;

    /**
     * PageCacheListener constructor.
     * @param $cacheService
     * @param TokenStorage $tokenStorage
     */
    public function __construct($cacheService, TokenStorage $tokenStorage)
    {
        $this->cacheService = $cacheService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param GetResponseEvent $event
     * @return Response
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if($this->cacheService->isEnabled() && $event->getRequestType() == 1)
        {
            if(!$this->cacheService->isExclude($request) && (!$this->tokenStorage->getToken() || $this->tokenStorage->getToken()->getUser() == 'anon.'))
            {
                /** @var TagAwareAdapter $cache */
                $cache = $this->cacheService->getAdapter();

                if($request->getMethod() == 'GET')
                {
                    $cacheKey = $this->cacheService->getCacheKey($request);

                    $data = $cache->getItem($cacheKey);

                    if ($data->isHit()) {
                        $event->setResponse(new Response($data->get()));
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 1))
        );
    }
}
