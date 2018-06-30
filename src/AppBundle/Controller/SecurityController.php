<?php

namespace AppBundle\Controller;

use AppBundle\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * https://stackoverflow.com/questions/35663410/form-builder-for-symfony-login-page
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $defaultData = array('_username' => $authenticationUtils->getLastUsername());

        $form = $this->createForm(LoginType::class, $defaultData);

        if (!is_null($authenticationUtils->getLastAuthenticationError(false))) {
            $form->addError(new \Symfony\Component\Form\FormError(
                $authenticationUtils->getLastAuthenticationError()->getMessageKey()
            ));
        }

        $form->handleRequest($request);

        return $this->render('security/login.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
