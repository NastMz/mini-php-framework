<?php
declare(strict_types=1);

namespace App\Application\Command;

/**
 * Dispatches Commands to their handlers.
 */
interface CommandBusInterface
{
    /**
     * @param CommandInterface $command
     * @return mixed
     */
    public function dispatch(CommandInterface $command): mixed;
}
