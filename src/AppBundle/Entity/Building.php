<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Building
 *
 * @ORM\Table(name="building")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BuildingRepository")
 */
class Building implements BuildingInterface
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
     * @var string
     *
     * @ORM\Column(name="id_resGivePH", type="string", length=20)
     */
    private $resGPH_Gname;

    /**
     * @var string
     *
     * @ORM\Column(name="id_needBldngL", type="string", length=20)
     */
    private $NBL_Gname;

    /**
     * @var string
     *
     * @ORM\Column(name="id_resNeedPL", type="string", length=20)
     */
    private $resNPL_Gname;

    /**
     * @var integer
     *
     * @ORM\Column(name="timeNeedPL", type="integer")
     */
    private $timeNPL;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=20)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="action_label", type="string", length=50)
     */
    private $actionLabel;

    /**
     * @var integer
     *
     * @ORM\JoinTable(name="kngdm_bldng", joinColumns={
     *     @ORM\JoinColumn(name="id_building", referencedColumnName="id", fieldName="level")})
     */
    private $level;

    /**
     * @var \DateTime|null|string
     *
     * @ORM\JoinTable(name="kngdm_bldng", joinColumns={
     *     @ORM\JoinColumn(name="id_building", referencedColumnName="id", fieldName="ready_on")})
     */
    private $readyOn;

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
     * @param string|null $resGPH_Gname
     */
    public function setResGPHGname($resGPH_Gname)
    {
        $this->resGPH_Gname = $resGPH_Gname;
    }
    /**
     * @return string|null
     */
    public function getResGPHGname()
    {
        return $this->resGPH_Gname;
    }

    /**
     * @param string|null $NBL_Gname
     */
    public function setNBLGname($NBL_Gname)
    {
        $this->NBL_Gname = $NBL_Gname;
    }
    /**
     * @return string|null
     */
    public function getNBLGname()
    {
        return $this->NBL_Gname;
    }

    /**
     * @param string $resNPL_Gname
     */
    public function setResNPLGname(string $resNPL_Gname)
    {
        $this->resNPL_Gname = $resNPL_Gname;
    }
    /**
     * @return string
     */
    public function getResNPLGname(): string
    {
        return $this->resNPL_Gname;
    }

    /**
     * @param int $timeNPL
     */
    public function setTimeNPL(int $timeNPL)
    {
        $this->timeNPL = $timeNPL;
    }
    /**
     * @return int
     */
    public function getTimeNPL(): int
    {
        return $this->timeNPL;
    }

    /**
     * @param string|null $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    /**
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string|null $actionLabel
     */
    public function setActionLabel($actionLabel)
    {
        $this->actionLabel = $actionLabel;
    }
    /**
     * @return string|null
     */
    public function getActionLabel()
    {
        return $this->actionLabel;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }
    /**
     * @return int|null
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param \DateTime|null|string $readyOn
     */
    public function setReadyOn($readyOn)
    {
        $this->readyOn = $readyOn;
    }
    /**
     * @return \DateTime|null|string
     */
    public function getReadyOn()
    {
        return $this->readyOn;
    }
}