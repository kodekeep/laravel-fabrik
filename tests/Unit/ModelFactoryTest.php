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

namespace KodeKeep\Fabrik\Tests\Unit;

use BadMethodCallException;
use Illuminate\Support\Collection;
use KodeKeep\Fabrik\Tests\Factories\Post;
use KodeKeep\Fabrik\Tests\Factories\User;
use KodeKeep\Fabrik\Tests\Factories\UserFactory;
use KodeKeep\Fabrik\Tests\TestCase;

/**
 * @covers \KodeKeep\Fabrik\ModelFactory
 */
class ModelFactoryTest extends TestCase
{
    /** @test */
    public function it_gives_you_a_factory_instance(): void
    {
        $this->assertInstanceOf(UserFactory::class, UserFactory::new());
    }

    /** @test */
    public function it_gives_you_a_factory_model_instance_that_was_persisted(): void
    {
        $this->assertInstanceOf(User::class, UserFactory::new()->create());
    }

    /** @test */
    public function it_gives_you_multiple_factory_model_instances_that_are_persisted(): void
    {
        $this->assertInstanceOf(Collection::class, UserFactory::new()->times(3)->create());
        $this->assertCount(3, UserFactory::new()->times(3)->create());
    }

    /** @test */
    public function it_gives_you_multiple_factory_model_instances_that_are_unique_and_persisted(): void
    {
        $collection = UserFactory::new()->times(3)->create();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertCount(3, collect($collection)->pluck('name'));
    }

    /** @test */
    public function it_gives_you_a_factory_model_instance_that_was_instantiated(): void
    {
        $this->assertInstanceOf(User::class, UserFactory::new()->make());
    }

    /** @test */
    public function it_gives_you_multiple_factory_model_instances_that_are_instantiated(): void
    {
        $this->assertInstanceOf(Collection::class, UserFactory::new()->times(3)->make());
        $this->assertCount(3, UserFactory::new()->times(3)->make());
    }

    /** @test */
    public function it_gives_you_multiple_factory_model_instances_that_are_unique_and_instantiated(): void
    {
        $collection = UserFactory::new()->times(3)->make();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertCount(3, collect($collection)->pluck('name'));
    }

    /** @test */
    public function it_gives_you_a_factory_model_instance_that_is_raw(): void
    {
        $this->assertIsArray(UserFactory::new()->raw());
    }

    /** @test */
    public function it_gives_you_multiple_factory_model_instances_that_are_raw(): void
    {
        $this->assertInstanceOf(Collection::class, UserFactory::new()->times(3)->raw());
        $this->assertCount(3, UserFactory::new()->times(3)->raw());
    }

    /** @test */
    public function it_gives_you_multiple_factory_model_instances_that_are_unique_and_raw(): void
    {
        $collection = UserFactory::new()->times(3)->raw();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertCount(3, collect($collection)->pluck('name'));
    }

    /** @test */
    public function it_lets_you_overwrite_default_data(): void
    {
        $this->assertSame('John', UserFactory::new()->create(['name' => 'John'])->name);
    }

    /** @test */
    public function it_gies_you_a_new_factory_when_using_times(): void
    {
        $factory = UserFactory::new();

        $this->assertSame($factory, $factory->with(Post::class, 'posts'));
        $this->assertNotSame($factory, $factory->times(3));
    }

    /** @test **/
    public function it_lets_you_add_a_related_model(): void
    {
        $user = UserFactory::new()->with(Post::class, 'posts')->create();

        $this->assertSame(1, $user->posts->count());
        $this->assertInstanceOf(Post::class, $user->posts->first());
    }

    /** @test **/
    public function it_lets_you_add_a_related_model_through_a_magic_method_call(): void
    {
        $user = UserFactory::new()->withPost()->create();

        $this->assertSame(1, $user->posts->count());
        $this->assertInstanceOf(Post::class, $user->posts->first());
    }

    /** @test **/
    public function it_lets_you_add_multiple_related_models(): void
    {
        $user = UserFactory::new()->with(Post::class, 'posts', 4)->create();

        $this->assertSame(4, $user->posts->count());
        $this->assertInstanceOf(Post::class, $user->posts->first());
    }

    /** @test **/
    public function it_lets_you_add_multiple_related_models_through_a_magic_method_call(): void
    {
        $user = UserFactory::new()->withPosts(4)->create();

        $this->assertSame(4, $user->posts->count());
        $this->assertInstanceOf(Post::class, $user->posts->first());
    }

    /** @test **/
    public function it_throws_if_an_unknown_method_is_called(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method KodeKeep\Fabrik\Tests\Factories\UserFactory::unknownMethod()');

        UserFactory::new()->unknownMethod()->create();
    }
}
