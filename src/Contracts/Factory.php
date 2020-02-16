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

namespace KodeKeep\Fabrik\Contracts;

use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface Factory
{
    public function create(array $extra = []): Model;

    public function times(int $times, array $extra = []): Collection;

    public function with(string $relatedModelClass, string $relationshipName, int $times = 1);

    public function getData(Generator $faker): array;
}
