<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Fabrik.
 *
 * (c) KodeKeep <hello@kodekeep.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KodeKeep\Fabrik;

use BadMethodCallException;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use KodeKeep\Fabrik\Contracts\Factory as Contract;

abstract class Factory implements Contract
{
    protected string $modelClass;

    private Collection $relatedModels;

    public function __construct()
    {
        $this->relatedModels = new Collection();
    }

    public static function new(): self
    {
        return new static();
    }

    public function create(array $extra = []): Model
    {
        $result = $this->modelClass::create($this->raw($extra));

        $this
            ->relatedModels
            ->each(fn ($models, $relationship) => $result->{$relationship}()->saveMany($models));

        return $result;
    }

    public function make(array $extra = []): Model
    {
        return new $this->modelClass($this->raw($extra));
    }

    public function raw(array $extra = []): array
    {
        return array_merge($this->getData(FakerFactory::create()), $extra);
    }

    public function times(int $times, array $extra = []): Collection
    {
        return collect()->times($times)->transform(fn () => $this->create($extra));
    }

    public function timesMake(int $times, array $extra = []): Collection
    {
        return collect()->times($times)->transform(fn () => $this->make($extra));
    }

    public function timesRaw(int $times, array $extra = []): Collection
    {
        return collect()->times($times)->transform(fn () => $this->raw($extra));
    }

    public function with(string $relatedModelClass, string $relationshipName, int $times = 1): self
    {
        $this->relatedModels->put(
            $relationshipName,
            $this->guessFactoryClassName($relatedModelClass)->timesMake($times)
        );

        return $this;
    }

    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return $this->forwardCallToWith($method, $parameters);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $method));
    }

    private function guessFactoryClassName(string $className): Contract
    {
        $baseClassName = (new \ReflectionClass($className))->getShortName();
        $factoryClass  = config('fabrik.namespaces.factories').'\\'.$baseClassName.'Factory';

        return new $factoryClass();
    }

    private function forwardCallToWith($method, $parameters): Contract
    {
        $modelName  = substr($method, 4);
        $modelClass = config('fabrik.namespaces.models').'\\'.Str::singular($modelName);

        $relationshipName = Str::camel(Str::plural($modelName));

        return $this->with($modelClass, $relationshipName, Arr::get($parameters, 0, 1));
    }
}
