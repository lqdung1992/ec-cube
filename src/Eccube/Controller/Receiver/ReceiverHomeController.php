<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/11/2018
 * Time: 1:00 AM
 */

namespace Eccube\Controller\Receiver;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\CustomerRole;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;

class ReceiverHomeController extends AbstractController
{
    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }

        /** @var ProductRepository $productRepo */
        $productRepo = $app['eccube.repository.product'];
        $qb = $productRepo->getProductQueryBuilderAll();
        $max = $app['eccube.repository.master.product_list_max']->findOneBy(array(), array('rank' => 'ASC'));

        $pageNo = $request->get('pageno', 1);
        $pagination = $app['paginator']()->paginate(
            $qb,
            $pageNo,
            $max->getId()
        );

        return $app->render('Receiver/receiver_home.twig', array(
            'Products' => $pagination,
            'Customer' => $Customer
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return bool
     */
    public function actionFavorite(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest() && $app->isGranted(CustomerRole::RECIPIENT)) {
            $id = $request->get('id');
            if (!$id) {
                return false;
            }
            $Product = $app['eccube.repository.product']->find($id);
            if (!$Product) {
                return false;
            }
            $Customer = $app->user();
            /** @var CustomerFavoriteProductRepository $favoriteRepo */
            $favoriteRepo = $app['eccube.repository.customer_favorite_product'];
            $isFavorite = $favoriteRepo->isFavorite($Customer, $Product);
            if ($isFavorite) {
                return $favoriteRepo->deleteFavorite($Customer, $Product);
            } else {
                $favoriteRepo->addFavorite($Customer, $Product);
            }
            return true;
        }
        return false;
    }
}
