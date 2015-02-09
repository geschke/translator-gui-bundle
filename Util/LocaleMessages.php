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

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use JMS\TranslationBundle\Translation\Loader\XliffLoader;

/**
 * LocaleMessages service 
 */
class LocaleMessages extends ContainerAware
{

    private $kernel;

    /**
     * Constructor
     * 
     * @param type $kernel
     */
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Get real filename with all directory components of a message xliff file. 
     * 
     * @param type $bundle
     * @param type $locale
     * @param type $domain
     * @return string
     * @throws FileNotFoundException
     */
    public function getFilename($bundle, $locale, $domain)
    {
        $path = $this->kernel->locateResource('@' . $bundle);

        $localeFiles = new LocaleFiles($this->kernel);

        $files = $localeFiles->getLanguages($path);
        $localeFile = null;

        foreach ($files as $localeData) {
            if ($localeData['locale'] == $locale && $localeData['domain'] == $domain) {
                $localeFile = $localeData;
                break;
            }
        }
        if (!$localeFile) {
            throw new FileNotFoundException();
        }

        $filename = $path . 'Resources/translations/' . $localeFile['file'];
        return $filename;
    }

    /**
     * Get message object by key
     * 
     * @param type $bundle
     * @param type $locale
     * @param type $messageKey
     * @param type $domain
     * @return bool false or string message
     */
    public function getMessage($bundle, $locale, $messageKey, $domain = 'messages')
    {
        try {
            $filename = $this->getFilename($bundle, $locale, $domain);
        } catch (FileNotFoundException $e) {
            return false;
        }
        // todo maybe: load other than xliff files
        // switch format, if xlf or xliff...

        $loader = new XliffLoader();
        $messages = $loader->load($filename, $locale, $domain);

        $reflect = new \ReflectionClass($messages);

        $refDomains = $reflect->getProperty('domains');
        $refDomains->setAccessible(true);

        $r = $refDomains->getValue($messages);
        $messageCollection = $r[$domain];
        $messageArray = $messageCollection->all();

        if (isset($messageArray[$messageKey])) {
            return $messageArray[$messageKey];
        }
        return false;
    }

    
    /**
     * Update translation string by message key.
     * Currently this method does not throw an exception or responds an error,
     * so the behaviour is very optimistic. 
     * 
     * @param type $bundle
     * @param type $locale
     * @param type $messageKey
     * @param type $translation
     * @param type $domain
     * @return bool true
     */
    public function updateMessage($bundle, $locale, $messageKey, $translation, $domain = 'messages')
    {
        // todo: support another translation formats, not only xliff...
        $container = $this->kernel->getContainer();
        $updater = $container->get('jms_translation.updater');
        $filename = $this->getFilename($bundle, $locale, $domain);
        // todo: check filename, does it exist?

        $updater->updateTranslation(
                $filename, 'xliff', $domain, $locale, $messageKey, $translation
        );
        return true;
    }

}
