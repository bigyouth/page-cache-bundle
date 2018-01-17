
# Big Youth Page Cache Bundle

## What is it ?

This bundle provides a simple page caching solution working as a in-app reverse proxy.

## Requirements
```yml
"php": "^5.3.9|^7.0",
"symfony/cache": "^3.1"
```    
    
## Installation

### Download the bundle

Download composer at https://getcomposer.org/download/

```bash
composer require bigyouth/page-cache-bundle
```

### Register the bundle

Enable the bundle by adding it to the bundles array of the registerBundles method in your project's app/AppKernel.php file :

```php
<?php

# app/AppKernel.php

// ...

class AppKernel extends Kernel
{
        public function registerBundles()
        {
            $bundles = [
                // ...

                new Bigyouth\BigyouthPageCacheBundle\BigyouthPageCacheBundle(),
            ];

            // ...
        }

        // ...
}
```


## Configuration

Here is the default bundle configuration :

```yml   
# app/config/config.yml


bigyouth_page_cache:
    enabled: false
    ttl: 300
    type: "filesystem"
    exclude: []
```
### enabled
```yml  
default : false
```

Set to `true` to enable the bundle.

### ttl
```yml  
default : 300
```

Cache lifetime. This value varies by +5%/-5% to avoid multiple caches to expire at the same time.

### type
```yml  
default: filesystem
```

Two value can be set for this parameter : `filesystem` and `redis`.

If you use filesystem, the cache will be written in the cache folder : `var/cache/by_cache`.

### exclude
```yml
default : empty array
```

This parameter allows you to define url schemes that will be excluded from the page caching.

ex : 
```yml
exclude:
    - "app_dev.php"
    - "back"
    - "login"
    - "logout"
    - "login_check"
```
Every url that contains one of the terms above will not be processed by the **BigyouthPageCacheBundle**.

### redis_host
```yml  
default : localhost
```
Redis host. This parameter is only used when the *type* parameter is set to `redis`.

### redis_port
```yml
default : 6379
```

Redis port. This parameter is only used when the *type* parameter is set to `redis`.


## Usage

### Caching

To use the **BigyouthPageCacheBundle**, your controller must extend the PageCacheController.php class and use the *render* function :

```php
<?php

// ...

class AcmeController extends PageCacheController
{

        // ...
        public function indexAction(Request $request)
        {

            // ...

            return $this->render('FrontBundle:Page:index.html.twig');

        }
}
```

The PageCacheController rewrites the *render* function to manage and render cache data. All of your controller actions that use the *render* function will be cached.

### Cache invalidation

The **BigyouthPageCacheBundle** uses [cache tags](https://symfony.com/blog/new-in-symfony-3-2-tagged-cache) to identify your cache data. The tags are defined by the url scheme of the cached page.

For example, the page behind */products/list/my-product* will be tagged with **products**, **list** and **my-product**.

Therefore you can invalidate a product page by doing the following in your controller :

```php
// ...

$this->get('by.page_cache')->invalidate(["my-product"]);

// ...
```

or if you want to invalidate all product pages :

```php
// ...

$this->get('by.page_cache')->invalidate(["products"]);

// ...
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

