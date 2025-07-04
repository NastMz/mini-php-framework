<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapping;

use Attribute;

/**
 * Column
 *
 * Attribute to define a database column for an entity property.
 * Used by the ORM to map entity properties to their corresponding database columns.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    /**
     * Column constructor.
     *
     * @param string $name The name of the database column
     * @param bool $id Whether this column is an identifier (primary key)
     * @param bool $auto Whether this column is auto-incremented
     */
    public function __construct(
        public string $name,
        public bool   $id   = false,
        public bool   $auto = false
    ) {}
}
