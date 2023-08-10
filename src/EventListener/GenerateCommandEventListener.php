<?php

namespace RafahSBorges\EloquentModelGenerator\EventListener;

use Illuminate\Console\Events\CommandStarting;
use RafahSBorges\EloquentModelGenerator\TypeRegistry;

class GenerateCommandEventListener
{
    private const SUPPORTED_COMMANDS = [
        'rafahsborges:generate:model',
        'rafahsborges:generate:models',
    ];

    public function __construct(private TypeRegistry $typeRegistry) {}

    public function handle(CommandStarting $event): void
    {
        if (!in_array($event->command, self::SUPPORTED_COMMANDS)) {
            return;
        }

        $userTypes = config('eloquent_model_generator.db_types', []);
        foreach ($userTypes as $type => $value) {
            $this->typeRegistry->registerType($type, $value);
        }
    }
}