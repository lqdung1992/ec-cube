<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/04/2018
 * Time: 11:02 AM
 */
namespace Eccube\Controller\Farm;

use Eccube\Application;
use Eccube\Entity\Customer;
use Eccube\Repository\CustomerRepository;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FarmServiceController
 * @package Eccube\Farm
 */
class FarmServiceController
{
    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        /** @var CustomerRepository $repo */
        $repo = $app['eccube.repository.customer'];
        $Customer = $repo->newCustomer();

        /* @var $builder2 \Symfony\Component\Form\FormBuilderInterface */
        $builder2 = $app['form.factory']->createBuilder('farmer_regist', $Customer);

        $builder = clone $builder2;
        $builder->remove('password')->remove('bus_stop');

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);
        switch ($request->get('mode')) {
            case 'register':
                if ($form->isSubmitted() && $form->isValid()) {
                    $builder->setAttribute('freeze', true);
                    $form = $builder->getForm();
                    $form->handleRequest($request);
                    $builder2->setMethod('GET');

                    return $app->render('Farm/service_register.twig', array(
                        'form' => $form->createView(),
                        'form2' => $builder2->getForm()->handleRequest($request)->createView(),
                    ));
                }
                break;

            case 'complete':
                /* @var $form2 \Symfony\Component\Form\FormInterface */
                $form2 = $builder2->getForm();
                $form2->handleRequest($request);
                if ($form2->isSubmitted() && $form2->isValid()) {
                    $Customer
                        ->setSalt(
                            $repo->createSalt(5)
                        )
                        ->setPassword(
                            $repo->encryptPassword($app, $Customer)
                        )
                        ->setSecretKey(
                            $repo->getUniqueSecretKey($app)
                        );

                    $app['orm.em']->persist($Customer);
                    $app['orm.em']->flush();

                    $activateUrl = $app->url('entry_activate', array('secret_key' => $Customer->getSecretKey()));

                    /** @var $BaseInfo \Eccube\Entity\BaseInfo */
                    $BaseInfo = $app['eccube.repository.base_info']->get();
                    $activateFlg = $BaseInfo->getOptionCustomerActivate();

                    // 仮会員設定が有効な場合は、確認メールを送信し完了画面表示.
                    if ($activateFlg) {
                        // メール送信
                        $app['eccube.service.mail']->sendCustomerConfirmMail($Customer, $activateUrl);

                        return $app->redirect($app->url('farm_service_profile'));
                    } else {
                        return $app->redirect($activateUrl);
                    }
                }

                if ($form->isSubmitted()) {
                $builder->setAttribute('freeze', true);
                $form = $builder->getForm();
                $form->handleRequest($request);
                return $app->render('Farm/service_register.twig', array(
                    'form' => $form->createView(),
                    'form2' => $form2->createView(),
                ));
                }
        }

        return $app->render('Farm/service_signup.twig', array(
            'subtitle' => 'Farm service',
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function profile(Application $app, Request $request)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }
        // load image
        $profileImage = null;
        $profileImageFileType = null;
        if ($Customer->getProfileImage()) {
            $profileImage = $Customer->getProfileImage();
            $profileImageFileType = new File($app['config']['image_save_realdir'] . '/' . $Customer->getProfileImage());
            $Customer->setProfileImage($profileImageFileType);
        }
        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('farmer_profile', $Customer);
        $form = $builder->getForm();

        // Set profile to form and return default to customer
        if ($profileImageFileType) {
            $Customer->setProfileImage($profileImage);
            $form['profile_image']->setData($profileImageFileType);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $image */
            $image = $form['profile_image']->getData();
            $filename = $profileImage;
            if ($image) {
                if ($filename && file_exists($old = $app['config']['image_save_realdir'] . '/' . $filename)) {
                    unlink($old);
                }
                $extension = $image->getClientOriginalExtension();
                $filename = date('mdHis').uniqid('_').'.'.$extension;
                $image->move($app['config']['image_save_realdir'], $filename);
            }

            $Customer->setProfileImage($filename);
            $app['orm.em']->persist($Customer);
            $app['orm.em']->flush();

            return $app->redirect($app->url('farm_service_profile'));
        }

        return $app->render('Farm/service_profile.twig', array(
            'subtitle' => 'Farm service profile',
            'profile_image' => $profileImage,
            'Customer' => $Customer,
            'form' => $form->createView(),
        ));
    }

    /**
     * Profile setting
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function settingProfile(Application $app, Request $request)
    {
        $Customer = $app->user();
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }

        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createBuilder('farmer_regist', $Customer);
        $builder->remove('customer_role')
            ->remove('password')
            ->remove('email')
            ->remove('bus_stop');
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $app['orm.em']->persist($Customer);
            $app['orm.em']->flush();

            return $app->redirect($app->url('farm_service_profile'));
        }

        return $app->render('Farm/service_profile_setting.twig', array(
            'subtitle' => 'Farm service profile setting',
            'form' => $form->createView(),
            'TargetCustomer' => $Customer
        ));
    }
}
