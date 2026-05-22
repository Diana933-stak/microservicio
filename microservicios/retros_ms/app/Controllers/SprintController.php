<?php

namespace App\Controllers;

use App\Models\Sprint;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class SprintController
{
    private Sprint $model;

    public function __construct()
    {
        $this->model = new Sprint();
    }

    public function listar(): Collection
    {
        return $this->model
            ->query()
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();
    }

    public function detalle(int $id): Sprint
    {
        $sprint = $this->model->find($id);

        if (!$sprint) {
            throw new Exception("El sprint {$id} no existe.", 404);
        }

        return $sprint;
    }

    public function guardar(array $data): Sprint
    {
        $this->validar($data);

        return $this->model->create([
            'nombre' => trim($data['nombre']),
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
        ]);
    }

    public function modificar(int $id, array $data): Sprint
    {
        $sprint = $this->detalle($id);

        $this->validar($data);

        $sprint->fill([
            'nombre' => trim($data['nombre']),
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
        ]);

        $sprint->save();

        return $sprint;
    }

    public function borrar(int $id): bool
    {
        return (bool) $this->detalle($id)->delete();
    }

    private function validar(array $data): void
    {
        if (
            empty(trim($data['nombre'] ?? '')) ||
            empty($data['fecha_inicio']) ||
            empty($data['fecha_fin'])
        ) {
            throw new Exception(
                'Nombre, fecha de inicio y fecha de fin son obligatorios.',
                422
            );
        }

        if ($data['fecha_fin'] < $data['fecha_inicio']) {
            throw new Exception(
                'La fecha de fin debe ser mayor o igual a la fecha de inicio.',
                422
            );
        }
    }
}