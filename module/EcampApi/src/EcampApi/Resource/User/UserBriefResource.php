<?php
namespace EcampApi\Resource\User;

use PhlyRestfully\HalResource;
use PhlyRestfully\Link;
use EcampCore\Entity\User;

class UserBriefResource extends HalResource
{
    public function __construct(User $user)
    {
        $object = array(
                'id' => $user->getId(),
                'username' 		=> 	$user->getUsername(),
                'fullname'		=>	$user->getFullName(),
                'displayName' 	=>  $user->getDisplayName(),
                'email'			=> 	$user->getEmail()
                );

        parent::__construct($object, $object['id']);

        $selfLink = new Link('self');
        $selfLink->setRoute('api/users', array('user' => $user->getId()));

        $webLink = new Link('web');
        $webLink->setRoute('web', array());

        $this->getLinks()
            ->add($selfLink)
               ->add($webLink);
    }
}
