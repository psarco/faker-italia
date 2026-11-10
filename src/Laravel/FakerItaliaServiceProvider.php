<?php

declare(strict_types=1);

namespace GeneratoreDati\FakerItalia\Laravel;

use Faker\Generator;
use GeneratoreDati\FakerItalia\FakerProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Registered automatically by Laravel package discovery: adds the provider to every Faker
 * instance, so fake()->codiceFiscale() and $this->faker->partitaIva() work in factories and tests.
 */
final class FakerItaliaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(Generator::class, function (Generator $faker): void {
            foreach ($faker->getProviders() as $provider) {
                if ($provider instanceof FakerProvider) {
                    return;
                }
            }
            $faker->addProvider(new FakerProvider($faker));
        });
    }
}
