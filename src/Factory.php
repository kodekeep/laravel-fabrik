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
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use KodeKeep\Fabrik\Contracts\Factory as Contract;

abstract class Factory implements Contract
{
    protected string $modelClass;

    private Collection $relatedModels;

    private int $amount = 1;

    public function __construct()
    {
        $this->relatedModels = new Collection();
    }

    public static function new(): self
    {
        return new static();
    }

    public function create(array $extra = [])
    {
        return $this->createMany(function () use ($extra) {
            $result = $this->modelClass::create($this->mergeExtraAttributes($extra));

            $this->relatedModels->each(fn ($models, $relationship) => $result->{$relationship}()->saveMany($models));

            return $result;
        });
    }

    public function make(array $extra = [])
    {
        return $this->createMany(fn () => new $this->modelClass($this->mergeExtraAttributes($extra)));
    }

    public function raw(array $extra = [])
    {
        return $this->createMany(fn () => $this->mergeExtraAttributes($extra));
    }

    public function times(int $amount): self
    {
        $clone = clone $this;

        $clone->amount = $amount;

        return $clone;
    }

    public function with(string $relatedModelClass, string $relationshipName, int $times = 1): self
    {
        $this->relatedModels->put(
            $relationshipName,
            Collection::wrap($this->guessFactoryClassName($relatedModelClass)->times($times)->make())
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

    private function createMany(callable $callback)
    {
        $result = collect()->times($this->amount)->transform($callback);

        $this->times = 1;

        if ($result->count() === 1) {
            return $result->first();
        }

        return $result;
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

    private function mergeExtraAttributes(array $extra): array
    {
        return array_merge($this->getData(FakerFactory::create()), $extra);
    }
}
