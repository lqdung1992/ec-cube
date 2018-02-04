<?php
namespace Eccube\Controller\Farm;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\FarmerRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FarmServiceController
 * @package Eccube\Farm
 */
class FarmServiceController
{
    private $title = '';

    public function index(Application $app, Request $request)
    {
        /** @var FarmerRepository $repo */
        $repo = $app['eccube.repository.farmer'];
        $Farmer = $repo->newFarmer();

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('farmer_regist', $Farmer);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'register':
                    $builder->setAttribute('freeze', true);
                    $form = $builder->getForm();
                    $form->handleRequest($request);
                    $builder2 = $builder->add('password', 'repeated_password');

                    return $app->render('Farm/service_register.twig', array(
                        'form' => $form->createView(),
                        'form2' => $builder2->getForm()->handleRequest($request)->createView(),
                    ));

                case 'complete':
                    $Farmer
                        ->setSalt(
                            $repo->createSalt(5)
                        )
                        ->setPassword(
                            $repo->encryptPassword($app, $Farmer)
                        )
                        ->setSecretKey(
                            $repo->getUniqueSecretKey($app)
                        );


                    $app['orm.em']->persist($Farmer);
                    $app['orm.em']->flush();

                    $activateUrl = $app->url('entry_activate', array('secret_key' => $Farmer->getSecretKey()));

                    /** @var $BaseInfo \Eccube\Entity\BaseInfo */
                    $BaseInfo = $app['eccube.repository.base_info']->get();
                    $activateFlg = $BaseInfo->getOptionCustomerActivate();

                    // 仮会員設定が有効な場合は、確認メールを送信し完了画面表示.
                    if ($activateFlg) {
                        // メール送信
                        $app['eccube.service.mail']->sendCustomerConfirmMail($Farmer, $activateUrl);

                        return $app->redirect($app->url('entry_complete'));
                    } else {
                        return $app->redirect($activateUrl);
                    }
            }
        }
        dump($form->getErrorsAsString() );

        return $app->render('Farm/service_signup.twig', array(
            'subtitle' => 'Farm service',
            'form' => $form->createView(),
        ));
    }
}
