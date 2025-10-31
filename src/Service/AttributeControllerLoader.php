<?php

declare(strict_types=1);

namespace UserRankingBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use UserRankingBundle\Controller\UserRankingArchiveCrudController;
use UserRankingBundle\Controller\UserRankingBlacklistCrudController;
use UserRankingBundle\Controller\UserRankingItemCrudController;
use UserRankingBundle\Controller\UserRankingListCrudController;
use UserRankingBundle\Controller\UserRankingPositionCrudController;

#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    private RouteCollection $collection;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();

        $this->collection = new RouteCollection();
        $this->collection->addCollection($this->controllerLoader->load(UserRankingListCrudController::class));
        $this->collection->addCollection($this->controllerLoader->load(UserRankingItemCrudController::class));
        $this->collection->addCollection($this->controllerLoader->load(UserRankingArchiveCrudController::class));
        $this->collection->addCollection($this->controllerLoader->load(UserRankingPositionCrudController::class));
        $this->collection->addCollection($this->controllerLoader->load(UserRankingBlacklistCrudController::class));
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        return $this->collection;
    }
}
