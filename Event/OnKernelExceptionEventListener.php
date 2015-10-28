<?php
/**
 * Copyright (C) 2012 code mitte GmbH - Zeughausstr. 28-38 - 50667 Cologne/Germany
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so, subject
 * to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Codemitte\Bundle\ForceToolkitBundle\Event;

use
    Symfony\Component\HttpKernel\HttpKernel,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent,
    Codemitte\ForceToolkit\Soap\Client\Connection\Storage\StorageInterface,
    Codemitte\ForceToolkit\Soap\Client\ClientDisabledException,
    Codemitte\Soap\Client\Connection\TracedSoapFault
;

/**
 * Implements API/Force.com failover/session timeout mechanism.
 */
class OnKernelExceptionEventListener
{
    /**
     * @var \Codemitte\ForceToolkit\Soap\Client\Connection\Storage\StorageInterface
     */
    private $connectionStorage;

    /**
     * @var String
     */
    private $maintenanceLocation;

    /**
     * @var String
     */
    private $maintenanceTemplate;

    /**
     * @param \Codemitte\ForceToolkit\Soap\Client\Connection\Storage\StorageInterface $connectionStorage
     * @param String $maintenanceLocation
     * @param String $maintenanceTemplate
     */
    public function __construct(StorageInterface $connectionStorage, $maintenanceLocation, $maintenanceTemplate)
    {
        $this->connectionStorage = $connectionStorage;
        $this->maintenanceLocation = $maintenanceLocation;
        $this->maintenanceTemplate = $maintenanceTemplate;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     * @throws \Exception
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {

        if(HttpKernel::MASTER_REQUEST === $event->getRequestType())
        {

            $e = $event->getException();

            //Show maintenance page
            if( $e instanceof ClientDisabledException)
            {
                // Create Response object
                $response = new Response();

                // create twig environement (non symfony)
                $loader = new \Twig_Loader_Filesystem($this->maintenanceLocation);
                $twig = new \Twig_Environment($loader);

                $response->setContent($twig->render($this->maintenanceTemplate));
                $response->setStatusCode(503);

                $event->setResponse($response);
            }
            // SESSION ID INVALID
            else if($e instanceof \SoapFault && 0 === strpos($e->getMessage(), 'INVALID_SESSION_ID'))
            {
                $request = $event->getRequest();

                $session = $request->getSession();

                $locale = $request->getLocale();

                $locale === null && $locale = $session->get('_locale');

                if(null !== $locale)
                {
                    $this->connectionStorage->remove($locale);

                    $event->setResponse(new RedirectResponse('/'));
                }
            }
        }
    }
}
