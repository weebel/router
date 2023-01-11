<?php

namespace Weebel\Router;

use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Weebel\Contracts\Bootable;
use Weebel\Contracts\Configuration;
use Weebel\Contracts\Container;
use Weebel\Contracts\EventDispatcher;

class RouterProvider implements Bootable
{
    public function __construct(
        protected Container       $container,
        protected Configuration   $configuration,
        protected EventDispatcher $eventDispatcher
    ) {
    }


    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws RouterException
     */
    public function boot(): void
    {
        $routeCollection = $this->container->get(RouteCollection::class);
        $context = $this->container->get(RequestContext::class);

        $matcher = new UrlMatcher($routeCollection, $context);
        $this->container->set(UrlMatcher::class, $matcher);
        $this->container->set(UrlMatcherInterface::class, $matcher);
        $this->container->set(RequestMatcherInterface::class, $matcher);
        $this->container->set(RequestContextAwareInterface::class, $matcher);

        $routes = $this->configuration->get('routes', []);

        $routes && $this->registerRoutes($routes, $routeCollection);

        $this->eventDispatcher->addListener('http.request', RouterListener::class);
    }

    /**
     * @throws RouterException
     */
    private function registerRoutes(array $routes, RouteCollection $routeCollection): void
    {
        foreach ($routes as $key => $route) {
            if (!$route instanceof Route) {
                throw new RouterException("Item $key in routes config is not instance of " . Route::class);
            }

            $routeCollection->add($key, $route);
        }
    }
}
