<?php
/*
 * Copyright (C) 2011 Urban Suppiger
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

namespace CoreApi\Entity;

/**
 * Specifies the exact time/duration/subcamp when an event happens
 * @Entity
 * @Table(name="event_instances")
 */
class EventInstance extends BaseEntity
{
	
	/**
	 * @var int
	 * @Id @Column(type="integer")
	 * @GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ManyToOne(targetEntity="Event")
	 * @JoinColumn(nullable=false)
	 */
	private $event;

	/**
	 * Offset in minutes from the subcamp's starting date (00:00)
	 * @Column(type="integer", nullable=false)
	 */
	private $minOffset;

	/**
	 * Duration of this instance in minutes
	 * @Column(type="integer", nullable=false)
	 */
	private $duration;

	/**
	 * @ManyToOne(targetEntity="Period")
	 * @JoinColumn(nullable=false)
	 */
	private $period;

	
	public function getId()
	{
		return $this->id;
	}

	
	/**
	 * @param Event $event
	 */
	public function __construct(Event $event)
	{
		$this->event = $event;
	}
	
	
	/**
	 * @return Event 
	 */
	public function getEvent()
	{
		return $this->event;
	}

	
	public function setMinOffset($minOffset)
	{
		$this->minOffset = $minOffset;
	}
	
	
	/**
	 * @return int
	 */
	public function getMinOffset()
	{
		return $this->minOffset;
	}

	
	/**
	 * @param DateInterval|int $duration
	 */
	public function setDuration($duration)
	{
		if($duration instanceof \DateInterval){
			$duration = 
				$duration->format('a') * 24 * 60 +
				$duration->format('h') * 60 + 
				$duration->format('i'); 
		}
		
		$this->duration = $duration;
	}
	
	
	/**
	 * @return DateInterval
	 */
	public function getDuration()
	{
		return new \DateInterval( 'PT' . $this->duration . 'M');
	}
	
	
	/**
	 * @return int
	 */
	public function getDurationInMinutes()
	{
		return $this->duration;
	}

	
	/**
	 * @return DateTime
	 */
	public function getStartTime()
	{
		$start = $this->period->getStart() + new \DateInterval( 'PT' . $this->getMinOffset() . 'M');
		return $start; 
	}
	
	
	/**
	 * @return DateTime
	 */
	public function getEndTime()
	{
		$end = $this->getStartTime() + $this->duration;
		return $end;
	}
	
	
	/**
	 * @param Period $period
	 */
	public function setPeriod(Period $period)
	{
		$this->period = $period;
	}
	
	
	/**
	 * @return Period 
	 */
	public function getPeriod()
	{
		return $this->period;
	}

}
