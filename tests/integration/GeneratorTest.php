<?php

namespace RafahSBorges\Tests\Integration;

use Illuminate\Database\Connectors\SQLiteConnector;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\SQLiteConnection;
use RafahSBorges\EloquentModelGenerator\Config\Config;
use RafahSBorges\EloquentModelGenerator\Generator;
use RafahSBorges\EloquentModelGenerator\Helper\EmgHelper;
use RafahSBorges\EloquentModelGenerator\Processor\CustomPrimaryKeyProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\CustomPropertyProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\FieldProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\NamespaceProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\RelationProcessor;
use RafahSBorges\EloquentModelGenerator\Processor\TableNameProcessor;
use RafahSBorges\EloquentModelGenerator\TypeRegistry;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    private static SQLiteConnection $connection;
    private Generator $generator;

    public static function setUpBeforeClass(): void
    {
        $connector = new SQLiteConnector();
        $pdo = $connector->connect([
            'database' => ':memory:',
            'foreign_key_constraints' => true,
        ]);
        self::$connection = new SQLiteConnection($pdo);

        $queries = explode("\n\n", file_get_contents(__DIR__ . '/resources/schema.sql'));
        foreach ($queries as $query) {
            self::$connection->statement($query);
        }
    }

    protected function setUp(): void
    {
        $databaseManagerMock = $this->createMock(DatabaseManager::class);
        $databaseManagerMock->expects($this->any())
            ->method('connection')
            ->willReturn(self::$connection);

        $typeRegistry = new TypeRegistry($databaseManagerMock);

        $this->generator = new Generator([
            new CustomPrimaryKeyProcessor($databaseManagerMock, $typeRegistry),
            new CustomPropertyProcessor(),
            new FieldProcessor($databaseManagerMock, $typeRegistry),
            new NamespaceProcessor(),
            new RelationProcessor($databaseManagerMock),
            new TableNameProcessor($databaseManagerMock),
        ]);
    }

    /**
     * @dataProvider modelNameProvider
     */
    public function testGeneratedModel(string $modelName): void
    {
        $config = (new Config())
            ->setClassName($modelName)
            ->setNamespace('App\Models')
            ->setBaseClassName(Model::class);

        $model = $this->generator->generateModel($config);
        $this->assertEquals(file_get_contents(__DIR__ . '/resources/' . $modelName . '.php.generated'), $model->render());
    }

    public function modelNameProvider(): array
    {
        return [
            [
                'modelName' => 'User',
            ],
            [
                'modelName' => 'Role',
            ],
            [
                'modelName' => 'Organization',
            ],
            [
                'modelName' => 'Avatar',
            ],
            [
                'modelName' => 'Post',
            ],
        ];
    }

    public function testGeneratedModelWithCustomProperties(): void
    {
        $config = (new Config())
            ->setClassName('User')
            ->setNamespace('App')
            ->setBaseClassName('Base\ClassName')
            ->setNoTimestamps(true)
            ->setDateFormat('d/m/y');

        $model = $this->generator->generateModel($config);
        $this->assertEquals(file_get_contents(__DIR__ . '/resources/User-with-params.php.generated'), $model->render());
    }
}
