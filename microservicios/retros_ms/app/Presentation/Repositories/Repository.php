<?php

namespace App\Presentation\Repositories;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class Repository
{
    protected function body(Request $request): array
    {
        $contents = $request->getBody()->getContents();
        $data = json_decode($contents, true);

        return is_array($data) ? $data : [];
    }

    protected function json(Response $response, array $payload, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    protected function ok(Response $response, mixed $data = null, int $status = 200): Response
    {
        return $this->json($response, [
            'ok' => true,
            'data' => $data,
        ], $status);
    }

    protected function message(Response $response, string $message, int $status = 200): Response
    {
        return $this->json($response, [
            'ok' => true,
            'message' => $message,
        ], $status);
    }

    protected function fail(Response $response, Exception $exception): Response
    {
        $status = $exception->getCode();

        if ($status < 400 || $status > 599) {
            $status = 500;
        }

        return $this->json($response, [
            'ok' => false,
            'message' => $exception->getMessage() ?: 'Error en el servicio.',
        ], $status);
    }
}
