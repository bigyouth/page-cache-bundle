
# BigyouthPageCacheBundle

## What is it ?

This bundle provides a simple page caching solution working as a in-app reverse proxy.

## Requirements

    "php": "^5.3.9|^7.0",
    "symfony/cache": "^3.1"
    
## Installation

### Download the bundle

Download composer at https://getcomposer.org/download/

    composer require bigyouth/page-cache-bundle 0.1.*

### Register the bundle

Then, enable the bundle by adding it to the bundles array of the registerBundles method in your project's app/AppKernel.php file :

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



## Configuration

Here is the default bundle configuration :

    # app/config/config.yml
   
    bigyouth_page_cache:
	    enabled: false
	    ttl: 300
	    type: "filesystem"
	    exclude: []

### enabled
*default : **false***

Set to **true** to enable the bundle.

### ttl
*default : **300***

Cache lifetime. This value varies by +5%/-5% to avoid multiple caches to expire at the same time.

### type
*default: **filesystem***

Two value can be set for this parameter : **filesystem** and **redis**.

If you use filesystem, the cache will be written in the cache folder : *var/cache/by_cache*.

### exclude
*default : **empty array***

This parameter allows you to define url schemes that will be excluded from the page caching.

ex : 

    exclude:
      - "app_dev.php"
      - "back"
      - "login"
      - "logout"
      - "login_check"

Every url that contains one of the terms above will not be processed by the **BigyouthPageCacheBundle**.

### redis_host
*default : **localhost***

Redis host. This parameter is only used when the *type* parameter is setted to **redis**.

### redis_port
*default : **6379***

Redis port. This parameter is only used when the *type* parameter is setted to **redis**.


## Usage

### Caching

To use the **BigyouthPageCacheBundle**, your controller must extend the PageCacheController.php class and use the *render* function :

    <?php
    // ...
    
    /**
    * Class PageController
    *
    * @package Bigyouth\FrontBundle\Controller
    */
	class PageController extends PageCacheController
	{
	  
	    // ...
	    public function indexAction(Request $request)
	    {

	        // ...

	        return $this->render('FrontBundle:Page:index.html.twig');

	    }
    }

The PageCacheController rewrites the *render* function to manage and render cache data. All of your controller actions that use the *render* function will be cached.

### Cache invalidation

The **BigyouthPageCacheBundle** uses [cache tags](https://symfony.com/blog/new-in-symfony-3-2-tagged-cache) to identify your cache data. The tags are defined by the url scheme of the cached page.

For example, the page behind */products/list/my-product* will be tagged with **products**, **list** and **my-product**.

Therefore you can invalidate a product page by doing the following in your controller :

    
	// ...
	
	$this->get('by.page_cache')->invalidate(["my-product"]);
	
	// ...


or if you want to invalidate all product pages :

    
	// ...
	
	$this->get('by.page_cache')->invalidate(["products"]);
	
	// ...
	
----------


*author : [Alexis Smadja](mailto:alexis.smadja@bigyouth.fr)*