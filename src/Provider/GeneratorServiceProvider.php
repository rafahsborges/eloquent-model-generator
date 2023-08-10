<?php

namespace RafahSBorges\EloquentModelGenerator\Provider;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use RafahSBorges\EloquentModelGenerator\Command\GenerateModelCommand;
use RafahSBorges\EloquentModelGenerator\Command\GenerateModelsCommand;
use RafahSBorges\EloquentModelGenerator\EventListener\GenerateCommandEventListener;
use RafahSBorges\EloquentModelGenerator\Generator;
use RafahSBorges\EloquentModelGenerator\Processor\CustomPrimaryKeyProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\CustomPropertyProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\FieldProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\NamespaceProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\RelationProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\TableNameProcessor;
use RafahSBorges\EloquentModelGenerator\TypeRegistry;

class GeneratorServiceProvider extends ServiceProvider
{
    public const PROCESSOR_TAG = 'eloquent_model_generator.processor';

    public function register()
    {
        $this->commands([
            GenerateModelCommand::class,
            GenerateModelsCommand::class,
        ]);

        $this->app->singleton(TypeRegistry::class);
        $this->app->singleton(GenerateCommandEventListener::class);

        $this->app->tag([
            FieldProcessor::class,
            NamespaceProcessor::class,
            RelationProcessor::class,
            CustomPropertyProcessor::class,
            TableNameProcessor::class,
            CustomPrimaryKeyProcessor::class,
        ], self::PROCESSOR_TAG);

        $this->app->bind(Generator::class, function ($app) {
            return new Generator($app->tagged(self::PROCESSOR_TAG));
        });
    }

    public function boot()
    {
        Event::listen(CommandStarting::class, [GenerateCommandEventListener::class, 'handle']);
    }
}
