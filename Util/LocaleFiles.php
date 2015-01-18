<?php
/**
 * Created by PhpStorm.
 * User: geschke
 * Date: 22.11.2014
 * Time: 17:17
 */

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Util;

use JMS\TranslationBundle\Translation\ConfigBuilder;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class LocaleFiles
{

    private $kernel;

    //  private $translator;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
//        $this->translator = $translator;
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
        } catch (\UnexpectedValueException $e) {
            // do nothing, $translationFiles array is empty
        }
        if ($number = count($translationFiles)) {
            for ($i = 0; $i < $number; $i++) {
                $matched = preg_match('/^(.*)\.(.*)\.(.*)$/', $translationFiles[$i]['file'], $ma);
                if ($matched) {
                    if (isset($ma[3])) {
                        $format = $ma[3];
                        $translationFiles[$i]['format'] = $format;
                    }
                    if (isset($ma[2])) {
                        $locale = $ma[2];
                        $translationFiles[$i]['locale'] = $locale;
                    }
                    if (isset($ma[1])) {
                        $domain = $ma[1];
                        $translationFiles[$i]['domain'] = $domain;
                    }

                }
            }
        }

        return $translationFiles;
    }

    public function rescanMessageFile($bundleName, $locale)
    {
        try {
            $builder = new ConfigBuilder();
            $this->updateWithInput($bundleName, $builder);

            $config = $builder->setLocale($locale)->getConfig();
//var_dump($config);

            /* echo sprintf('Keep old translations: <info>%s</info>', $config->isKeepOldMessages() ? 'Yes' : 'No');
             echo sprintf('Output-Path: <info>%s</info>', $config->getTranslationsDir());
             echo sprintf('Directories: <info>%s</info>', implode(', ', $config->getScanDirs()));
             echo  sprintf('Excluded Directories: <info>%s</info>', $config->getExcludedDirs() ? implode(', ', $config->getExcludedDirs()) : '# none #');
             echo sprintf('Excluded Names: <info>%s</info>', $config->getExcludedNames() ? implode(', ', $config->getExcludedNames()) : '# none #');
             echo  sprintf('Output-Format: <info>%s</info>', $config->getOutputFormat() ? $config->getOutputFormat() : '# whatever is present, if nothing then ' . $config->getDefaultOutputFormat() . ' #');
             echo  sprintf('Custom Extractors: <info>%s</info>', $config->getEnabledExtractors() ? implode(', ', array_keys($config->getEnabledExtractors())) : '# none #');
     */
            $container = $this->kernel->getContainer();


            $updater = $container->get('jms_translation.updater');

//        if ($input->getOption('dry-run')) {
            $changeSet = $updater->getChangeSet($config);

            $addedMessages = count($changeSet->getAddedMessages());
            //foreach ($changeSet->getAddedMessages() as $message) {
            //    echo $message->getId() . '-> ' . $message->getDesc();
            //}

            //if (!$config->isKeepOldMessages()) {
                //echo 'Deleted Messages: ' . count($changeSet->getDeletedMessages());
            //    foreach ($changeSet->getDeletedMessages() as $message) {
                    //echo $message->getId() . '-> ' . $message->getDesc();
            //    }

            //}


            $updater->process($config);
            return $addedMessages;
        }
        catch (ContextErrorException $e) {
            return false;
        }
        catch (\Exception $e) {
            return false;
        }

    }

    private function updateWithInput($bundleName, ConfigBuilder $builder)
    {

        $bundle = $this->kernel->getBundle($bundleName);
        $builder->setTranslationsDir($bundle->getPath().'/Resources/translations');
        $builder->setScanDirs(array($bundle->getPath()));

        $outputFormat = 'xliff';
        $builder->setOutputFormat($outputFormat);
        return true;
    }

    public function deleteMessageFile($bundleName, $locale)
    {
        $filesError = $filesDeleted = array();


        $path = $this->kernel->locateResource('@' . $bundleName);

        $fs = new Filesystem();

        $files = $this->getLanguages($path);

        $localeFile = null;
        foreach ($files as $localeData) {
            if ($localeData['locale'] == $locale) {
                //echo "locale file found! with $locale<br>";
                $localeFile = $localeData['file'];
                try {
                    $completeFile = $path . 'Resources/translations/' . $localeFile;
                   // echo $completeFile;
                    $fs->remove($completeFile);
                   // echo "should be deleted!";
                    $filesDeleted[] = $localeFile;
                } catch (IOExceptionInterface $e) {
                    $filesError[] = $localeFile;
                 // echo "An error occurred while creating your directory at ".$e->getPath();
                }
           }
        }
        if ($localeFile === null) {
            return false;
        }
        // todo maybe in future: get more information about failed deletion
        if (count($filesError)) {
            return false;
        }
      return true;

    }

}