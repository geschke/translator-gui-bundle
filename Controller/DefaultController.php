<?php

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {

        $locale = $request->getLocale();

        $request->setLocale('de');

        $translator =  $this->get('translator');
        $welcome = $translator->trans("Welcome to Translator GUI Bundle!");

        $name = 'foo';
        return $this->render('GeschkeAdminTranslatorGUIBundle:Default:index.html.twig',
            array('mainnav' => '',
                'name' => $name,
            'welcome' => $welcome));
    }

    public function listBundlesAction()
    {
        $bundles = $this->container->getParameter('kernel.bundles');


        $name = 'foo';
        return $this->render('GeschkeAdminTranslatorGUIBundle:Bundles:list.html.twig',
            array('mainnav' => '',
                'bundles' => $bundles,
                'name' => $name));
    }

}
