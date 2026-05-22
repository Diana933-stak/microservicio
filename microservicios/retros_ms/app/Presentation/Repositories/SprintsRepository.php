<?php

namespace App\Presentation\Repositories;

use App\Controllers\SprintController;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SprintsRepository extends Repository
{
    private SprintController $controller;

    public function __construct()
    {
        $this->controller = new SprintController();
    }

    public function list(Request $request, Response $response): Response
    {
        return $this->ok($response, $this->controller->listar());
    }

    public function detail(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->ok($response, $this->controller->detalle((int) $args['id']));
        } catch (Exception $exception) {
            return $this->fail($response, $exception);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            return $this->ok($response, $this->controller->guardar($this->body($request)), 201);
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

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->controller->borrar((int) $args['id']);
            return $this->message($response, 'Sprint eliminado correctamente.');
        } catch (Exception $exception) {
            return $this->fail($response, $exception);
        }
    }
}
