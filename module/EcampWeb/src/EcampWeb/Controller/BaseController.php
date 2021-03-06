<?php

namespace EcampWeb\Controller;

use EcampCore\Controller\AbstractBaseController;
use EcampCore\Entity\User;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

/**
 * Class BaseController
 * @method Request getRequest()
 * @method Response getResponse()
 */
abstract class BaseController
    extends AbstractBaseController
{
    private $fullHeight = false;

    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);

        $events->attach('dispatch', function($e) { $this->setMeInViewModel($e); } , -100);
        $events->attach('dispatch', function($e) { $this->setConfigInViewModel($e); } , -100);
        $events->attach('dispatch', function($e) { $this->setLayoutBodyClass($e); }, -100);
    }

    public function onDispatch(MvcEvent $e)
    {
        $locale = $this->params()->fromRoute('locale', 'en');
        $this->getTranslator()->setLocale($locale);

        $textDomain = $this->params()->fromRoute('module', 'default');

        $translateHelper = $this->getServiceLocator()->get('viewhelpermanager')->get('Translate');
        $translateHelper->setTranslatorTextDomain($textDomain);

        $translateHelper = $this->getServiceLocator()->get('viewhelpermanager')->get('TranslatePlural');
        $translateHelper->setTranslatorTextDomain($textDomain);

        $translateHelper = $this->getServiceLocator()->get('viewhelpermanager')->get('FormRow');
        $translateHelper->setTranslatorTextDomain($textDomain);

        $translateHelper = $this->getServiceLocator()->get('viewhelpermanager')->get('FormLabel');
        $translateHelper->setTranslatorTextDomain($textDomain);

        $this->getServiceLocator()->get('Twig_Environment')->getExtension('core')->setDateFormat('d.m.Y');

        parent::onDispatch($e);
    }

    /**
     * @param MvcEvent $e
     */
    private function setMeInViewModel(MvcEvent $e)
    {
        $result = $e->getResult();

        if ($result instanceof ViewModel) {
            $me = $this->getMe() ?: User::ROLE_GUEST;
            $acl = $this->getServiceLocator()->get('EcampLib\Acl');

            $result->setVariable('me', $me);
            $result->setVariable('acl', $acl);
        }
    }

     /**
     * @param MvcEvent $e
     */
    private function setConfigInViewModel(MvcEvent $e)
    {
        $result = $e->getResult();

        if ($result instanceof ViewModel) {
            $config = $this->getServiceLocator()->get('config');

            $result->setVariable('config', $config['ecamp']);
        }
    }

    private function setLayoutBodyClass(MvcEvent $e)
    {
        $result = $e->getResult();

        if ($result instanceof ViewModel) {
            $layoutBodyClass = $this->fullHeight ? 'full-height' : '';

            $result->setVariable('layoutBodyClass', $layoutBodyClass);
        }
    }

    /**
     * @return \Zend\I18n\Translator\Translator
     */
    protected function getTranslator()
    {
        /** @var \Zend\Mvc\I18n\Translator $mvcTranslator */
        $mvcTranslator = $this->getServiceLocator()->get('MvcTranslator');

        return $mvcTranslator->getTranslator();
    }

    protected function getRedirectResponse($url)
    {
        /** @var $renderer \Zend\View\Renderer\RendererInterface */
        $renderer = $this->getServiceLocator()->get('ZfcTwigRenderer');

        $viewModel = new ViewModel();
        $viewModel->setTemplate('ecamp-web/redirect');
        $viewModel->setVariable('url', $url);

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setContent($renderer->render($viewModel));

        return $response;
    }

    protected function setFullHeight($fullHeight = true)
    {
        $this->fullHeight = $fullHeight;
    }

}
