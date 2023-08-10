<?php

namespace RafahSBorges\EloquentModelGenerator\Processor;

use Illuminate\Database\DatabaseManager;
use RafahSBorges\CodeGenerator\Model\DocBlockModel;
use RafahSBorges\CodeGenerator\Model\PropertyModel;
use RafahSBorges\CodeGenerator\Model\VirtualPropertyModel;
use RafahSBorges\EloquentModelGenerator\Config\Config;
use RafahSBorges\EloquentModelGenerator\Helper\Prefix;
use RafahSBorges\EloquentModelGenerator\Model\EloquentModel;
use RafahSBorges\EloquentModelGenerator\TypeRegistry;

class FieldProcessor implements ProcessorInterface
{
    public function __construct(private DatabaseManager $databaseManager, private TypeRegistry $typeRegistry) {}
    
    public function process(EloquentModel $model, Config $config): void
    {
        $schemaManager = $this->databaseManager->connection($config->getConnection())->getDoctrineSchemaManager();

        $tableDetails = $schemaManager->listTableDetails(Prefix::add($model->getTableName()));
        $primaryColumnNames = $tableDetails->getPrimaryKey() ? $tableDetails->getPrimaryKey()->getColumns() : [];

        $columnNames = [];
        foreach ($tableDetails->getColumns() as $column) {
            $model->addProperty(new VirtualPropertyModel(
                $column->getName(),
                $this->typeRegistry->resolveType($column->getType()->getName())
            ));

            if (!in_array($column->getName(), $primaryColumnNames)) {
                $columnNames[] = $column->getName();
            }
        }

        $fillableProperty = new PropertyModel('fillable');
        $fillableProperty->setAccess('protected')
            ->setValue($columnNames)
            ->setDocBlock(new DocBlockModel('@var array'));
        $model->addProperty($fillableProperty);
    }

    public function getPriority(): int
    {
        return 5;
    }
}
