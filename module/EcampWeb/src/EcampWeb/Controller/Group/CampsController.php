<?php

namespace EcampWeb\Controller\Group;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

class CampsController
    extends BaseController
{

    protected function getUpcomingCampsPaginator()
    {
        $upcomingCamps = $this->getCampRepository()->findUpcomingCamps($this->getGroup());

        $adapter = new ArrayAdapter($upcomingCamps);

        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber(1);

        return $paginator;
    }

    protected function getPastCampsPaginator()
    {
        $upcomingCamps = $this->getCampRepository()->findPastCamps($this->getGroup());

        $adapter = new ArrayAdapter($upcomingCamps);

        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber(1);

        return $paginator;
    }

    public function upcomingCampsAction()
    {
        $page = $this->getRequest()->getQuery('page', 1);

        $paginator = $this->getUpcomingCampsPaginator();
        $paginator->setCurrentPageNumber($page);

        return array('paginator' => $paginator);
    }

    public function pastCampsAction()
    {
        $page = $this->getRequest()->getQuery('page', 1);

        $paginator = $this->getPastCampsPaginator();
        $paginator->setCurrentPageNumber($page);

        return array('paginator' => $paginator);
    }

    public function indexAction()
    {
        $upcomingCampsPaginator = $this->getUpcomingCampsPaginator();
        $pastCampsPaginator = $this->getPastCampsPaginator();

        return array(
            'upcomingCampsPaginator' => $upcomingCampsPaginator,
            'pastCampsPaginator' => $pastCampsPaginator
        );

    }

}
