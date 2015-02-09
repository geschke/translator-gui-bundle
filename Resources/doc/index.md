# GeschkeAdminTranslatorGUIBundle

## Introduction

This bundle builds a nice GUI to help the translation process of Symfony
bundles. The bundle uses the JMSTranslationBundle as its underlaying 
structure for writing the language files. In contrast to other similar
bundles, there is no requirement for a third-party storage component like
a database. 


## Requirements:

- JMSTranslationBundle `>=1.1` (will be installed by requirement section)

## Installation and configuration

Add the package definition to your composer.json:

```json
 "require": {
    ...
        "geschke/translator-gui-bundle": "dev-master"
    ...
    },

```

Put the routing information in your routing.yml or in the routing_dev.yml (preferred) file:

```yaml

geschke_admin_translator_gui:
    resource: "@GeschkeAdminTranslatorGUIBundle/Resources/config/routing.yml"
    prefix:   /_transgui

```

**If you want to run the TranslatorGUIBundle in your productive environment, be sure that it's secured by Symfony2 firewall authentication! **

Add the TranslationGUIBundle to your application's kernel:

``` php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new JMS\TranslationBundle\JMSTranslationBundle(),
        new Geschke\Bundle\Admin\TranslatorGUIBundle\GeschkeAdminTranslatorGUIBundle(),
        // ...
    );
    ...
}
```

Or if you want to use in development environment only, use the bundles array below:


``` php
<?php
public function registerBundles()
{
 if (in_array($this->getEnvironment(), array('dev', 'test'))) {
        // ...

        $bundles[] = new JMS\TranslationBundle\JMSTranslationBundle();
        $bundles[] = new Geschke\Bundle\Admin\TranslatorGUIBundle\GeschkeAdminTranslatorGUIBundle(),
        // ...
    }
    ...
}
```

Enable the Symfony2 translator component in your configuration:

``` yaml
# app/config/config.yml
framework:
    translator: { fallback: en }

```

Run composer update

``` sh
composer update
```

The composer run installs the assets like images, CSS and JavaScript files into the document root folder. If you did run composer update before, you can use the following CLI command to install the assets:

``` sh
php app/console assets:install

```

Go to the URL /_transgui/ , e.g. if you're using the Symfony2 integrated development webserver http://localhost:8000/_transgui/ .

**Be sure that the translation paths of your application is writable by the webserver user!**


## Usage

After passing the welcome screen you will see a list of installed bundles of your Symfony2 application. As example you can scroll down to the GeschkeAdminTranslatorGUIBundle section. 
The flags visualize the available language files. If you want to add another translation, press the "Add new language" button. You can choose a locale definition or add a custom locale string at the bottom of the formular. 

In the bundle list page you see the list of message files and their action buttons:

- Edit: see below
- Rescan: Rescan the source code and extract translatable items. This is needed if you have modified, i.e. added or deleted the translation message in the bundle. 
- Copy: Copy the message files of a locale to another. You can choose the target locale in a similar way of adding new languages. 
- Delete: Deletes *all* message files of a locale. 

### Edit:
This button opens the list of message to be translated. In the first column there is the original data, in the second the current translation. At last, the edit button show a modal dialog to submit the translation text. The "message reference" helps to build the translation, especially if you're using message keys instead of message texts as recommended in the Symfony2 Best Practices. The chosen reference language is stored in the session, so you don't need to choose it multiple times. 

## Restrictions and Todo list

- TranslatorGUIBundle scans the translation contained in your bundle directories only. It does not regard another message files in your app folder. This feature will be added as soon as possible. 
- Currently the TranslatorGUIBundle supports the recommended XLIFF format only. In the future maybe support for another formats will be added. 

- Do some more code cleanup, make the Controller methods smaller...
- Add some locales to this bundle.
- Show status information (number of messages, percentage of translations and so on)
- ...and so on...

## Thanks to...

- Johannes M. Schmitt for developing the great JMSTranslationBundle
- WordPress Codestyling Localization plugin for inspiration and collection of locale definitions

## License

The code is released under the business-friendly `Apache2 license`_. 


.. _Apache2 license: http://www.apache.org/licenses/LICENSE-2.0.html
