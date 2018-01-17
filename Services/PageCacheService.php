<?php

/**
 * Service
 *
 * @author Alexis Smadja <alexis.smadja@bigyouth.fr>
 */

namespace Bigyouth\BigyouthPageCacheBundle\Services;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PageCacheService
 *
 * @package BigyouthPageCacheBundle\Services
 */
class PageCacheService
{
    /**
     * @var TagAwareAdapter
     */
    protected $cacheAdapter;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var int
     */
    protected $exclude = [];

    /**
     * @var string
     */
    protected $type = 'filesystem';

    /**
     * @var string
     */
    protected $redis_host;

    /**
     * @var string
     */
    protected $redis_port;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * PageCacheService constructor.
     * @param $enabled
     * @param $ttl
     * @param array $exclude
     * @param $type
     * @param $redis_host
     * @param $redis_port
     * @param $cacheDir
     */
    public function __construct($enabled, $ttl, array $exclude, $type, $redis_host, $redis_port, $cacheDir)
    {
        $this->enabled    = $enabled;
        $this->ttl        = $ttl;
        $this->exclude    = $exclude;
        $this->type       = $type;
        $this->redis_host = $redis_host;
        $this->redis_port = $redis_port;
        $this->cacheDir   = $cacheDir;
        $this->setAdapter();
    }

    /**
     *
     */
    public function setAdapter()
    {
        switch ($this->type) {
            case 'redis':
                $redisConnection = RedisAdapter::createConnection('redis://'. $this->redis_host . ':' . $this->redis_port);
                $adapter         = new RedisAdapter(
                    $redisConnection,
                    $namespace = 'bigyouth',
                    $defaultLifetime = $this->ttl
                );
                break;
            case 'filesystem':
            default:
                $adapter = new FilesystemAdapter(
                    $namespace = 'bigyouth',
                    $defaultLifetime = $this->ttl,
                    $directory = $this->cacheDir
                );
                break;
        }

        $this->cacheAdapter = new TagAwareAdapter($adapter);
    }

    /**
     * @param array $tags
     */
    public function invalidate(array $tags)
    {
        $cache = $this->cacheAdapter;

        $cache->invalidateTags($tags);
    }

    /**
     * @param $request
     * @return string
     */
    public function getCacheKey(Request $request)
    {
        if ($request->getPathInfo() == "/") {
            $cacheKey = "root";
        } else {
            $cacheKey = explode('/', substr($request->getPathInfo(), 1));
            $cacheKey = implode(':', $cacheKey);
        }


        $params = [];

        foreach ($request->query->all() as $key => $value) {
            if (is_array($value)) {
                $params[] = $key;
                foreach ($value as $k => $v) {
                    $params[] = $k . ':' . $v;
                }
            } else {
                $params[] = $key . ':' . $value;
            }
        }

        if (sizeof($params) > 0) {
            $cacheKeyParams = implode('::', $params);
            $cacheKey       = $cacheKey . '::' . $cacheKeyParams;
        }

        return 'by_cache_' . md5($cacheKey);
    }

    /**
     *
     */
    public function getAdapter()
    {
        return $this->cacheAdapter;
    }

    /**
     *
     */
    public function getExcludeUrls()
    {
        return $this->exclude;
    }

    /**
     * @param Request $request
     * @return array|int
     */
    public function isExclude(Request $request)
    {
        foreach ($this->exclude as $e) {
            if (strpos($request->getRequestUri(), $e) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param $path
     * @return array
     */
    public function getTags($path)
    {
        if ($path == "/") {
            return ["root"];
        } else {
            return explode('/', substr($path, 1));
        }
    }
}
