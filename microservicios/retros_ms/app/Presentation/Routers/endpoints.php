<?php

use App\Presentation\Repositories\RetroItemsRepository;
use App\Presentation\Repositories\SprintsRepository;
use App\Presentation\Repositories\TestRepository;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('', [TestRepository::class, 'default']);
    $app->get('/', [TestRepository::class, 'default']);

    $app->group('/sprints', function (RouteCollectorProxy $group) {
        $group->get('', [SprintsRepository::class, 'list']);
        $group->post('', [SprintsRepository::class, 'create']);
        $group->get('/{id}', [SprintsRepository::class, 'detail']);
        $group->put('/{id}', [SprintsRepository::class, 'update']);
        $group->delete('/{id}', [SprintsRepository::class, 'delete']);
        $group->get('/{id}/items', [RetroItemsRepository::class, 'listBySprint']);
        $group->post('/{id}/items', [RetroItemsRepository::class, 'create']);
        $group->get('/{id}/acciones-anteriores', [RetroItemsRepository::class, 'previousActions']);
    });

    $app->group('/items', function (RouteCollectorProxy $group) {
        $group->get('', [RetroItemsRepository::class, 'list']);
        $group->put('/{id}', [RetroItemsRepository::class, 'update']);
        $group->patch('/{id}/cumplida', [RetroItemsRepository::class, 'complete']);
        $group->delete('/{id}', [RetroItemsRepository::class, 'delete']);
    });
};
