<?php

namespace Weebel\Router;

use Symfony\Component\Routing\Route;

class RouteResolved
{
    public Route $route;

    /**
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }


    public function __get(string $key): mixed
    {
        return $this->route->getDefault($key);
    }

    public function __set(string $name, $value): void
    {
        $this->route->setDefault($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->route->hasDefault($name);
    }
}
