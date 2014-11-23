<?php

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\LanguageFile;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleDefinitions;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleFiles;
use JMS\TranslationBundle\Translation\Loader\XliffLoader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Loader\XliffFileLoader;

class LanguageController extends Controller
{
    public function listTranslationsAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $locale = $request->get('locale');

        $bundles = $this->container->getParameter('kernel.bundles');

        $kernel = $this->container->get('kernel');

        $path = $kernel->locateResource('@' . $bundle);

        $localeFiles = new LocaleFiles();

        $files = $localeFiles->getLanguages($path);
        $localeFile = null;
        foreach ($files as $localeData) {
            if ($localeData['locale'] == $locale) {
                echo "locale file found!";
                $localeFile = $localeData;
                break;
            }
        }

        var_dump($path);
var_dump($localeFile);
        $filename = $path . 'Resources/translations/' . $localeFile['file'];
        // todo maybe: load other than xliff files
        // switch format, if xlf or xliff...
        $translator = $this->get('translator');

      //  $handle = fopen($filename,'r');
        $foo = new XliffLoader();
        $messages = $foo->load($filename,$locale);
var_dump($messages);
//        $translator->addLoader('xliff', new XliffFileLoader());
//        $translator->addResource('xliff', $filename, $locale);

        die;
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

    public function rescanTranslationsAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $locale = $request->get('locale');
        sleep(3);
        $response = new JsonResponse();
        $response->setData(array(
            'success' => false,

            'bundle' => $bundle,
            'locale' => $locale,
            'data' => 123
        ));

        return $response;
    }


    }