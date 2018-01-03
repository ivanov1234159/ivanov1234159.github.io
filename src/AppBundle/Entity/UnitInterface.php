<?php

namespace AppBundle\Entity;


interface UnitInterface
{
    public function setId(int $id);
    public function getId(): int;

    public function setName(string $name);
    public function getName(): string;

    public function setAttack(float $attack);
    public function getAttack(): float;

    public function setLive(float $live);
    public function getLive(): float;

    public function setNBLGname(string $NBL_Gname);
    public function getNBLGname(): string;

    public function setResNPUGname(string $resNPU_Gname);
    public function getResNPUGname(): string;

    public function setTimePU(int $timePU);
    public function getTimePU(): int;

    public function setCount(int $count);
    public function getCount(): int;

    public function setOrderCount(int $orderCount);
    public function getOrderCount(): int;
}