<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Kingdom
 *
 * @ORM\Table(name="kingdom",uniqueConstraints={@UniqueConstraint(name="coordinateX_coordinateY",columns={"coordinateX","coordinateY"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KingdomRepository")
 */
class Kingdom implements KingdomInterface
{
    const MIN_COORDINATE_X = -100;
    const MAX_COORDINATE_X = 100;
    const MIN_COORDINATE_Y = -100;
    const MAX_COORDINATE_Y = 100;

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
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="coordinateX", type="integer")
     */
    private $coordinateX=null;

    /**
     * @var integer
     *
     * @ORM\Column(name="coordinateY", type="integer")
     */
    private $coordinateY=null;

    /**
     * @var integer
     *
     * @ORM\Column(name="pop_count", type="integer")
     */
    private $populationCounter=0;

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
     * @param int $coordinateX
     */
    public function setCoordinateX(int $coordinateX)
    {
        $this->coordinateX = $coordinateX;
    }
    /**
     * @return int
     */
    public function getCoordinateX(): int
    {
        return $this->coordinateX;
    }

    /**
     * @param int $coordinateY
     */
    public function setCoordinateY(int $coordinateY)
    {
        $this->coordinateY = $coordinateY;
    }
    /**
     * @return int
     */
    public function getCoordinateY(): int
    {
        return $this->coordinateY;
    }

    /**
     * @param int $populationCounter
     */
    public function setPopulationCounter(int $populationCounter)
    {
        $this->populationCounter = $populationCounter;
    }
    /**
     * @return int
     */
    public function getPopulationCounter(): int
    {
        return $this->populationCounter;
    }
}