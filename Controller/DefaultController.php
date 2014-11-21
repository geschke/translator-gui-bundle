<?php

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\LanguageFile;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleDefinitions;
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
        $translationFiles = array();
        try {
            $files = new \DirectoryIterator($path . 'Resources/translations/');
            foreach ($files as $fileinfo) {
                if (!$fileinfo->isDot()) {
                    $translationFiles[]['file'] = $fileinfo->getFilename();
                }
            }
        }
        catch (\UnexpectedValueException $e) {
            // do nothing, $translationFiles array is empty
        }
        if ($number = count($translationFiles)) {
            for ($i = 0; $i < $number; $i++) {
                $matched = preg_match('/^(.*)\.(.*)\.(.*)$/', strtolower($translationFiles[$i]['file']), $ma);
                if ($matched) {
                    if (isset($ma[3])) {
                        $format = $ma[3];
                        $translationFiles[$i]['format'] = $format;
                    }
                    if (isset($ma[2])) {
                        $locale = $ma[2];
                        $translationFiles[$i]['locale'] = $locale;
                    }
                }
            }
        }

        return $translationFiles;
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

    public function addLanguageAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $translator = $this->get('translator');

        $languageFile = new LanguageFile();
        $languageFile->setBundle($bundle);

        $locales = LocaleDefinitions::$csp_l10n_sys_locales;

        $assets = $this->container->get('templating.helper.assets');

        $baseUrl = $assets->getUrl('bundles/geschkeadmintranslatorgui/images/flags/');


        foreach ($locales as $locale => $localeData ) {
            $choices[$locale] = '<img src="' . $baseUrl . $localeData['country-www']. '.gif" alt="locale: ' . $locale . '" /> ' . $locale . ' ' . $localeData['lang-native'] ;
        }

        $form = $this->createFormBuilder($languageFile)
            ->add('bundle', 'hidden')
            ->add('locale', 'choice', array(
                'label'     => $translator->trans("Choose language"),
                'required'  => true,
                'expanded' => true,
                'choices' => $choices
            ))
          //  ->add('dueDate', 'date')
            ->add('save', 'submit', array('label' => $translator->trans("Create new language file")))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {

            echo "form submitted";
            die;
            return $this->redirect($this->generateUrl('task_success'));
        }

        return $this->render('GeschkeAdminTranslatorGUIBundle:Default:language-add.html.twig',
            array(
                'mainnav' => '',
                'form' => $form->createView(),
                'bundle' => $bundle,
                'languages' => $locales
            ));


    }

}
