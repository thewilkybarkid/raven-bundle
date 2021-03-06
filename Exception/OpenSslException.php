<?php

/*
 * This file is part of the MisdRavenBundle for Symfony2.
 *
 * (c) University of Cambridge
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Misd\RavenBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Thrown when there is an OpenSSL problem.
 *
 * @author Chris Wilkinson <chris.wilkinson@admin.cam.ac.uk>
 */
class OpenSslException extends AuthenticationException
{
}
