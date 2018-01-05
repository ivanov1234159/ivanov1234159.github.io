<?php
/**
 * Created by PhpStorm.
 * User: TOIPC
 * Date: 30.12.2017 г.
 * Time: 17:09 ч.
 */

namespace AppBundle\Entity;


interface BuildingInterface
{
    public function setId(int $id);
    public function getId(): int;

    public function setName(string $name);
    public function getName(): string;

    public function setResGPHGname($resGPH_Gname);
    public function getResGPHGname();

    public function setNBLGname($NBL_Gname);
    public function getNBLGname();

    public function setResNPLGname(string $resNPL_Gname);
    public function getResNPLGname(): string;

    public function setTimeNPL(int $timeNPL);
    public function getTimeNPL(): int;

    public function setAction($action);
    public function getAction();

    public function setActionLabel($actionLabel);
    public function getActionLabel();

    public function setLevel(int $level);
    public function getLevel();

    public function setReadyOn($readyOn);
    public function getReadyOn();
}