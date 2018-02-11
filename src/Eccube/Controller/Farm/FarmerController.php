<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/10/2018
 * Time: 5:43 PM
 */
namespace Eccube\Controller\Farm;

use Eccube\Application;
use Eccube\Entity\ChangePassword;
use Eccube\Entity\Customer;
use Eccube\Entity\CustomerImage;
use Eccube\Entity\Master\CustomerRole;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class FarmerController
{
    public function index(Application $app, Request $request)
    {
        /** @var Customer $Customer */
        $Customer = $app->user();
        // Todo: check is farmer
        // || !$app->isGranted(CustomerRole::FARMER)
        if (!($Customer instanceof Customer)) {
            return $app->redirect($app->url('mypage_login'));
        }

        return $app->render('Farm/farm_profile.twig', array(
            'Customer' => $Customer,
        ));
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
    public function uploadCover(Application $app, Request $request)
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

            return $app->redirect($app->url('farm_profile_edit'));
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

        $images = $request->files->get('farm_profile_edit');

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
}