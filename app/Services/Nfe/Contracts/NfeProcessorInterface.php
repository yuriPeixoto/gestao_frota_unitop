<?php

namespace App\Services\Nfe\Contracts;

interface NfeProcessorInterface
{
    public function setCaminho(string $path): self;

    public function save(): array;
}
