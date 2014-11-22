<?php
/**
 * Created by PhpStorm.
 * User: geschke
 * Date: 22.11.2014
 * Time: 17:17
 */

namespace Geschke\Bundle\Admin\TranslatorGUIBundle\Util;

class LocaleFiles
{


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
}