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

        $translator = $this->get('translator');
        $welcome = $translator->trans("Welcome to Translator GUI Bundle!");

        $name = 'foo';
        return $this->render('GeschkeAdminTranslatorGUIBundle:Default:index.html.twig',
            array(
                'mainnav' => '',
                'name' => $name,
                'welcome' => $welcome
            ));
    }

    public function getLanguages($path)
    {
        $translationfiles = array();
        try {
            $files = new \DirectoryIterator($path . 'Resources/translations/');
            foreach ($files as $fileinfo) {
                if (!$fileinfo->isDot()) {
                    $translationfiles[] = $fileinfo->getFilename();
                }
            }
        }
        catch (\UnexpectedValueException $e) {
            // do nothing, $translationFiles array is empty
        }

        return $translationfiles;
    }

    public function listBundlesAction()
    {
        $bundles = $this->container->getParameter('kernel.bundles');

        $kernel = $this->container->get('kernel');


        foreach ($bundles as $bundle => $bundleFullName) {
            $path = $kernel->locateResource('@' . $bundle);
            $messageFiles = $this->getLanguages($path);
            $bundleList[] = array(
                'name' => $bundle,
                'fullName' => $bundleFullName,
                'path' => $path,
                'messageFiles' => $messageFiles
            );


        }
        asort($bundleList);

        $name = 'foo';
        return $this->render('GeschkeAdminTranslatorGUIBundle:Bundles:list.html.twig',
            array(
                'mainnav' => '',
                'bundles' => $bundleList,
                'name' => $name
            ));
    }

}
