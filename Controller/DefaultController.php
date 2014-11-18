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
        try {
            $files = new \DirectoryIterator($path . 'Resources/translations/');
            echo $path . "\n";
            foreach ($files as $fileinfo) {
                if (!$fileinfo->isDot()) {
                    var_dump($fileinfo->getFilename());
                }
            }

        }
        catch (\UnexpectedValueException $e) {
            echo "no entry for $path\n";
        }

        return $foo = true;
    }

    public function listBundlesAction()
    {
        $bundles = $this->container->getParameter('kernel.bundles');

        $kernel = $this->container->get('kernel');


        foreach ($bundles as $bundle => $bundleFullName) {
            $path = $kernel->locateResource('@' . $bundle);
            $this->getLanguages($path);
            $bundleList[] = array(
                'name' => $bundle,
                'fullName' => $bundleFullName,
                'path' => $path
            );


        }
        asort($bundleList);
        die;
        $name = 'foo';
        return $this->render('GeschkeAdminTranslatorGUIBundle:Bundles:list.html.twig',
            array(
                'mainnav' => '',
                'bundles' => $bundleList,
                'name' => $name
            ));
    }

}
