<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;

		$router->addRoute('pet', 'Api:ProcessAnimalRequest');
		$router->addRoute('pet/<id>', 'Api:ProcessAnimalRequestId');
		$router->addRoute('pet/findByTags/<tag>', 'Api:GetByTag');
		$router->addRoute('pet/findByStatus/<status>', 'Api:GetByStatus');

		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		$router->addRoute('animal-detail/<id>', 'AnimalDetail:default');

		return $router;
	}
}
