<?php

namespace AppBundle\Entity;


interface ResourcesInterface
{
    public function setId(int $id);
    public function getId(): int;

    public function setName(string $name);
    public function getName(): string;

    public function setDefValue(int $defValue);
    public function getDefValue(): int;
}