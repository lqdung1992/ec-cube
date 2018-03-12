<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/11/2018
 * Time: 1:02 AM
 */

namespace Eccube\Controller\Farm;

use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Repository\ProductRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FarmHistoryController extends AbstractController
{
    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Application\AuthenticationCredentialsNotFoundException
     */
    public function index(Application $app, Request $request)
    {
        if (!$app->isGranted('ROLE_FARMER')) {
            throw new NotFoundHttpException();
        }
        $Customer = $app->user();

        /** @var ProductRepository $productRepository */
        $productRepository = $app['eccube.repository.product'];
        $queryBuilder = $productRepository->getProductQueryBuilderForHistory($Customer);
        $history = $queryBuilder->getQuery()->getResult();

        return $app->render('Farm/history.twig', array(
            'history' => $history
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Application\AuthenticationCredentialsNotFoundException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function historyDetail(Application $app, Request $request, $id)
    {
        if (!$app->isGranted('ROLE_FARMER')) {
            throw new NotFoundHttpException();
        }
        /** @var ProductRepository $productRepository */
        $productRepository = $app['eccube.repository.product'];
        $Product = $productRepository->find($id);
        if (!$Product) {
            throw new NotFoundHttpException();
        }

        if ($request->getMethod() == 'PUT') {
            $this->isTokenValid($app);

            $CopyProduct = clone $Product;
            $CopyProduct->copy();
            $Disp = $app['eccube.repository.master.disp']->find(\Eccube\Entity\Master\Disp::DISPLAY_HIDE);
            $CopyProduct->setStatus($Disp);

            $CopyProductCategories = $CopyProduct->getProductCategories();
            /** @var EntityManager $em */
            $em = $app['orm.em'];
            foreach ($CopyProductCategories as $Category) {
                $em->persist($Category);
            }

            // 規格あり商品の場合は, デフォルトの商品規格を取得し登録する.
            if ($CopyProduct->hasProductClass()) {
                $softDeleteFilter = $em->getFilters()->getFilter('soft_delete');
                $softDeleteFilter->setExcludes(array(
                    'Eccube\Entity\ProductClass'
                ));
                $dummyClass = $app['eccube.repository.product_class']->findOneBy(array(
                    'del_flg' => \Eccube\Common\Constant::ENABLED,
                    'ClassCategory1' => null,
                    'ClassCategory2' => null,
                    'Product' => $Product,
                ));
                $dummyClass = clone $dummyClass;
                $dummyClass->setProduct($CopyProduct);
                $CopyProduct->addProductClass($dummyClass);
                $softDeleteFilter->setExcludes(array());
            }

            $CopyProductClasses = $CopyProduct->getProductClasses();
            foreach ($CopyProductClasses as $Class) {
                $Stock = $Class->getProductStock();
                $CopyStock = clone $Stock;
                $CopyStock->setProductClass($Class);
                $em->persist($CopyStock);

                $em->persist($Class);
            }
            $Images = $CopyProduct->getProductImage();
            foreach ($Images as $Image) {
                // 画像ファイルを新規作成
                $extension = pathinfo($Image->getFileName(), PATHINFO_EXTENSION);
                $filename = date('mdHis').uniqid('_').'.'.$extension;
                try {
                    $fs = new Filesystem();
                    $fs->copy($app['config']['image_save_realdir'].'/'.$Image->getFileName(), $app['config']['image_save_realdir'].'/'.$filename);
                } catch (\Exception $e) {
                    // エラーが発生しても無視する
                }
                $Image->setFileName($filename);

                $em->persist($Image);
            }
            $Tags = $CopyProduct->getProductTag();
            foreach ($Tags as $Tag) {
                $em->persist($Tag);
            }

            $em->persist($CopyProduct);

            $em->flush($CopyProduct);
            return $app->redirect($app->url('farm_item_edit', array('id' => $CopyProduct->getId())));
        }

        return $app->render('Farm/history_detail.twig', array(
            'Product' => $Product
        ));
    }

}