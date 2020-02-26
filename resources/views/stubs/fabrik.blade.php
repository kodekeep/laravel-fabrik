namespace {{ $dummyNamespace }};

use KodeKeep\Fabrik\ModelFactory;
use {{ $dummyFullModelClass }};
use Faker\Generator;

class {{ $dummyFactory }} extends ModelFactory
{
    protected string $modelClass = {{ $dummyModelClass }}::class;

    public function getData(Generator $faker): array
    {
        return [
@foreach($dummyColumns as $column => $type)
            '{{ $column }}' => $faker->{{ $type }},
@endforeach
        ];
    }
}
