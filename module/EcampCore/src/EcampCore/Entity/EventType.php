<?php
/*
 * Copyright (C) 2012 Urban Suppiger
 *
 * This file is part of eCamp.
 *
 * eCamp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * eCamp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with eCamp.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace EcampCore\Entity;

use OutOfRangeException;
use Doctrine\ORM\Mapping as ORM;

use EcampLib\Entity\BaseEntity;

/**
 * EventType
 * @ORM\Entity(repositoryClass="EcampCore\Repository\EventTypeRepository")
 * @ORM\Table(name="event_types")
 * @ORM\HasLifecycleCallbacks
 */
class EventType extends BaseEntity
{

    public function __construct($name, $defaultColor, $defaultNumberingStyle)
    {
        parent::__construct();

        $this->name = $name;
        $this->defaultColor = $defaultColor;
        $this->defaultNumberingStyle = $defaultNumberingStyle;

        $this->campTypes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventTypePlugins = new \Doctrine\Common\Collections\ArrayCollection();
        $this->eventTypeFactories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=8, nullable=false)
     */
    private $defaultColor;

    /**
     * @ORM\Column(type="string", length=1, nullable=false)
     */
    private $defaultNumberingStyle;

    /**
     * @ORM\ManyToMany(targetEntity="CampType")
     * @ORM\JoinTable(name="camp_type_event_type",
     *      joinColumns={@ORM\JoinColumn(name="eventtype_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="camptype_id", referencedColumnName="id")}
     *      )
     */
    protected $campTypes;

    /**
     * @ORM\OneToMany(targetEntity="EventTypePlugin", mappedBy="eventType")
     */
    protected $eventTypePlugins;

    /**
     * @ORM\OneToMany(targetEntity="EventTypeFactory", mappedBy="eventType")
     */
    protected $eventTypeFactories;

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $color
     */
    public function setDefaultColor($color)
    {
        $this->defaultColor = $color;
    }

    /**
     * @return string
     */
    public function getDefaultColor()
    {
        return $this->defaultColor;
    }

    /**
     * @param  string              $numberingStyle
     * @throws OutOfRangeException
     */
    public function setDefaultNumberingStyle($numberingStyle)
    {
        $allowed = array('1', 'a', 'A', 'i', 'I');
        if (in_array($numberingStyle, $allowed)) {
            $this->defaultNumberingStyle = $numberingStyle;
        } else {
            throw new OutOfRangeException("Unknown NumberingStyle");
        }
    }

    /**
     * @return string
     */
    public function getDefaultNumberingStyle()
    {
        return $this->defaultNumberingStyle;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCampTypes()
    {
        return $this->campTypes;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEventTypePlugins()
    {
        return $this->eventTypePlugins;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEventTypeFactories()
    {
        return $this->eventTypeFactories;
    }
}
