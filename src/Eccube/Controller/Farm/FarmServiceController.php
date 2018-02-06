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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

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
        $builder->remove('password')
            ->remove('bus_stop');

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
                    dump($Customer);
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

                        return $app->redirect($app->url('farm_service_profile', array('id' => $Customer->getId())));
//                        return $app->redirect($app->url('farm_service'));
                    } else {
                        return $app->redirect($activateUrl);
                    }
                }

                if ($form->isSubmitted() && $form->isValid()) {
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
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function profile(Application $app, Request $request, $id)
    {
        /** @var CustomerRepository $repo */
        $repo = $app['eccube.repository.customer'];
        $Customer = $repo->find($id);

        if (!$Customer) {
            throw new NotFoundHttpException();
        }

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('farmer_profile', $Customer);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $app['orm.em']->persist($Customer);
            $app['orm.em']->flush();

            return $app->redirect($app->url('farm_service_profile', array('id' => $Customer->getId())));
        }

        return $app->render('Farm/service_profile.twig', array(
            'subtitle' => 'Farm service profile',
            'Customer' => $Customer,
            'form' => $form->createView(),
        ));
    }

//    public function addImage(Application $app, Request $request)
//    {
//        if (!$request->isXmlHttpRequest()) {
//            throw new BadRequestHttpException('リクエストが不正です');
//        }
//
//        $images = $request->files->get('farmer_profile');
//
//        $files = array();
//        if (count($images) > 0) {
//            /** @var UploadedFile[] $images */
//            foreach ($images as $img) {
//                //ファイルフォーマット検証
//                $mimeType = $img->getMimeType();
//                if (0 !== strpos($mimeType, 'image')) {
//                    throw new UnsupportedMediaTypeHttpException('ファイル形式が不正です');
//                }
//
//                $extension = $img->getClientOriginalExtension();
//                $filename = date('mdHis').uniqid('_').'.'.$extension;
//                $img->move($app['config']['image_temp_realdir'], $filename);
//                $files[] = $filename;
//            }
//        }
//
//        return $app->json(array('files' => $files), 200);
//    }
}
