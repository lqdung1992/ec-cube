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
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\ChangePassword;
use Eccube\Entity\Customer;
use Eccube\Entity\CustomerImage;
use Eccube\Entity\CustomerVoice;
use Eccube\Entity\Follow;
use Eccube\Entity\Master\CustomerRole;
use Eccube\Entity\Master\ReceiptableDate;
use Eccube\Entity\Notification;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductRate;
use Eccube\Entity\ProductReceiptableDate;
use Eccube\Entity\ProductTag;
use Eccube\Exception\CartException;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\CustomerVoiceRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Service\CartService;
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
class FarmerController extends AbstractController
{
    /**
     * Profile page
     *
     * @param Application $app
     * @param Request $request
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Application\AuthenticationCredentialsNotFoundException
     */
    public function index(Application $app, Request $request, $id = null)
    {
        if (!$app->isGranted("IS_AUTHENTICATED_FULLY")) {
            return $app->redirect($app->url('mypage_login'));
        }

        $isOwner = false;
        /** @var Customer $Customer */
        $Customer = $app->user();
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

            if ($TargetCustomer->getId() != $Customer->getId()) {
                $app['eccube.repository.notification']
                    ->insertNotice($Customer, $TargetCustomer, Notification::TYPE_PROFILE, $id);
            }

            return $app->redirect($app->url('farm_profile', array('id' => $TargetCustomer->getId(), 'voice' => 1)));
        }
        /** @var CustomerVoiceRepository $voiceRepo */
        $voiceRepo = $app['eccube.repository.customer_voice'];
        $CustomerVoice = $voiceRepo->findBy(array('TargetCustomer' => $TargetCustomer, 'Product' => null), array('create_date' => 'ASC'));

        /** @var ProductRepository $productRepo */
        $productRepo = $app['eccube.repository.product'];
        $products = $productRepo->getProductQueryBuilderByCustomer($TargetCustomer)->getQuery()->getResult();

        return $app->render('Farm/farm_profile.twig', array(
            'TargetCustomer' => $TargetCustomer,
            'CustomerVoice' => $CustomerVoice,
            'form' => $form->createView(),
            'is_owner' => $isOwner,
            'Products' => $products
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
        $ProductClass->setStockUnlimited(false);

        $form['class']->setData($ProductClass);

        $images = array();
        $ProductImages = $Product->getProductImage();
        foreach ($ProductImages as $ProductImage) {
            $images[] = $ProductImage->getFileName();
        }
        $form['images']->setData($images);

        $collection = $Product->getProductCategories();
        if (count($collection) > 0) {
            $category = $collection->first()->getCategory();
            $form['Category']->setData($category);
        }

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
            // set farmer
            $Product->setCreator($Customer);

            /** @var EntityManager $em */
            $em = $app['orm.em'];

            /** @var ProductClass $ProductClass */
            $ProductClass = $form['class']->getData();
            $ProductType = $app['eccube.repository.master.product_type']->find(1);
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
            $ProductStock->setStock($ProductClass->getStock());
            $em->persist($ProductStock);

            /* @var $Product \Eccube\Entity\Product */
            foreach ($Product->getProductCategories() as $ProductCategory) {
                $Product->removeProductCategory($ProductCategory);
                $em->remove($ProductCategory);
            }
            // Remove Receiptable date
            $ProductRDs = $Product->getProductReceiptableDates();
            foreach ($ProductRDs as $productRD) {
                $Product->removeProductReceiptableDate($productRD);
                $em->remove($productRD);
            }

            $em->persist($Product);
            $em->flush();

            $Category = $form->get('Category')->getData();
            $productCate = $this->createProductCategory($Product, $Category);
            $em->persist($productCate);

            /** @var ReceiptableDate[] $ReceiptableDates*/
            $ReceiptableDates = $form->get('ReceiptableDate')->getData();
            // Loop step
            $interval = \DateInterval::createFromDateString('1 day');
            $dateEnd = clone $ProductClass->getProductionEndDate();
            $dateStart = $ProductClass->getProductionStartDate();
            $now = new \DateTime();
            if ($dateStart->getTimestamp() < $now) {
                $dateStart = new \DateTime($now->format('Y/m/d'));
            }
            $period = new \DatePeriod($dateStart, $interval, $dateEnd->modify('+1 day'));
            $arrDateId = array();
            foreach ($ReceiptableDates as $receiptableDate) {
                $arrDateId[$receiptableDate->getId()] = $receiptableDate;
            }
            foreach ($period as $date) {
                $dateId = $date->format('N');
                if (in_array($dateId, array_keys($arrDateId))) {
                    $productRD = new ProductReceiptableDate();
                    $productRD->setProduct($Product);
                    $productRD->setProductId($Product->getId());
                    $productRD->setDate($date);
                    $productRD->setReceiptableDate($arrDateId[$dateId]);
                    $productRD->setDateId($dateId);
                    $productRD->setMaxQuantity($ProductClass->getStock());
                    $Product->addProductReceiptableDate($productRD);
                    $em->persist($productRD);
                }
            }

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
     * @throws Application\AuthenticationCredentialsNotFoundException
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

        $voice = new CustomerVoice();
        /** @var FormBuilder $builder */
        $builder = $app['form.factory']->createBuilder('farm_voice', $voice);
        $form = $builder->getForm();

        /* @var $builderCart \Symfony\Component\Form\FormBuilderInterface */
        $builderCart = $app['form.factory']->createNamedBuilder('', 'add_cart', null, array(
            'product' => $Product,
            'id_add_product_id' => false,
        ));
        $cartForm = $builderCart->getForm();

        $mode = $request->get('mode');
        switch ($mode) {
            case 'add_cart':
                $cartForm->handleRequest($request);
                if ($cartForm->isSubmitted() && $cartForm->isValid()) {
                    $addCartData = $cartForm->getData();
                    $arrQuantity = array_filter($addCartData['quantity'], function ($item) {
                        return $item > 0;
                    });
                    /** @var CartService $cartService */
                    $cartService = $app['eccube.service.cart'];
                    try {
                        $cartService->addProduct($addCartData['product_class_id'], $arrQuantity)->save();
                    } catch (CartException $e) {
                        $app->addRequestError($e->getMessage());
                    }

                    return $app->redirect($app->url('cart'));
                }
                break;
            case 'add_voice':
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    if (!$app->isGranted("IS_AUTHENTICATED_FULLY")) {
                        return $app->redirect($app->url('mypage_login'));
                    }
                    $Customer = $app->user();
                    $TargetCustomer = $Product->getCreator();

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
                    if ($TargetCustomer->getId() != $Customer->getId()) {
                        $app['eccube.repository.notification']
                            ->insertNotice($Customer, $TargetCustomer, Notification::TYPE_PRODUCT, $Product->getId(), $Product->getName());
                    }
                }
                break;
            default:
                break;
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
            'CustomerVoice' => $CustomerVoice,
            'ProductRate' => $ProductRate,
            'cartForm' => $cartForm->createView(),
        ));
    }



    /*
     * ProductCategory作成
     * @param \Eccube\Entity\Product $Product
     * @param \Eccube\Entity\Category $Category
     * @return \Eccube\Entity\ProductCategory
     */
    private function createProductCategory($Product, $Category, $count = 1)
    {
        $ProductCategory = new \Eccube\Entity\ProductCategory();
        $ProductCategory->setProduct($Product);
        $ProductCategory->setProductId($Product->getId());
        $ProductCategory->setCategory($Category);
        $ProductCategory->setCategoryId($Category->getId());
        $ProductCategory->setRank($count);

        return $ProductCategory;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws Application\AuthenticationCredentialsNotFoundException
     */
    public function countLike(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('リクエストが不正です');
        }

        if (!$app->isGranted(CustomerRole::RECIPIENT)) {
            throw new NotFoundHttpException();
        }

        $id = $request->get('product_id');
        $type = $request->get('type');
        /** @var Product $Product */
        $Product = $app['eccube.repository.product']->find($id);
        if ($Product) {
            /**@var $ProductRate ProductRate*/
            $ProductRate = $Product->getProductRate();
            if ($ProductRate instanceof ProductRate) {
                switch ($type) {
                    case 'like_count' :
                        $count = $ProductRate->getLikeCount() + 1;
                        $ProductRate->setLikeCount($count);
                        break;
                    case 'delicious_count' :
                        $count = $ProductRate->getDeliciousCount() + 1;
                        $ProductRate->setDeliciousCount($count);
                        break;
                    case 'fresh_count' :
                        $count = $ProductRate->getFreshCount() + 1;
                        $ProductRate->setFreshCount($count);
                        break;
                    case 'vivid_count' :
                        $count = $ProductRate->getVividCount() + 1;
                        $ProductRate->setVividCount($count);
                        break;
                    default:
                        $count = $ProductRate->getAromaCount() + 1;
                        $ProductRate->setAromaCount($count);
                        break;
                }
            } else {
                $ProductRate = new ProductRate();
                $ProductRate->setProduct($Product);
                switch ($type) {
                    case 'like_count' :
                        $ProductRate->setLikeCount(1);
                        break;
                    case 'delicious_count' :
                        $ProductRate->setDeliciousCount(1);
                        break;
                    case 'fresh_count' :
                        $ProductRate->setFreshCount(1);
                        break;
                    case 'vivid_count' :
                        $ProductRate->setVividCount(1);
                        break;
                    default:
                        $ProductRate->setAromaCount(1);
                        break;
                }
                $Product->setProductRate($ProductRate);
            }

            $app['orm.em']->persist($ProductRate);
            $app['orm.em']->flush();
        }

        return $app->json(array('success' => true), 200);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @throws Application\AuthenticationCredentialsNotFoundException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function follow(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);
        if (!$app->isGranted(CustomerRole::RECIPIENT)) {
            throw new NotFoundHttpException();
        }

        /** @var CustomerRepository $customerRepo */
        $customerRepo = $app['eccube.repository.customer'];
        $TargetCustomer = $customerRepo->find($id);
        if (!$TargetCustomer) {
            throw new NotFoundHttpException();
        }

        /** @var Customer $Customer */
        $Customer = $app->user();

        $follow = new Follow();
        $follow->setTargetCustomer($TargetCustomer);
        $follow->setCustomer($Customer);
        $Customer->addFollow($follow);
        /** @var EntityManager $em */
        $em = $app['orm.em'];
        $em->persist($follow);
        $em->flush();

        return $app->redirect($app->url('farm_profile', array('id' => $id)));
    }
}
