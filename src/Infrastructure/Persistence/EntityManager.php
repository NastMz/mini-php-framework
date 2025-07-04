<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Infrastructure\Persistence\Mapping\Table;
use App\Infrastructure\Persistence\Mapping\Column as ColumnAttr;
use ReflectionClass;
use ReflectionProperty;
use PDO;

/**
 * EntityManager
 *
 * A simple ORM-like entity manager for managing entities in a database.
 * Supports transactions, persistence, and retrieval of entities.
 */
class EntityManager
{
    /**
     * EntityManager constructor.
     *
     * @param PDO $pdo PDO instance for database connection
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Start a transaction.
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rollback the current transaction.
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Run a callable inside a transaction.
     */
    public function transaction(callable $work): mixed
    {
        try {
            $this->beginTransaction();
            $result = $work($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Persist a new entity (INSERT).
     */
    public function persist(object $entity): void
    {
        [$table, $data] = $this->extractMetadata($entity);
        (new QueryBuilder($this->pdo))
            ->insert($table, $data)
            ->execute();
    }

    /**
     * Find by primary key.
     */
    public function find(string $class, mixed $id): ?object
    {
        $rc     = new ReflectionClass($class);
        $table  = $this->getTableName($rc);
        $pkProp = $this->getIdProperty($rc);
        $col    = $this->getColumnMeta($pkProp);

        $row = (new QueryBuilder($this->pdo))
            ->select($table)
            ->where("{$col->name} = :id", ['id' => $id])
            ->execute()
            ->fetch();

        if (! $row) {
            return null;
        }

        // Hydrate
        $entity = $rc->newInstanceWithoutConstructor();
        foreach ($rc->getProperties() as $prop) {
            $meta = $this->getColumnMeta($prop);
            if (! $meta) {
                continue;
            }
            $prop->setAccessible(true);
            $prop->setValue($entity, $row[$meta->name]);
        }

        return $entity;
    }

    /**
     * Extract table name and non-auto columns/data for INSERT.
     */
    private function extractMetadata(object $entity): array
    {
        $rc    = new ReflectionClass($entity);
        $table = $this->getTableName($rc);
        $data  = [];

        foreach ($rc->getProperties() as $prop) {
            $meta = $this->getColumnMeta($prop);
            if (! $meta || $meta->auto) {
                continue;
            }
            $prop->setAccessible(true);
            $data[$meta->name] = $prop->getValue($entity);
        }

        return [$table, $data];
    }

    /**
     * Get the table name from the entity class.
     */
    private function getTableName(ReflectionClass $rc): string
    {
        $attr = $rc->getAttributes(Table::class)[0] ?? null;
        if (! $attr) {
            throw new \RuntimeException("Entity missing #[Table(...)]");
        }
        return $attr->newInstance()->name;
    }

    /**
     * Get the Column attribute metadata for a property.
     */
    private function getColumnMeta(ReflectionProperty $prop): ?ColumnAttr
    {
        $attr = $prop->getAttributes(ColumnAttr::class)[0] ?? null;
        return $attr?->newInstance();
    }

    /**
     * Get the ID property of an entity class.
     *
     * @throws \RuntimeException if no ID property is found
     */
    private function getIdProperty(ReflectionClass $rc): ReflectionProperty
    {
        foreach ($rc->getProperties() as $prop) {
            $meta = $this->getColumnMeta($prop);
            if ($meta && $meta->id) {
                return $prop;
            }
        }
        throw new \RuntimeException("No #[Column(id:true)] found in {$rc->getName()}");
    }
}
