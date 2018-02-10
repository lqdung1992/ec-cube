<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/10/2018
 * Time: 5:43 PM
 */
namespace Eccube\Controller\Farm;

use Eccube\Application;
use Eccube\Entity\ChangePassword;
use Eccube\Entity\Customer;
use Symfony\Component\HttpFoundation\Request;

class FarmerController
{
    /**
     * Farmer change password
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function changePassword(Application $app, Request $request)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        $changePassword = new ChangePassword();
        $changePassword->setEmail($Customer->getEmail());
        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('change_password', $changePassword);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $Customer->setPassword($changePassword->getNewPassword());
            if ($Customer->getSalt() === null) {
                $Customer->setSalt($app['eccube.repository.customer']->createSalt(5));
            }
            $Customer->setPassword(
                $app['eccube.repository.customer']->encryptPassword($app, $Customer)
            );
            $app['orm.em']->flush();

            return $app->redirect($app->url('mypage_change_complete'));
        }

        return $app->render('Farm/farmer_setting_change.twig', array(
            'form' => $form->createView(),
        ));
    }
}