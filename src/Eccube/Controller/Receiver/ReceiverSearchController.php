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
use Eccube\Entity\Master\CustomerRole;
use Eccube\Entity\Master\SearchType;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\CultivationMethodRepository;
use Eccube\Repository\Master\SearchTypeRepository;
use Eccube\Repository\ProductRepository;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class ReceiverSearchController extends AbstractController
{
    const COOKIE_KEY = 'search_key';

    public function index(Application $app, Request $request)
    {
        if (!$app->isGranted(CustomerRole::RECIPIENT)) {
            return $app->redirect($app->url('mypage_login'));
        }
        /** @var SearchTypeRepository $searchRepo */
        $searchRepo = $app['eccube.repository.master.search'];
        $arrType = $searchRepo->getAllIdAsKey();
        $arrType = array_column($arrType, 'name', 'id');

        $searchHistory = $this->getSearchHistory($request);
        $arrSearchHistoryType = array();
        if ($searchHistory) {
            $arrSearchHistoryType = explode(',', $searchHistory);
        }
        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createNamedBuilder('', 'receiver_search');
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $form = $builder->getForm();
        $form->handleRequest($request);
        $searchData = $form->getData();
        $type = null;
        if (isset($searchData['search_type'])) {
            $type = $searchData['search_type'];
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

                $arrOptions = array(
                    'order_by_form' => $orderByForm->createView(),
                    'form' => $form->createView(),
                    'Products' => $pagination,
                    'Customer' => $app->user(),
                );
                $template = 'Receiver/receiver_search_product.twig';

                return $this->createRender($app, $searchData, $arrSearchHistoryType, $template, $arrOptions);
                break;
            case SearchType::SEARCH_FARMER:
                $builder->setAttribute('freeze', true);
                $builder->setAttribute('freeze_display_text', false);
                $form = $builder->getForm();
                $form->handleRequest($request);

                /** @var CustomerRepository $customerRepo */
                $customerRepo = $app['eccube.repository.customer'];

                $farmers = $customerRepo->getQueryBuilderForReceiver($searchData)->getQuery()->getResult();

                $arrOptions = array(
                    'form' => $form->createView(),
                    'farmers' => $farmers,
                );
                $template = 'Receiver/receiver_search_farmer.twig';

                return $this->createRender($app, $searchData, $arrSearchHistoryType, $template, $arrOptions);
                break;
            case SearchType::SEARCH_METHOD:
                $builder->setAttribute('freeze', true);
                $builder->setAttribute('freeze_display_text', false);
                $form = $builder->getForm();
                $form->handleRequest($request);

                /** @var CultivationMethodRepository $cultivationMethodRepo */
                $cultivationMethodRepo = $app['eccube.repository.master.cultivation_method'];

                $methods = $cultivationMethodRepo->findAll();

                $arrOptions = array(
                    'form' => $form->createView(),
                    'methods' => $methods,
                );
                $template = 'Receiver/receiver_search_method.twig';

                return $this->createRender($app, $searchData, $arrSearchHistoryType, $template, $arrOptions);
                break;
            case SearchType::SEARCH_HISTORY:
                return $app->redirect($app->url('receiver_search', array('name' => $searchData['name'], 'search_type' => SearchType::SEARCH_ITEM)));
                break;
            case SearchType::SEARCH_OTHER:
            default:

                break;
        }

        return $app->render('Receiver/receiver_search.twig', array(
            'form' => $form->createView(),
            'types' => $arrType,
            'history_type' => $arrSearchHistoryType,
        ));
    }

    /**
     * @param Application $app
     * @param string $template
     * @param array $option
     * @param string $cookie = name1,name2,name3,name4,name5,...,name10
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderWithCookie(Application $app, $template = 'Receiver/receiver_search.twig', $option = array(), $cookie = null)
    {
        $time = time() + 60*60*24*30;
        $urlpath = $app['config']['root_urlpath'];
        $response = $app->render($template, $option);
        if ($cookie) {
            $response->headers->setCookie(new Cookie(self::COOKIE_KEY, $cookie, $time, $urlpath));
        }
        return $response;
    }

    /**
     * @param Request $request
     * @return string = name1,name2,name3,name4,name5,...,name10
     */
    protected function getSearchHistory(Request $request)
    {
        $cookie = $request->cookies->get(self::COOKIE_KEY);
        return $cookie;
    }

    /**
     * @param Application $app
     * @param array $searchData
     * @param array $arrSearchHistoryType
     * @param string $template
     * @param array $arrOptions
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createRender(Application $app, $searchData, $arrSearchHistoryType, $template, $arrOptions)
    {
        if (isset($searchData['name']) && $searchData['name']) {
            $arrSearchHistoryType[] = $searchData['name'];
            $arrSearchHistoryType = array_unique($arrSearchHistoryType);
            if (count($arrSearchHistoryType) > 10) {
                unset($arrSearchHistoryType[0]);
            }
            $saveData = implode(',', $arrSearchHistoryType);

            return $this->renderWithCookie($app, $template, $arrOptions, $saveData);
        }

        return $app->render($template, $arrOptions);
    }
}
