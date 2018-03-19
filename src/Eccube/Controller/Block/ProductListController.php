<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/11/2018
 * Time: 3:33 PM
 */

namespace Eccube\Controller\Block;


use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class ProductListController
{
    public function index(Application $app, Request $request)
    {
        $products = $app['eccube.repository.product']->findBy(array(), array('create_date' => 'DESC'));

        return $app->render('Block/product_list.twig', array('Products' => $products));
    }
}