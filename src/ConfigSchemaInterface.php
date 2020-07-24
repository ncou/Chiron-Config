<?php

declare(strict_types=1);

namespace Chiron\Config;

interface ConfigSchemaInterface extends ConfigInterface
{
    public function setData(array $data): void;

    public function addData(array $data): void;

    public function resetData(): void;
}
