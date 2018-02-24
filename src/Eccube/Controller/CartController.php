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

use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Entity\Cart;
use Eccube\Entity\CartItem;
use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\CartException;
use Eccube\Service\CartService;
use Eccube\Util\DateUtil;
use Symfony\Component\HttpFoundation\Request;

class CartController extends AbstractController
{
    /**
     * カート画面.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        /** @var CartService $cartService */
        $cartService = $app['eccube.service.cart'];
        $Cart = $cartService->getCart();

        // FRONT_CART_INDEX_INITIALIZE
        $event = new EventArgs(
            array(),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_INDEX_INITIALIZE, $event);

        // FRONT_CART_INDEX_COMPLETE
        $event = new EventArgs(
            array(),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_INDEX_COMPLETE, $event);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        /** @var EntityManager $em */
        $em = $app['orm.em'];

        $masterDate = $em->createQueryBuilder()
            ->select('rd')
            ->from('Eccube\Entity\Master\ReceiptableDate', 'rd', 'rd.id')
            ->getQuery()
            ->getResult();

        $mode = $request->get('mode');
        switch ($mode) {
            case 'confirm':
                $dateId = $request->get('date_id');
                $dateTime = DateUtil::getDay($dateId);
                /** @var Cart $CartByDate */
                $CartByDate = $cartService->getCartByDate($dateTime);

                /** @var  Customer[] $arrCreator */
                $arrCreator = array();
                /** @var  CartItem $cart */
                foreach ($CartByDate->getCartItems() as $cart) {
                    $Creator = $cart->getObject()->getProduct()->getCreator();
                    $arrCreator[$Creator->getId()] = $Creator;
                }

                $arrTotal = array();
                foreach ($arrCreator as $creator) {
                    $arrTotal[$creator->getId()] = 0;
                    foreach ($CartByDate->getCartItems() as $cartItem) {
                        if ($cartItem->getObject()->getProduct()->getCreator()->getId() == $creator->getId()) {
                            $arrTotal[$creator->getId()] += $cartItem->getTotalPrice();
                        }
                    }
                }

                return $app->render(
                    'Cart/confirm.twig',
                    array(
                        'Cart' => $CartByDate,
                        'reception_date' => $dateTime,
                        'Creators' => $arrCreator,
                        'creator_total' => $arrTotal,
                        'date_id' => $dateId,
                        'master_date' => $masterDate
                    )
                );
        }

        $receptionDate = array();
        foreach ($Cart->getCartItems() as $CartItem) {
            $receptionDate[$CartItem->getReceptionDate()->format('Y/m/d')] = $CartItem->getReceptionDate();
        }

        return $app->render(
            'Cart/index.twig',
            array(
                'Cart' => $Cart,
//                'least' => $least,
//                'quantity' => $quantity,
//                'is_delivery_free' => $isDeliveryFree,
                'reception_dates' => $receptionDate,
                'master_date' => $masterDate
            )
        );
    }

    /**
     * カートに商品を追加する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function add(Application $app, Request $request)
    {
        $productClassId = $request->get('product_class_id');
        $quantity = $request->request->has('quantity') ? $request->get('quantity') : 1;

        // FRONT_CART_ADD_INITIALIZE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
                'quantity' => $quantity,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_ADD_INITIALIZE, $event);

        try {

            $productClassId = $event->getArgument('productClassId');
            $quantity = $event->getArgument('quantity');

            log_info('カート追加処理開始', array('product_class_id' => $productClassId, 'quantity' => $quantity));

            $app['eccube.service.cart']->addProduct($productClassId, $quantity)->save();

            log_info('カート追加処理完了', array('product_class_id' => $productClassId, 'quantity' => $quantity));

            // FRONT_CART_ADD_COMPLETE
            $event = new EventArgs(
                array(
                    'productClassId' => $productClassId,
                    'quantity' => $quantity,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_ADD_COMPLETE, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

        } catch (CartException $e) {

            log_info('カート追加エラー', array($e->getMessage()));

            // FRONT_CART_ADD_EXCEPTION
            $event = new EventArgs(
                array(
                    'exception' => $e,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_ADD_EXCEPTION, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $app->addRequestError($e->getMessage());
        }

        return $app->redirect($app->url('cart'));
    }

    /**
     * カートに入っている商品の個数を1増やす.
     *
     * @param Application $app
     * @param Request $request
     * @param $productClassId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function up(Application $app, Request $request, $productClassId)
    {
        $this->isTokenValid($app);

        // FRONT_CART_UP_INITIALIZE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_UP_INITIALIZE, $event);

        try {

            log_info('カート加算処理開始', array('product_class_id' => $productClassId));

            $productClassId = $event->getArgument('productClassId');

            $app['eccube.service.cart']->upProductQuantity($productClassId)->save();

            // FRONT_CART_UP_COMPLETE
            $event = new EventArgs(
                array(
                    'productClassId' => $productClassId,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_UP_COMPLETE, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            log_info('カート加算処理完了', array('product_class_id' => $productClassId));

        } catch (CartException $e) {

            log_info('カート加算エラー', array($e->getMessage()));

            // FRONT_CART_UP_EXCEPTION
            $event = new EventArgs(
                array(
                    'exception' => $e,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_UP_EXCEPTION, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $app->addRequestError($e->getMessage());
        }

        return $app->redirect($app->url('cart'));
    }

    /**
     * カートに入っている商品の個数を1減らす.
     * マイナスになる場合は, 商品をカートから削除する.
     *
     * @param Application $app
     * @param Request $request
     * @param $productClassId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function down(Application $app, Request $request, $productClassId)
    {
        $this->isTokenValid($app);

        // FRONT_CART_DOWN_INITIALIZE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_DOWN_INITIALIZE, $event);

        try {

            log_info('カート減算処理開始', array('product_class_id' => $productClassId));

            $productClassId = $event->getArgument('productClassId');
            $app['eccube.service.cart']->downProductQuantity($productClassId)->save();

            // FRONT_CART_UP_COMPLETE
            $event = new EventArgs(
                array(
                    'productClassId' => $productClassId,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_DOWN_COMPLETE, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            log_info('カート減算処理完了', array('product_class_id' => $productClassId));

        } catch (CartException $e) {
            log_info('カート減算エラー', array($e->getMessage()));

            // FRONT_CART_DOWN_EXCEPTION
            $event = new EventArgs(
                array(
                    'exception' => $e,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_DOWN_EXCEPTION, $event);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            $app->addRequestError($e->getMessage());
        }

        return $app->redirect($app->url('cart'));
    }

    /**
     * カートに入っている商品を削除する.
     *
     * @param Application $app
     * @param Request $request
     * @param $productClassId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function remove(Application $app, Request $request, $productClassId)
    {
        $this->isTokenValid($app);
        $dateId = $request->get('date_id');

        log_info('カート削除処理開始', array('product_class_id' => $productClassId, 'date_id' => $dateId));

        // FRONT_CART_REMOVE_INITIALIZE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_REMOVE_INITIALIZE, $event);

        $productClassId = $event->getArgument('productClassId');
        $date = null;
        if ($dateId) {
            $date = DateUtil::getDay($dateId);
        }
        $app['eccube.service.cart']->removeProduct($productClassId, $date)->save();

        log_info('カート削除処理完了', array('product_class_id' => $productClassId));

        // FRONT_CART_REMOVE_COMPLETE
        $event = new EventArgs(
            array(
                'productClassId' => $productClassId,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_REMOVE_COMPLETE, $event);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return $app->redirect($app->url('cart'));
    }

    /**
     * カートに商品を個数を指定して設定する.
     *
     * @param Application $app
     * @param Request $request
     * @param $productClassId
     * @param $quantity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws CartException
     *
     * @deprecated since 3.0.0, to be removed in 3.1
     */
    public function setQuantity(Application $app, Request $request, $productClassId, $quantity)
    {
        $this->isTokenValid($app);

        $app['eccube.service.cart']->setProductQuantity($productClassId, $quantity)->save();

        return $app->redirect($app->url('cart'));
    }

    /**
     * カートをロック状態に設定し、購入確認画面へ遷移する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function buystep(Application $app, Request $request)
    {
        // FRONT_CART_BUYSTEP_INITIALIZE
        $event = new EventArgs(
            array(),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_BUYSTEP_INITIALIZE, $event);

        $app['eccube.service.cart']->lock();
        $app['eccube.service.cart']->save();

        // FRONT_CART_BUYSTEP_COMPLETE
        $event = new EventArgs(
            array(),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_CART_BUYSTEP_COMPLETE, $event);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return $app->redirect($app->url('shopping'));
    }

    /**
     * modify cart
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function modify(Application $app, Request $request)
    {
        $quantity = $request->request->has('quantity') ? $request->get('quantity') : 1;
        /** @var CartService $serviceCart */
        $serviceCart = $app['eccube.service.cart'];
        try {

            foreach ($quantity as $productClassId => $item) {
                foreach ($item as $dateId => $quantity) {
                    $date = DateUtil::getDay($dateId);
                    $serviceCart->setProductQuantity($productClassId, $quantity, $date)->save();
                }
            }
        } catch (CartException $e) {

            log_info('カート追加エラー', array($e->getMessage()));
            $app->addRequestError($e->getMessage());
        }

        return $app->redirect($app->url('cart'));
    }
}
