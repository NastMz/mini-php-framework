<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapping;

use Attribute;

/**
 * Table
 *
 * Attribute to define the database table name for an entity class.
 * Used by the ORM to map entities to their corresponding database tables.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    /**
     * Table constructor.
     *
     * @param string $name The name of the database table
     */
    public function __construct(public string $name) {}
}
