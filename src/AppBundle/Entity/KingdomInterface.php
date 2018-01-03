<?php

namespace AppBundle\Entity;


interface KingdomInterface
{
    public function setId(int $id);
    public function getId(): int;

    public function setName(string $name);
    public function getName(): string;

    public function setCoordinateX(int $coordinateX);
    public function getCoordinateX(): int;

    public function setCoordinateY(int $coordinateY);
    public function getCoordinateY(): int;

    public function setPopulationCounter(int $populationCounter);
    public function getPopulationCounter(): int;
}