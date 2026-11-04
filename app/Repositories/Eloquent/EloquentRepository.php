<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @implements RepositoryInterface<TModel>
 */
abstract class EloquentRepository implements RepositoryInterface
{
    /**
     * @return class-string<TModel>
     */
    abstract protected function modelClass(): string;

    public function find(int $id): ?Model
    {
        $class = $this->modelClass();

        return $class::query()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        $class = $this->modelClass();

        return $class::query()->findOrFail($id);
    }

    public function create(array $attributes = []): Model
    {
        $class = $this->modelClass();

        return $class::create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->update($attributes);

        return $model;
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
