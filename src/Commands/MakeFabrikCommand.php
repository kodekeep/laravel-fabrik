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

namespace KodeKeep\Fabrik\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use KodeKeep\Fabrik\Tests\Factories\User;

class MakeFabrikCommand extends Command
{
    protected $signature = 'make:fabrik {modelClass} {--force}';

    protected $description = 'Create a new model fabrik.';

    private $fakerByName = [
        'email' => 'safeEmail',
        'name'  => 'name',
        'uuid'  => 'uuid',
    ];

    private $fakerByType = [
        'datetime' => 'dateTime',
        'integer'  => 'randomNumber',
        'string'   => 'word',
        'uuid'     => 'uuid',
    ];

    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $this->fullClassName = $this->argument('modelClass');
        $this->className     = class_basename($this->fullClassName);

        $classPath = config('fabrik.paths.factories').'/'.$this->className.'Factory.php';

        $alreadyExists = $this->files->exists($classPath);

        if ((! $this->hasOption('force') || ! $this->option('force')) && $alreadyExists) {
            $this->error('Fabrik already exists!');

            return false;
        }

        if (! $this->files->isDirectory(dirname($classPath))) {
            $this->files->makeDirectory(dirname($classPath), 0777, true, true);
        }

        $this->files->put($classPath, '<?php'.PHP_EOL.PHP_EOL.view('fabrik::stubs.fabrik', [
            'dummyNamespace'      => config('fabrik.namespaces.factories'),
            'dummyFullModelClass' => $this->fullClassName,
            'dummyModelClass'     => $this->className,
            'dummyFactory'        => $this->className.'Factory',
            'dummyColumns'        => $this->getFakerTypes($this->getTableColumns(User::class)),
        ])->render());

        $this->info(config('fabrik.namespaces.factories').'\\'.$this->className.' created successfully.');
    }

    private function getTableColumns(string $model): array
    {
        $model = new $model();

        $table = $model->getTable();

        $schema = (new $model())->getConnection()->getSchemaBuilder();

        $columns = $schema->getColumnListing($table);

        return collect($columns)
            ->mapWithKeys(fn ($key) => [$key => $schema->getColumnType($table, $key)])
            ->toArray();
    }

    private function getFakerTypes(array $columns): array
    {
        return collect($columns)->mapWithKeys(function ($value, $key) {
            if (array_key_exists($key, $this->fakerByName)) {
                $type = $this->fakerByName[$key];
            } else {
                $type = $this->fakerByType[$value];
            }

            return [$key => $type];
        })->toArray();
    }
}
