<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/10/2018
 * Time: 5:43 PM
 */
namespace Eccube\Controller\Farm;

use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\ChangePassword;
use Eccube\Entity\Customer;
use Eccube\Entity\CustomerImage;
use Eccube\Entity\CustomerVoice;
use Eccube\Entity\Master\ReceiptableDate;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductReceiptableDate;
use Eccube\Entity\ProductTag;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\CustomerVoiceRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Util\EntityUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Class FarmerController
 * @package Eccube\Controller\Farm
 */
class FarmerController
{
    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request, $id = null)
    {
        $isOwner = false;
        /** @var Customer $Customer */
        $Customer = $app->user();
        // Todo: check is farmer
        // || !$app->isGranted(CustomerRole::FARMER)
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }
        if ($id) {
            /** @var CustomerRepository $repo */
            $repo = $app['eccube.repository.customer'];
            /** @var Customer $TargetCustomer */
            $TargetCustomer = $repo->find($id);
            if (!$TargetCustomer) {
                throw new NotFoundHttpException();
            }
            if ($TargetCustomer->getId() == $Customer->getId()) {
                $isOwner = true;
            }
        } else {
            $isOwner = true;
            $TargetCustomer = $Customer;
        }

        $voice = new CustomerVoice();
        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createBuilder('farm_voice', $voice);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $image */
            $image = $form['file_name']->getData();
            if ($image) {
                $extension = $image->getClientOriginalExtension();
                $fileName = date('mdHis').uniqid('_').'.'.$extension;
                $image->move($app['config']['image_save_realdir'], $fileName);
                $voice->setFileName($fileName);
            }
            $voice->setCustomer($Customer);
            $voice->setTargetCustomer($TargetCustomer);
            $app['orm.em']->persist($voice);
            $app['orm.em']->flush();

            return $app->redirect($app->url('farm_profile', array('id' => $TargetCustomer->getId(), 'voice' => 1)));
        }
        /** @var CustomerVoiceRepository $voiceRepo */
        $voiceRepo = $app['eccube.repository.customer_voice'];
        $CustomerVoice = $voiceRepo->findBy(array('TargetCustomer' => $TargetCustomer, 'Product' => null), array('create_date' => 'ASC'));

        return $app->render('Farm/farm_profile.twig', array(
            'TargetCustomer' => $TargetCustomer,
            'CustomerVoice' => $CustomerVoice,
            'form' => $form->createView(),
            'is_owner' => $isOwner,
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteVoice(Application $app, Request $request, $id)
    {
        if (!$id) {
            throw new NotFoundHttpException();
        }
        /** @var Customer $Customer */
        $Customer = $app->user();
        // Todo: check is farmer
        // || !$app->isGranted(CustomerRole::FARMER)
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }
        /** @var CustomerVoiceRepository $repo */
        $repo = $app['eccube.repository.customer_voice'];
        /** @var CustomerVoice $voice */
        $voice = $repo->find($id);
        if (!$voice) {
            throw new NotFoundHttpException();
        }
        if ($voice->getCustomer()->getId() != $voice->getTargetCustomer()->getId()) {
            throw new AccessDeniedHttpException();
        }

        $voice->setDelFlg(Constant::ENABLED);
        $app['orm.em']->persist($voice);
        $app['orm.em']->flush();

        return $app->redirect($app->url('farm_profile', array('id' => $Customer->getId(), 'voice' => 1)));
    }

    /**
     * Farmer change password
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function changePassword(Application $app, Request $request)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        $changePassword = new ChangePassword();
        $changePassword->setEmail($Customer->getEmail());
        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('change_password', $changePassword);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $Customer->setPassword($changePassword->getNewPassword());
            if ($Customer->getSalt() === null) {
                $Customer->setSalt($app['eccube.repository.customer']->createSalt(5));
            }
            $Customer->setPassword(
                $app['eccube.repository.customer']->encryptPassword($app, $Customer)
            );
            $app['orm.em']->flush();

            return $app->redirect($app->url('mypage_change_complete'));
        }

        return $app->render('Farm/farmer_setting_change.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editProfile(Application $app, Request $request)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        // Todo: check is farmer
        // || !$app->isGranted(CustomerRole::FARMER)
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }
        $profileImage = null;
        $profileImageFileType = null;
        if ($Customer->getProfileImage()) {
            $profileImage = $Customer->getProfileImage();
            $profileImageFileType = new File($app['config']['image_save_realdir'] . '/' . $Customer->getProfileImage());
            $Customer->setProfileImage($profileImageFileType);
        }
        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('farm_profile_edit', $Customer);

        $form = $builder->getForm();
        // Set profile to form and return default to customer
        if ($profileImageFileType) {
            $Customer->setProfileImage($profileImage);
            $form['profile_image']->setData($profileImageFileType);
        }
        $images = array();
        $customerImages = $Customer->getCustomerImage();
        foreach ($customerImages as $customerImage) {
            $images[] = $customerImage->getFileName();
        }
        $form['images']->setData($images);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $Customer = $form->getData();
            /** @var UploadedFile $image */
            $image = $form['profile_image']->getData();
            $oldProfileImage = $profileImage;
            if ($image) {
                if ($oldProfileImage && file_exists($old = $app['config']['image_save_realdir'] . '/' . $oldProfileImage)) {
                    unlink($old);
                }
                $extension = $image->getClientOriginalExtension();
                $oldProfileImage = date('mdHis').uniqid('_').'.'.$extension;
                $image->move($app['config']['image_save_realdir'], $oldProfileImage);
            }
            $Customer->setProfileImage($oldProfileImage);

            $addImages = $form->get('add_images')->getData();
            foreach ($addImages as $addImage) {
                $customerImage = new CustomerImage();
                $customerImage->setFileName($addImage)->setCustomer($Customer);
                $Customer->addCustomerImage($customerImage);
                $app['orm.em']->persist($customerImage);

                $file = new File($app['config']['image_temp_realdir'].'/'.$addImage);
                $file->move($app['config']['image_save_realdir']);
            }

            $delImages = $form->get('delete_images')->getData();
            foreach ($delImages as $delImage) {
                $customerImage = $app['eccube.repository.customer_image']->findOneBy(array('file_name' => $delImage));
                if ($customerImage instanceof CustomerImage) {
                    $Customer->removeCustomerImage($customerImage);
                    $app['orm.em']->remove($customerImage);
                }
                $app['orm.em']->persist($Customer);

                if (!empty($delImage)) {
                    $fs = new Filesystem();
                    $fs->remove($app['config']['image_save_realdir'].'/'.$delImage);
                }
            }

            $app['orm.em']->persist($Customer);
            $app['orm.em']->flush();

            return $app->redirect($app->url('farm_profile', array('id' => $Customer->getId())));
        }

        return $app->render('Farm/farm_profile_edit.twig', array(
            'subtitle' => 'Farm service profile',
            'profile_image' => $profileImage,
            'Customer' => $Customer,
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addImage(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('リクエストが不正です');
        }

        $allFile = $request->files->all();
        $images = array_shift($allFile);
        $files = array();
        if (count($images) > 0) {
            /** @var UploadedFile[] $img */
            foreach ($images as $img) {
                foreach ($img as $image) {
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException('ファイル形式が不正です');
                    }

                    $extension = $image->getClientOriginalExtension();
                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    $image->move($app['config']['image_temp_realdir'], $filename);
                    $files[] = $filename;
                }
            }
        }

        return $app->json(array('files' => $files), 200);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home(Application $app, Request $request, $id)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        $TargetCustomer = $app['eccube.repository.customer']->find($id);
        if (!$TargetCustomer instanceof Customer) {
            throw new NotFoundHttpException();
        }

        /** @var ProductRepository $productRepo */
        $productRepo = $app['eccube.repository.product'];
        $productList = $productRepo->getProductQueryBuilderByCustomer($TargetCustomer)->getQuery()->getResult();

        return $app->render('Farm/farm_home.twig', array(
            'items' => array(),
            'products' => $productList,
            'TargetCustomer' => $TargetCustomer,
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function item(Application $app, Request $request, $id = null)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        // Todo: check is farmer
        // || !$app->isGranted(CustomerRole::FARMER)
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }

        if (is_null($id)) {
            $Product = new \Eccube\Entity\Product();
            $ProductClass = new \Eccube\Entity\ProductClass();
            $Disp = $app['eccube.repository.master.disp']->find(\Eccube\Entity\Master\Disp::DISPLAY_HIDE);
            $Product->setDelFlg(Constant::DISABLED)
                ->addProductClass($ProductClass)
                ->setStatus($Disp);
            $ProductClass->setDelFlg(Constant::DISABLED)
                ->setStockUnlimited(true)
                ->setProduct($Product)
                ->setCreator($Customer);
            $ProductStock = new \Eccube\Entity\ProductStock();
            $ProductClass->setProductStock($ProductStock);
            $ProductStock->setProductClass($ProductClass)
                ->setCreator($Customer);
        } else {
            /** @var Product $Product */
            $Product = $app['eccube.repository.product']->find($id);
            if (!$Product) {
                throw new NotFoundHttpException();
            }
            $ProductClasses = $Product->getProductClasses();
            $ProductClass = $ProductClasses[0];
            /** @var BaseInfo $BaseInfo */
            $BaseInfo = $app['eccube.repository.base_info']->get();
            if ($BaseInfo->getOptionProductTaxRule() == Constant::ENABLED && $ProductClass->getTaxRule() && !$ProductClass->getTaxRule()->getDelFlg()) {
                $ProductClass->setTaxRate($ProductClass->getTaxRule()->getTaxRate());
            }
            $ProductStock = $ProductClasses[0]->getProductStock();
        }

        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createBuilder('item_edit', $Product);
        $form = $builder->getForm();
        $ProductType = $app['eccube.repository.master.product_type']->find(1);
        $ProductClass->setStockUnlimited(true);

        $form['class']->setData($ProductClass);

        $images = array();
        $ProductImages = $Product->getProductImage();
        foreach ($ProductImages as $ProductImage) {
            $images[] = $ProductImage->getFileName();
        }
        $form['images']->setData($images);

        $category = $Product->getProductCategories()->first()->getCategory();
        $form['Category']->setData($category);

        $Tags = array();
        $ProductTags = $Product->getProductTag();
        foreach ($ProductTags as $ProductTag) {
            $Tags[] = $ProductTag->getTag();
        }
        $form['Tag']->setData($Tags);

        $rd = array();
        $productRDs = $Product->getProductReceiptableDates();
        foreach ($productRDs as $productRD) {
            $rd[] = $productRD->getReceiptableDate();
        }
        $form['ReceiptableDate']->setData($rd);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $Product = $form->getData();
            $Disp = $app['eccube.repository.master.disp']->find(\Eccube\Entity\Master\Disp::DISPLAY_SHOW);
            $Product->setStatus($Disp);
            // error creator;
            $Product->setCreator($Customer);

            /** @var EntityManager $em */
            $em = $app['orm.em'];

            /** @var ProductClass $ProductClass */
            $ProductClass = $form['class']->getData();
            $ProductClass->setProductType($ProductType);

            // 個別消費税
            $BaseInfo = $app['eccube.repository.base_info']->get();
            if ($BaseInfo->getOptionProductTaxRule() == Constant::ENABLED) {
                if ($ProductClass->getTaxRate() !== null) {
                    if ($ProductClass->getTaxRule()) {
                        if ($ProductClass->getTaxRule()->getDelFlg() == Constant::ENABLED) {
                            $ProductClass->getTaxRule()->setDelFlg(Constant::DISABLED);
                        }

                        $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                    } else {
                        $taxrule = $app['eccube.repository.tax_rule']->newTaxRule();
                        $taxrule->setTaxRate($ProductClass->getTaxRate());
                        $taxrule->setApplyDate(new \DateTime());
                        $taxrule->setProduct($Product);
                        $taxrule->setProductClass($ProductClass);
                        $ProductClass->setTaxRule($taxrule);
                    }
                } else {
                    if ($ProductClass->getTaxRule()) {
                        $ProductClass->getTaxRule()->setDelFlg(Constant::ENABLED);
                    }
                }
            }
            $em->persist($ProductClass);

            // 在庫情報を作成
            if (!$ProductClass->getStockUnlimited()) {
                $ProductStock->setStock($ProductClass->getStock());
            } else {
                // 在庫無制限時はnullを設定
                $ProductStock->setStock(null);
            }
            $em->persist($ProductStock);

            /* @var $Product \Eccube\Entity\Product */
            foreach ($Product->getProductCategories() as $ProductCategory) {
                $Product->removeProductCategory($ProductCategory);
                $em->remove($ProductCategory);
            }
            $em->persist($Product);
            $em->flush();

            $Category = $form->get('Category')->getData();
            $productCate = $this->createProductCategory($Product, $Category);
            $em->persist($productCate);
            $em->flush();            

            // Update
            /** @var ReceiptableDate[] $ReceiptableDates*/
            $ReceiptableDates = $form->get('ReceiptableDate')->getData();

            $ProductRDs = $Product->getProductReceiptableDates();
            foreach ($ProductRDs as $productRD) {
                $Product->removeProductReceiptableDate($productRD);
                $em->remove($productRD);
            }
            $em->flush();

            foreach ($ReceiptableDates as $receiptableDate) {
                $productRD = new ProductReceiptableDate();
                $productRD->setProduct($Product);
                $productRD->setProductId($Product->getId());
                $productRD->setReceiptableDate($receiptableDate);
                $productRD->setDateId($receiptableDate->getId());
                $productRD->setMaxQuantity(1);
                $Product->addProductReceiptableDate($productRD);
                $em->persist($productRD);
            }
            $em->persist($Product);
            $em->flush();

            // 画像の登録
            $add_images = $form->get('add_images')->getData();
            foreach ($add_images as $add_image) {
                $ProductImage = new \Eccube\Entity\ProductImage();
                $ProductImage
                    ->setFileName($add_image)
                    ->setProduct($Product)
                    ->setRank(1)
                    ->setCreator($Customer);
                $Product->addProductImage($ProductImage);
                $em->persist($ProductImage);

                // 移動
                $file = new File($app['config']['image_temp_realdir'].'/'.$add_image);
                $file->move($app['config']['image_save_realdir']);
            }

            // 画像の削除
            $delete_images = $form->get('delete_images')->getData();
            foreach ($delete_images as $delete_image) {
                $ProductImage = $app['eccube.repository.product_image']
                    ->findOneBy(array('file_name' => $delete_image));

                // 追加してすぐに削除した画像は、Entityに追加されない
                if ($ProductImage instanceof \Eccube\Entity\ProductImage) {
                    $Product->removeProductImage($ProductImage);
                    $em->remove($ProductImage);

                }
                $em->persist($Product);

                // 削除
                if (!empty($delete_image)) {
                    $fs = new Filesystem();
                    $fs->remove($app['config']['image_save_realdir'].'/'.$delete_image);
                }
            }
            $em->persist($Product);
            $em->flush();

            $ranks = $request->get('rank_images');
            if ($ranks) {
                foreach ($ranks as $rank) {
                    list($filename, $rank_val) = explode('//', $rank);
                    /** @var ProductImage $ProductImage */
                    $ProductImage = $app['eccube.repository.product_image']->findOneBy(array('file_name' => $filename, 'Product' => $Product));
                    $ProductImage->setRank($rank_val);
                    $em->persist($ProductImage);
                }
            }
            $em->flush();

            // 商品タグの登録
            // 商品タグを一度クリア
            $ProductTags = $Product->getProductTag();
            foreach ($ProductTags as $ProductTag) {
                $Product->removeProductTag($ProductTag);
                $em->remove($ProductTag);
            }

            // 商品タグの登録
            $Tags = $form->get('Tag')->getData();
            foreach ($Tags as $Tag) {
                $ProductTag = new ProductTag();
                $ProductTag->setProduct($Product)->setTag($Tag)->setCreator($Customer);
                $Product->addProductTag($ProductTag);
                $em->persist($ProductTag);
            }

            $Product->setUpdateDate(new \DateTime());
            $em->flush();

            return $app->redirect($app->url('farm_home', array('id' => $Customer->getId())));
        }

        return $app->render('Farm/farm_item.twig', array(
            'Product' => $Product,
            'id' => $id,
            'form' => $form->createView(),
            'TargetCustomer' => $Customer
        ));
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detail(Application $app, Request $request, $id)
    {
        /** @var ProductRepository $productRepo */
        $productRepo = $app['eccube.repository.product'];
        /** @var Product $Product */
        $Product = $productRepo->find($id);

        if (!$Product) {
            throw new NotFoundHttpException();
        }
        $TargetCustomer = $Product->getCreator();
        $Customer = $app->user();

        $voice = new CustomerVoice();
        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createBuilder('farm_voice', $voice);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!($Customer instanceof Customer)) {
                return $app->redirect($app->url('mypage_login'));
            }
            /** @var UploadedFile $image */
            $image = $form['file_name']->getData();
            if ($image) {
                $extension = $image->getClientOriginalExtension();
                $fileName = date('mdHis').uniqid('_').'.'.$extension;
                $image->move($app['config']['image_save_realdir'], $fileName);
                $voice->setFileName($fileName);
            }
            $voice->setCustomer($Customer);
            $voice->setTargetCustomer($TargetCustomer);
            $voice->setProduct($Product);
            $app['orm.em']->persist($voice);
            $app['orm.em']->flush();

        }
        /** @var CustomerVoiceRepository $voiceRepo */
        $voiceRepo = $app['eccube.repository.customer_voice'];
        $CustomerVoice = $voiceRepo->findBy(array('Product' => $Product), array('create_date' => 'ASC'));
        $ProductRate = $Product->getProductRate();
        if (EntityUtil::isEmpty($ProductRate)) {
            $ProductRate = null;
        }
        return $app->render('Farm/farm_item_detail.twig', array(
            'subtitle' => $Product->getName(),
            'Product' => $Product,
            'form' => $form->createView(),
            'CustomerVoice' =>$CustomerVoice,
            'ProductRate' => $ProductRate,
        ));
    }
}
