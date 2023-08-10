<?php

namespace RafahSBorges\EloquentModelGenerator\Processor;

use RafahSBorges\EloquentModelGenerator\Config\Config;
use RafahSBorges\EloquentModelGenerator\Model\EloquentModel;

interface ProcessorInterface
{
    public function process(EloquentModel $model, Config $config): void;
    public function getPriority(): int;
}
