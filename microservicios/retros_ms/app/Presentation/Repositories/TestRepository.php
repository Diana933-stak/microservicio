<?php

namespace App\Presentation\Repositories;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestRepository extends Repository
{
    public function default(Request $request, Response $response): Response
    {
        return $this->json($response, [
            'ok' => true,
            'message' => 'Microservicio Registro de Retrospectivas Scrum',
            'endpoints' => [
                'GET /index.php/sprints',
                'POST /index.php/sprints',
                'GET /index.php/sprints/{id}',
                'PUT /index.php/sprints/{id}',
                'DELETE /index.php/sprints/{id}',
                'GET /index.php/sprints/{id}/items',
                'POST /index.php/sprints/{id}/items',
                'GET /index.php/sprints/{id}/acciones-anteriores',
                'GET /index.php/items',
                'PUT /index.php/items/{id}',
                'PATCH /index.php/items/{id}/cumplida',
                'DELETE /index.php/items/{id}',
            ],
        ]);
    }
}
