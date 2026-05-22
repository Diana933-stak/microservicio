<?php

namespace App\Presentation\Repositories;

use App\Controllers\RetroItemController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RetroItemsRepository extends Repository
{
    private RetroItemController $controller;

    public function __construct()
    {
        $this->controller = new RetroItemController();
    }

    public function list(Request $request, Response $response): Response
    {
        $query = $request->getQueryParams();
        $sprintId = isset($query['sprint_id']) ? (int) $query['sprint_id'] : null;
        $categoria = $query['categoria'] ?? null;

        return $this->ok($response, $this->controller->listar($sprintId, $categoria));
    }

    public function listBySprint(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->ok($response, $this->controller->listarPorSprint((int) $args['id']));
        } catch (Exception $exception) {
            return $this->fail($response, $exception);
        }
    }

    public function previousActions(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->ok($response, $this->controller->accionesAnteriores((int) $args['id']));
        } catch (Exception $exception) {
            return $this->fail($response, $exception);
        }
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->ok(
                $response,
                $this->controller->guardar((int) $args['id'], $this->body($request)),
                201
            );
        } catch (Exception $exception) {
            return $this->fail($response, $exception);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->ok($response, $this->controller->modificar((int) $args['id'], $this->body($request)));
        } catch (Exception $exception) {
            return $this->fail($response, $exception);
        }
    }

    public function complete(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $this->body($request);
            return $this->ok($response, $this->controller->actualizarCumplida((int) $args['id'], $data['cumplida'] ?? false));
        } catch (Exception $exception) {
            return $this->fail($response, $exception);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->controller->borrar((int) $args['id']);
            return $this->message($response, 'Item eliminado correctamente.');
        } catch (Exception $exception) {
            return $this->fail($response, $exception);
        }
    }
}
