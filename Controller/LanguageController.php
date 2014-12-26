<?php

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Controller;

use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\LanguageFile;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslation;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Entity\MessageTranslationResponse;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleDefinitions;
use Geschke\Bundle\Admin\TranslatorGUIBundle\Util\LocaleFiles;
use JMS\TranslationBundle\Translation\ConfigBuilder;
use JMS\TranslationBundle\Translation\Loader\XliffLoader;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
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

        $localeFiles = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_files');

        $files = $localeFiles->getLanguages($path);
        $localeFile = null;
        foreach ($files as $localeData) {
            if ($localeData['locale'] == $locale) {
                //echo "locale file found!";
                $localeFile = $localeData;
                break;
            }
        }

       // var_dump($path);
       // var_dump($localeFile);
        $filename = $path . 'Resources/translations/' . $localeFile['file'];
        // todo maybe: load other than xliff files
        // switch format, if xlf or xliff...
        $translator = $this->get('translator');

        //  $handle = fopen($filename,'r');
        $foo = new XliffLoader();
        //$foo = new XliffFileLoader();
        $messages = $foo->load($filename, $locale);


        $reflect = new \ReflectionClass($messages);
  //      $props   = $reflect->getProperties();


      /*  foreach ($props as $prop) {
            print $prop->getName() . "\n";
        }
*/
       // var_dump($props);

$refDomains = $reflect->getProperty('domains');
        $refDomains->setAccessible(true);

        $r = $refDomains->getValue($messages);
        $messageCollection = $r['messages'];
        $messageArray = $messageCollection->all();

        //var_dump($messageArray);
        foreach ($messageArray as $key => $message) {
            $messageKeys[] = $key;
        }
//var_dump($messageArray);
//        var_dump($messageKeys);
//die;
//        $translator->addLoader('xliff', new XliffFileLoader());
//        $translator->addResource('xliff', $filename, $locale);

        $messageTranslation = new MessageTranslation();

        $form = $this->createFormBuilder($messageTranslation)
            ->add('locale', 'hidden')
            ->add('message', 'textarea', array('disabled' => true))
            ->add('translation', 'textarea')

            //  ->add('dueDate', 'date')
           // ->add('save', 'submit', array('label' => $translator->trans("Create new language file")))
            ->getForm();




        return $this->render('GeschkeAdminTranslatorGUIBundle:Language:list.html.twig',
            array(
                'mainnav' => '',
                'bundle' => $bundle,
                'locale' => $locale,
                'messages' => $messageArray,
                'form' => $form->createView(),

            ));


    }

    public function rescanTranslationsAction(Request $request)
    {

        $bundle = $request->get('bundle');
        $locale = $request->get('locale');

        var_dump($bundle);
        var_dump($locale);
        $builder = new ConfigBuilder();
        $this->updateWithInput($bundle, $builder);



//        foreach ($locales as $locale) {
        $config = $builder->setLocale($locale)->getConfig();

        echo sprintf('Keep old translations: <info>%s</info>', $config->isKeepOldMessages() ? 'Yes' : 'No');
        echo sprintf('Output-Path: <info>%s</info>', $config->getTranslationsDir());
        echo sprintf('Directories: <info>%s</info>', implode(', ', $config->getScanDirs()));
        echo  sprintf('Excluded Directories: <info>%s</info>', $config->getExcludedDirs() ? implode(', ', $config->getExcludedDirs()) : '# none #');
        echo sprintf('Excluded Names: <info>%s</info>', $config->getExcludedNames() ? implode(', ', $config->getExcludedNames()) : '# none #');
        echo  sprintf('Output-Format: <info>%s</info>', $config->getOutputFormat() ? $config->getOutputFormat() : '# whatever is present, if nothing then ' . $config->getDefaultOutputFormat() . ' #');
        echo  sprintf('Custom Extractors: <info>%s</info>', $config->getEnabledExtractors() ? implode(', ', array_keys($config->getEnabledExtractors())) : '# none #');

        $updater = $this->container->get('jms_translation.updater');


//        if ($input->getOption('dry-run')) {
            $changeSet = $updater->getChangeSet($config);

            echo 'Added Messages: ' . count($changeSet->getAddedMessages());
                foreach ($changeSet->getAddedMessages() as $message) {
                    echo $message->getId() . '-> ' . $message->getDesc();
                }

            if ($config->isKeepOldMessages()) {
                echo  'Deleted Messages: # none as "Keep Old Translations" is true #';
            } else {
                echo  'Deleted Messages: ' . count($changeSet->getDeletedMessages());
                    foreach ($changeSet->getDeletedMessages() as $message) {
                        echo $message->getId() . '-> ' . $message->getDesc();
                    }

            }



        $updater->process($config);

die;

       // sleep(3);
        $response = new JsonResponse();
        /*
         {
success: false,
error: {
errorcode: "7y7",
displaymsg: "resource not found error"
}
}
         */
        $response->setData(array(
            'success' => true,
            'bundle' => $bundle,
            'locale' => $locale,
            'data' => 123
        ));

        return $response;
    }

        private function updateWithInput($bundleName, ConfigBuilder $builder)
    {
        $kernel = $this->container->get('kernel');

            $bundle = $kernel->getBundle($bundleName);
            $builder->setTranslationsDir($bundle->getPath().'/Resources/translations');
            $builder->setScanDirs(array($bundle->getPath()));

        $outputFormat = 'xliff';
            $builder->setOutputFormat($outputFormat);

        /*if ($input->getOption('ignore-domain')) {
            foreach ($input->getOption('ignore-domain') as $domain) {
                $builder->addIgnoredDomain($domain);
            }
        }*/

/*        if ($input->getOption('domain')) {
            foreach ($input->getOption('domain') as $domain) {
                $builder->addDomain($domain);
            }
        }
*/
  /*      if ($excludeDirs = $input->getOption('exclude-dir')) {
            $builder->setExcludedDirs($excludeDirs);
        }

        if ($excludeNames = $input->getOption('exclude-name')) {
            $builder->setExcludedNames($excludeNames);
        }

        if ($format = $input->getOption('default-output-format')) {
            $builder->setDefaultOutputFormat($format);
        }
*/
 /*       if ($enabledExtractors = $input->getOption('enable-extractor')) {
            foreach ($enabledExtractors as $alias) {
                $builder->enableExtractor($alias);
            }
        }

        if ($disabledExtractors = $input->getOption('disable-extractor')) {
            foreach ($disabledExtractors as $alias) {
                $builder->disableExtractor($alias);
            }
        }

        if ($input->hasParameterOption('--keep') || $input->hasParameterOption('--keep=true')) {
            $builder->setKeepOldTranslations(true);
        } else if ($input->hasParameterOption('--keep=false')) {
            $builder->setKeepOldTranslations(false);
        }
*/
/*        if ($loadResource = $input->getOption('external-translations-dir')) {
            $builder->setLoadResources($loadResource);
        }*/
    }


    public function translateAction(Request $request)
    {
        $bundle = $request->get('bundle');
        $locale = $request->get('locale');
        $messageId = $request->get('messageId');

        // get message from file...
        // fill MessageTranslation object
        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $messageResponse = new MessageTranslationResponse();
        $messageResponse->setLocale($locale);
        $messageResponse->setBundle($bundle);

        $localeMessages = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_messages');

        $messageInstance = $localeMessages->getMessage($bundle, $locale, $messageId);

        if ($messageInstance) {

            $message = new MessageTranslation();
            $message->setLocale($locale);
            $message->setMessage($messageInstance->getSourceString());
            $message->setTranslation($messageInstance->getLocaleString());

            $messageResponse->setSuccess(true);
            $messageResponse->setMessageTranslation($message);

        }
        else {

            $messageResponse->setSuccess(false);
        }
        $jsonContent = $serializer->serialize($messageResponse, 'json');
        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function processTranslateAction(Request $request)
    {
       // var_dump($request->get('locale'));
       // var_dump($request->get('bundle'));
        //var_dump($request->get('message'));
        //var_dump($request->get('translation'));

        $localeMessages = $this->container->get('geschke_bundle_admin_translatorguibundle.locale_messages');

        $success = $localeMessages->updateMessage($request->get('bundle'), $request->get('locale'), $request->get('message'), $request->get('translation'));


        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);


        $messageResponse = new MessageTranslationResponse();
        $messageResponse->setLocale($request->get('locale'));
        $messageResponse->setBundle($request->get('bundle'));
        if ($success) {
            // or get translation from file?
            $message = new MessageTranslation();
            $message->setLocale($request->get('locale'));
            $message->setMessage($request->get('message'));
            $message->setTranslation($request->get('translation'));

            $messageResponse->setSuccess(true);
            $messageResponse->setMessageTranslation($message);

        }
        else {
            $messageResponse->setSuccess(false);
        }

        $jsonContent = $serializer->serialize($messageResponse, 'json');
        $response = new Response();
        $response->setContent($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }

    }
