<?php

namespace Weebel\Router;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Weebel\Contracts\Container;

class RouterListener
{
    public function __construct(
        protected UrlMatcher               $urlMatcher,
        protected RequestContext           $requestContext,
        protected Container                $container,
        protected RouteCollection          $routeCollection,
        protected EventDispatcherInterface $eventDispatcher,
        protected Request                  $request,
        protected Response                 $response
    ) {
    }

    public function __invoke(): void
    {
        $request = $this->request;
        $this->requestContext->fromRequest($request);
        try {
            $res = $this->urlMatcher->matchRequest($request);
        } catch
        (\Throwable $exception) {
            throw new RouterException("Route not found. " . $exception->getMessage());
        }

        foreach ($res as $key => $value) {
            $this->container->set($key, $value);
        }

        $route = $this->routeCollection->get($res['_route']);
        $this->container->set(Route::class, $route);

        $this->eventDispatcher->dispatch(new RouteResolved($route));
    }
}
