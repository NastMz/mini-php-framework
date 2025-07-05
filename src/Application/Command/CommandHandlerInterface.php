<?php
declare(strict_types=1);

namespace App\Application\Command;

/**
 * A handler for a specific CommandInterface.
 */
interface CommandHandlerInterface
{
    /**
     * @param CommandInterface $command
     * @return mixed
     */
    public function handle(CommandInterface $command): mixed;
}
