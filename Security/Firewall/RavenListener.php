<?php

/*
 * This file is part of the MisdRavenBundle for Symfony2.
 *
 * (c) University of Cambridge
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Misd\RavenBundle\Security\Firewall;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Misd\RavenBundle\Exception\RavenException;
use Misd\RavenBundle\Security\Authentication\Token\RavenUserToken;

/**
 * RavenListener.
 *
 * @author Chris Wilkinson <chris.wilkinson@admin.cam.ac.uk>
 */
class RavenListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;
    protected $container;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface       $securityContext       Security context
     * @param AuthenticationManagerInterface $authenticationManager Authentication manager
     * @param ContainerInterface             $container             Container
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        ContainerInterface $container
    ) {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->query->has('WLS-Response')) {
            // There's a Raven response to process

            $token = RavenUserToken::factory($request->query->get('WLS-Response'));
            $redirect = $token->getAttribute('url');

            switch ($token->getAttribute('status')) {
                case 200:
                    $message = 'Successful authentication';
                    break;
                case 410:
                    $message = 'The user cancelled the authentication request';
                    break;
                case 510:
                    $message = 'No mutually acceptable authentication types available';
                    break;
                case 520:
                    $message = 'Unsupported protocol version';
                    break;
                case 530:
                    $message = 'General request parameter error';
                    break;
                case 540:
                    $message = 'Interaction would be required';
                    break;
                case 560:
                    $message = 'WAA not authorised';
                    break;
                case 570:
                    $message = 'Authentication declined';
                    break;
                default:
                    $message = 'Unknown status code';
                    break;
            }

            if ($token->getAttribute('status') <> 200) {
                throw new RavenException(
                    $token->getAttribute('msg') ? : $message,
                    null,
                    $token->getAttribute('status'));
            }

            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface) {
                $this->securityContext->setToken($returnValue);
                $event->setResponse(new RedirectResponse($redirect));
            } elseif ($returnValue instanceof Response) {
                $event->setResponse($returnValue);
            } else {
                $this->requestAuthentication($event, $request->getUri());
            }
        } elseif (
            $this->securityContext->getToken() != null &&
            $this->securityContext->getToken()->getUser() instanceof UserInterface
        ) {
            // The user is already logged in
        } else {
            $this->requestAuthentication($event, $request->getUri());
        }
    }

    /**
     * Request Raven authentication.
     *
     * @param GetResponseEvent $event Get response event
     * @param string           $url   Redirect URL
     */
    protected function requestAuthentication(GetResponseEvent $event, $url)
    {
        $params['ver'] = 2;
        $params['url'] = urlencode($url);
        $params['desc'] = urlencode($this->container->getParameter('misd_raven.description'));

        $parameters = array();
        foreach ($params as $key => $val) {
            $parameters[] = $key . '=' . utf8_encode($val);
        }
        $parameters = implode('&', $parameters);

        $url = 'https://raven.cam.ac.uk/auth/authenticate.html?' . $parameters;

        $event->setResponse(new RedirectResponse($url));
    }
}
