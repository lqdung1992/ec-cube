<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContactController
{
    /**
     * お問い合わせ画面.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     */
    public function index(Application $app, Request $request)
    {
        $builder = $app['form.factory']->createBuilder('contact');
        $form = $builder->getForm();
        $form->handleRequest($request);
        if (!$app->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $app->redirect($app->url('mypage_login'));
        }
        $user = $app['user'];

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    //$builder->setAttribute('freeze', true);
                    $form = $builder->getForm();
                    $form->handleRequest($request);

                    return $app->render('Contact/confirm.twig', array(
                        'form' => $form->createView(),
                        'TargetCustomer' => $app['user']
                    ));

                case 'complete':
                    $data = $form->getData();
                    $data = array(
                        'name01' => $user->getName01(),
                        'name02' => $user->getName02(),
                        'kana01' => $user->getKana01(),
                        'kana02' => $user->getKana02(),
                        'zip01' => $user->getZip01(),
                        'zip02' => $user->getZip02(),
                        'pref' => $user->getPref(),
                        'addr01' => $user->getAddr01(),
                        'addr02' => $user->getAddr02(),
                        'tel01' => $user->getTel01(),
                        'tel02' => $user->getTel02(),
                        'tel03' => $user->getTel03(),
                        'email' => $user->getEmail(),
                        'title' => $data['title'],
                        'contents' => $data['contents']
                    );
                    // メール送信
                    $app['eccube.service.mail']->sendContactMail($data);
                    return $app->redirect($app->url('contact_complete'));
            }
        }

        return $app->render('Contact/index.twig', array(
            'form' => $form->createView(),
            'TargetCustomer' => $app['user']
        ));
    }

    /**
     * お問い合わせ完了画面.
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function complete(Application $app)
    {
        return $app->render('Contact/complete.twig', array(
            'TargetCustomer' => $app['user']
        ));
    }
}
