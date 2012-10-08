MisdRavenBundle
===============

This bundle contains a Symfony2 authentication provider so that users can log in to a Symfony2 application through [Raven](http://raven.cam.ac.uk/), the University of Cambridge's central authentication service.

It also contains a user provider, which can allow any user authenticating through Raven access to your application.

Authors
-------

* Chris Wilkinson <chris.wilkinson@admin.cam.ac.uk>

The bundle uses code based on the [UcamWebauth PHP class](https://wiki.cam.ac.uk/raven/PHP_library).

Requirements
------------

* Symfony 2.1
* [PHP OpenSSL library](http://www.php.net/manual/en/book.openssl.php)

Installation
------------

 1. Add the bundle to your dependencies:

        // composer.json

        {
           // ...
           "require": {
               // ...
               "misd/raven-bundle": "dev-master"
           }
        }

 2. Use Composer to download and install the bundle:

        $ php composer.phar update misd/raven-bundle

 3. Register the bundle in your application:

        // app/AppKernel.php

        class AppKernel extends Kernel
        {
            // ...
            public function registerBundles()
            {
                $bundles = array(
                    // ...
                    new Misd\RavenBundle\MisdRavenBundle(),
                    // ...
                );
            }
            // ...
        }

Configuration
-------------

### Firewall

To enable Raven authentication, add `raven: true` to a firewall configuration:

    // app/config/security.yml

    security:
        firewalls:
            raven_secured:
                pattern: ^/secure/.*
                raven: true

### User provision

Normal Symfony2 user providers can be used, as long as the username is the user's CRSid.

If you would like any user who has successfully authenticated with Raven to access your application, you can use the bundle's Raven user provider:

    // app/config/security.yml

    security:
        providers:
            raven:
                id: raven_user_provider

The user provider returns an instance of `Misd\RavenBundle\Security\User\RavenUser` with the role `ROLE_USER`.

This can be chained with other providers to grant certain users extra roles. For example:

    // app/config/security.yml

    security:
        providers:
            chain_provider:
                chain:
                    providers: [in_memory, raven]
            in_memory:
                memory:
                    users:
                        abc123: { roles: [ 'ROLE_ADMIN' ] }
            raven:
                id: raven_user_provider

### Resource description

You can add the name of your application to the Raven log in page:

    // app/config/config.yml

    misd_raven:
        description: "My application"

The text on the page will now include something like "This resource calls itself 'My application' and is ...".
