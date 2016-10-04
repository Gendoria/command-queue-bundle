# Command Queue Bundle

[![Build Status](https://img.shields.io/travis/Gendoria/command-queue-bundle/master.svg)](https://travis-ci.org/Gendoria/command-queue-bundle)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/Gendoria/command-queue-bundle.svg)](https://scrutinizer-ci.com/g/Gendoria/command-queue-bundle/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Gendoria/command-queue-bundle.svg)](https://scrutinizer-ci.com/g/Gendoria/command-queue-bundle/?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/gendoria/command-queue-bundle.svg)](https://packagist.org/packages/gendoria/command-queue-bundle)
[![Latest Stable Version](https://img.shields.io/packagist/v/gendoria/command-queue-bundle.svg)](https://packagist.org/packages/gendoria/command-queue-bundle)

Bundle created in cooperation with [Isobar Poland](http://www.isobar.com/pl/).

![Isobar Poland](doc/images/isobar.jpg "Isobar Poland logo") 

# Installation

## Step 1: Download the Bundle


Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require gendoria/command-queue-bundle "dev-master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Step 2: Enable the Bundle


Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Gendoria\CommandQueueBundle\GendoriaCommandQueueBundle(),
        );

        // ...
    }

    // ...
}
```

## Step 3: Add bundle configuration

To be able to use the bundle, you have to add correct configuration in your `app/config/config.yml`.

<Documentation in progress>