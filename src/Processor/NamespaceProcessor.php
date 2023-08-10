<?php

namespace RafahSBorges\EloquentModelGenerator\Processor;

use RafahSBorges\CodeGenerator\Model\NamespaceModel;
use RafahSBorges\EloquentModelGenerator\Config\Config;
use RafahSBorges\EloquentModelGenerator\Model\EloquentModel;

class NamespaceProcessor implements ProcessorInterface
{
    public function process(EloquentModel $model, Config $config): void
    {
        $model->setNamespace(new NamespaceModel($config->getNamespace()));
    }

    public function getPriority(): int
    {
        return 6;
    }
}
