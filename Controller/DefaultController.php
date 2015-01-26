<?php

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\LanguageFile;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Form\Type\LanguageChoiceType;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleDefinitions;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleFiles;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {

        $translator = $this->get('translator');
        $welcome = $translator->trans("Welcome to Translator GUI Bundle!");

       return $this->render('GeschkeAdminTranslatorGUIBundle:Default:index.html.twig',
            array(
                'mainnav' => 'index',
                'welcome' => $welcome
            ));
    }



    public function listBundlesAction()
    {
        $bundles = $this->container->getParameter('kernel.bundles');

        $kernel = $this->container->get('kernel');

        $translator = $this->get('translator');

        $csp_l10n_sys_locales = LocaleDefinitions::$csp_l10n_sys_locales;
        $csp_l10n_langs = LocaleDefinitions::$csp_l10n_langs;

        $localeFiles = new LocaleFiles($kernel);

        foreach ($bundles as $bundle => $bundleFullName) {
            $path = $kernel->locateResource('@' . $bundle);
            $messageFiles = $localeFiles->getLanguages($path);
            for ($i = 0; $i < count($messageFiles); $i++) {
                //$messageFiles[$i]['additional'] = array();

                if (!isset($csp_l10n_sys_locales[$messageFiles[$i]['locale']]) &&
                !isset($csp_l10n_langs[$messageFiles[$i]['locale']])) {
                    $messageFiles[$i]['unknown'] = true;
                }
                if (strlen($messageFiles[$i]['locale']) > 2) {
                    if (isset($csp_l10n_sys_locales[$messageFiles[$i]['locale']])) {
                        $messageFiles[$i]['additional'] = $csp_l10n_sys_locales[$messageFiles[$i]['locale']];
                    }
                }
            }
            $bundleList[] = array(
                'name' => $bundle,
                'fullName' => $bundleFullName,

                'path' => $path,
                'messageFiles' => $messageFiles
            );
        }


        asort($bundleList);

        $languageFile = new LanguageFile();
        //$form = $this->createForm('languagechoice', $languageFile);
        $form = $this->createForm(
            new LanguageChoiceType($this->container, $translator, false)
        );


        return $this->render('GeschkeAdminTranslatorGUIBundle:Bundles:list.html.twig',
            array(
                'mainnav' => 'bundles',
                'bundles' => $bundleList,
                'form_copy' => $form->createView()

            ));
    }

    public function addLanguageAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $translator = $this->get('translator');

        $languageFile = new LanguageFile();
        $languageFile->setBundle($bundle);

        $form = $this->createForm('languagechoice', $languageFile);

        $form->handleRequest($request);
        if ($form->isValid()) {


//            $locale = $form->get('locale')->getData();
            $locale = $languageFile->getLocale();
            $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');




            $result = $localeFiles->rescanMessageFile($bundle, $locale);


            if ($result === false) {
                $this->get('session')->getFlashBag()->add(
                    'message_error',
                    $translator->trans('Error by creating language file.')
                );
            }
            elseif ($result === 0) {
                $this->get('session')->getFlashBag()->add(
                    'message_warning',
                    $translator->trans('No messages found. The language file was not created.')
                );
            } else {
                // result > 0
                $this->get('session')->getFlashBag()->add(
                    'message_success',
                    'The language file was created successfully.'
                );
            }


            return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));
        }

        return $this->render('GeschkeAdminTranslatorGUIBundle:Default:language-add.html.twig',
            array(
                'mainnav' => '',
                'form' => $form->createView(),
                'bundle' => $bundle,
              //  'languages' => $localesFull
            ));
    }

    public function deleteLanguageAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $locale = $request->get('locale');

        $translator = $this->get('translator');

        $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');
        $result = $localeFiles->deleteMessageFile($bundle, $locale);


        if ($result) {
            $this->get('session')->getFlashBag()->add(
                'message_success',
                $translator->trans('The language file was deleted successfully.')
            );

        } else {
            $this->get('session')->getFlashBag()->add(
                'message_error',
                $translator->trans('Error by deleting language file.')
            );
        }

        return $this->redirect($this->generateUrl('geschke_admin_translator_gui_bundles'));

    }

    }
