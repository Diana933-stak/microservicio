<?php

namespace App\Controllers;

use App\Models\RetroItem;
use App\Models\Sprint;
use Exception;
use Illuminate\Support\Collection;

class RetroItemController
{
    public function listar(?int $sprintId = null, ?string $categoria = null): Collection
    {
        $query = RetroItem::query()
            ->with('sprint:id,nombre,fecha_inicio')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($sprintId) {
            $query->where('sprint_id', $sprintId);
        }

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        return $query->get()->map(fn (RetroItem $item) => $this->formatear($item));
    }

    public function listarPorSprint(int $sprintId): Collection
    {
        $this->buscarSprint($sprintId);

        return $this->listar($sprintId);
    }

    public function accionesAnteriores(int $sprintId): Collection
    {
        $sprint = $this->buscarSprint($sprintId);

        return RetroItem::query()
            ->with('sprint:id,nombre,fecha_inicio')
            ->where('categoria', 'accion')
            ->where('sprint_id', '<>', $sprintId)
            ->whereHas('sprint', fn ($query) => $query->where('fecha_inicio', '<', $sprint->fecha_inicio))
            ->orderByDesc(
                Sprint::select('fecha_inicio')
                    ->whereColumn('sprints.id', 'retro_items.sprint_id')
                    ->limit(1)
            )
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (RetroItem $item) => $this->formatear($item));
    }

    public function guardar(int $sprintId, array $data): RetroItem
    {
        $data['sprint_id'] = $sprintId;
        $this->validar($data);

        return RetroItem::create($this->prepararDatos($data));
    }

    public function modificar(int $id, array $data): RetroItem
    {
        $item = $this->detalle($id);
        $this->validar($data);
        $item->fill($this->prepararDatos($data));
        $item->save();

        return $this->detalle($id);
    }

    public function actualizarCumplida(int $id, mixed $cumplida): RetroItem
    {
        $item = $this->detalle($id);

        if ($item->categoria !== 'accion') {
            throw new Exception('Solo las acciones pueden marcarse como cumplidas.', 422);
        }

        $item->cumplida = filter_var($cumplida, FILTER_VALIDATE_BOOLEAN);
        $item->save();

        return $this->detalle($id);
    }

    public function borrar(int $id): bool
    {
        return (bool) $this->detalle($id)->delete();
    }

    public function detalle(int $id): RetroItem
    {
        $item = RetroItem::with('sprint:id,nombre,fecha_inicio')->find($id);

        if (!$item) {
            throw new Exception("El item {$id} no existe.", 404);
        }

        return $item;
    }

    private function validar(array $data): void
    {
        if (empty($data['sprint_id']) || !Sprint::find((int) $data['sprint_id'])) {
            throw new Exception('El sprint seleccionado no existe.', 422);
        }

        if (empty($data['categoria']) || !in_array($data['categoria'], RetroItem::CATEGORIAS, true)) {
            throw new Exception('La categoria seleccionada no es válida.', 422);
        }

        if (empty(trim($data['descripcion'] ?? ''))) {
            throw new Exception('La descripción es obligatoria.', 422);
        }
    }

    private function prepararDatos(array $data): array
    {
        return [
            'sprint_id' => (int) $data['sprint_id'],
            'categoria' => $data['categoria'],
            'descripcion' => trim($data['descripcion']),
            'cumplida' => $data['categoria'] === 'accion'
                ? filter_var($data['cumplida'] ?? false, FILTER_VALIDATE_BOOLEAN)
                : null,
            'fecha_revision' => $data['fecha_revision'] ?? null,
        ];
    }

    private function buscarSprint(int $id): Sprint
    {
        $sprint = Sprint::find($id);

        if (!$sprint) {
            throw new Exception("El sprint {$id} no existe.", 404);
        }

        return $sprint;
    }

    private function formatear(RetroItem $item): RetroItem
    {
        $item->sprint_nombre = $item->sprint?->nombre;
        unset($item->sprint);

        return $item;
    }
}
