<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/15/2018
 * Time: 8:04 PM
 */

namespace Eccube\Controller\Receiver;


use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\SearchType;
use Eccube\Repository\ProductRepository;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

class ReceiverSearchController extends AbstractController
{
    public function index(Application $app, Request $request)
    {
        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createNamedBuilder('', 'receiver_search');
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $form = $builder->getForm();
        $form->handleRequest($request);
        $searchData = $form->getData();
        $type = null;
        if (isset($searchData['search_type']) && $searchData['search_type'] instanceof SearchType) {
            $type = $searchData['search_type']->getId();
        }
        switch ($type) {
            case SearchType::SEARCH_ITEM:
                $builder->setAttribute('freeze', true);
                $builder->setAttribute('freeze_display_text', false);
                $form = $builder->getForm();
                $form->handleRequest($request);

                /** @var FormBuilder $builder2 */
                $builder2 = $app['form.factory']->createNamedBuilder('orderby', 'product_list_order_by', null, array(
                    'empty_data' => null,
                    'required' => false,
                    'label' => false,
                    'allow_extra_fields' => true,
                ));
                if ($request->getMethod() === 'GET') {
                    $builder2->setMethod('GET');
                }
                $orderByForm = $builder2->getForm();

                $orderByForm->handleRequest($request);

                /** @var ProductRepository $productRepo */
                $productRepo = $app['eccube.repository.product'];

                $qb = $productRepo->getQueryBuilderBySearchData($searchData);
                $max = $app['eccube.repository.master.product_list_max']->findOneBy(array(), array('rank' => 'ASC'));
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    !empty($searchData['pageno']) ? $searchData['pageno'] : 1,
                    $max->getId()
                );

                return $app->render('Receiver/receiver_search_product.twig', array(
                    'order_by_form' => $orderByForm->createView(),
                    'form' => $form->createView(),
                    'Products' => $pagination,
                    'Customer' => $app->user(),
                ));
                break;
            case SearchType::SEARCH_FARMER:
                break;
            case SearchType::SEARCH_METHOD:
                break;
            case SearchType::SEARCH_HISTORY:
                break;
            case SearchType::SEARCH_OTHER:
            default:

                break;
        }

        return $app->render('Receiver/receiver_search.twig', array(
            'form' => $form->createView(),

        ));
    }
}
