<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Building
 *
 * @ORM\Table(name="unit")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UnitRepository")
 */
class Unit implements UnitInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=20, unique=true)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="attack", type="float")
     */
    private $attack;

    /**
     * @var float
     *
     * @ORM\Column(name="live", type="float")
     */
    private $live;

    /**
     * @var string
     *
     * @ORM\Column(name="id_needBldngL", type="string", length=20)
     */
    private $NBL_Gname;

    /**
     * @var string
     *
     * @ORM\Column(name="id_resNeedPU", type="string", length=20)
     */
    private $resNPU_Gname;

    /**
     * @var int
     *
     * @ORM\Column(name="timePU", type="integer")
     */
    private $timePU;

    /**
     * @var integer
     *
     * @ORM\JoinTable(name="kngdm_unit", joinColumns={
     *     @ORM\JoinColumn(name="id_unit", referencedColumnName="id", fieldName="count")})
     */
    private $count;

    /**
     * @var integer
     *
     * @ORM\JoinTable(name="kngdm_unit", joinColumns={
     *     @ORM\JoinColumn(name="id_unit", referencedColumnName="id", fieldName="order_count")})
     */
    private $orderCount;

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param float $attack
     */
    public function setAttack(float $attack)
    {
        $this->attack = $attack;
    }
    /**
     * @return float
     */
    public function getAttack(): float
    {
        return $this->attack;
    }

    /**
     * @param float $live
     */
    public function setLive(float $live)
    {
        $this->live = $live;
    }
    /**
     * @return float
     */
    public function getLive(): float
    {
        return $this->live;
    }

    /**
     * @param string $NBL_Gname
     */
    public function setNBLGname(string $NBL_Gname)
    {
        $this->NBL_Gname = $NBL_Gname;
    }
    /**
     * @return string
     */
    public function getNBLGname(): string
    {
        return $this->NBL_Gname;
    }

    /**
     * @param string $resNPU_Gname
     */
    public function setResNPUGname(string $resNPU_Gname)
    {
        $this->resNPU_Gname = $resNPU_Gname;
    }
    /**
     * @return string
     */
    public function getResNPUGname(): string
    {
        return $this->resNPU_Gname;
    }

    /**
     * @param int $timePU
     */
    public function setTimePU(int $timePU)
    {
        $this->timePU = $timePU;
    }
    /**
     * @return int
     */
    public function getTimePU(): int
    {
        return $this->timePU;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count)
    {
        $this->count = $count;
    }
    /**
     * @return int|null
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $orderCount
     */
    public function setOrderCount(int $orderCount)
    {
        $this->orderCount = $orderCount;
    }
    /**
     * @return int
     */
    public function getOrderCount(): int
    {
        return $this->orderCount;
    }
}