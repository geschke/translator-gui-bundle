<?php

/*
 * Copyright 2015 Ralf Geschke <ralf@kuerbis.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Util;

use JMS\TranslationBundle\Translation\ConfigBuilder;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Collection of message file handling functions
 */
class LocaleFiles
{

    private $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Get array of message translation files in folder
     * 
     * @param string $path
     * @return array
     */
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

    /**
     * Rescan, i.e. check and build translation message file of a bundle
     * 
     * @param string $bundleName
     * @param string $locale
     * @return bool
     */
    public function rescanMessageFile($bundleName, $locale)
    {
        try {
            $builder = new ConfigBuilder();
            $this->updateWithInput($bundleName, $builder);

            $config = $builder->setLocale($locale)->getConfig();

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
        } catch (ContextErrorException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set default values to handle rescan process
     * 
     * @param string $bundleName
     * @param ConfigBuilder $builder
     * @return boole
     */
    private function updateWithInput($bundleName, ConfigBuilder $builder)
    {
        $bundle = $this->kernel->getBundle($bundleName);
        $builder->setTranslationsDir($bundle->getPath() . '/Resources/translations');
        $builder->setScanDirs(array($bundle->getPath()));

        $outputFormat = 'xliff';
        $builder->setOutputFormat($outputFormat);
        return true;
    }

    /**
     * Delete all message translation files which belong to a bundle and locale
     * 
     * @param string $bundleName
     * @param string $locale
     * @return bool
     */
    public function deleteMessageFile($bundleName, $locale)
    {
        $filesError = $filesDeleted = array();

        $path = $this->kernel->locateResource('@' . $bundleName);

        $fs = new Filesystem();

        $files = $this->getLanguages($path);

        $localeFile = null;
        foreach ($files as $localeData) {
            if ($localeData['locale'] == $locale) {
                $localeFile = $localeData['file'];
                try {
                    $completeFile = $path . 'Resources/translations/' . $localeFile;
                    $fs->remove($completeFile);
                    $filesDeleted[] = $localeFile;
                } catch (IOExceptionInterface $e) {
                    $filesError[] = $localeFile;
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

    /**
     * Copy translation messages file
     * 
     * @param string $bundleName
     * @param string $domain
     * @param string $localeFrom
     * @param string $localeTo
     * @return bool
     */
    public function copyMessageFile($bundleName, $domain, $localeFrom, $localeTo)
    {
        $filesError = $filesCopied = array();

        $path = $this->kernel->locateResource('@' . $bundleName);

        $fs = new Filesystem();

        $files = $this->getLanguages($path);

        $localeFile = null;

        foreach ($files as $localeData) {
            if ($localeData['locale'] == $localeFrom) {

                $localeFile = $localeData['file'];
                try {
                    $completeFileSource = $path . 'Resources/translations/' . $localeFile;
                    $completeFileTarget = $path . 'Resources/translations/' . $localeData['domain'] . '.' . $localeTo . '.' . $localeData['format'];

                    $fs->copy($completeFileSource, $completeFileTarget);

                    $filesCopied[] = $localeFile;
                } catch (IOExceptionInterface $e) {
                    $filesError[] = $localeFile;
                }
            }
        }
        // maybe todo: rescan after copy, but currently not necessary because if a message is written at first, the target language in the xliff file will be overwritten
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
