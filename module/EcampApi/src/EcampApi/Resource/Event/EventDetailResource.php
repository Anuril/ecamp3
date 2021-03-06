<?php
namespace EcampApi\Resource\Event;

use PhlyRestfully\HalResource;
use PhlyRestfully\Link;
use EcampCore\Entity\Event as Event;
use EcampApi\Resource\EventCategory\EventCategoryBriefResource;
use EcampApi\Resource\Camp\CampBriefResource;

class EventDetailResource extends HalResource
{
    public function __construct(Event $entity)
    {
        $object = array(
            'id'			=> 	$entity->getId(),
            'categoryId'    =>  $entity->getEventCategory()->getId(),
            'title'			=> 	$entity->getTitle(),
            'camp'			=>	new CampBriefResource($entity->getCamp()),
            'category'		=>  new EventCategoryBriefResource($entity->getEventCategory())
        );

        parent::__construct($object, $object['id']);

        $selfLink = new Link('self');
        $selfLink->setRoute('api/events', array('event' => $entity->getId()));

        $webLink = new Link('web');
        $webLink->setRoute(
            'web/camp/default',
            array(
                'camp' => $entity->getCamp(),
                'controller' => 'event',
                'action' => 'index'
            ),
            array(
                'query' => array( 'eventId' => $entity->getId())
            )
        );

        $eventInstanceLink = new Link('event_instances');
        $eventInstanceLink->setRoute('api/events/event_instances', array('event' => $entity->getId()));

        $eventRespLink = new Link('event_resps');
        $eventRespLink->setRoute('api/events/event_resps', array('event' => $entity->getId()));

        $this->getLinks()
            ->add($selfLink)
            ->add($webLink)
            ->add($eventInstanceLink)
            ->add($eventRespLink);

    }
}
