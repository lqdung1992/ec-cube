<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 3/10/2018
 * Time: 11:16 PM
 */

namespace Eccube\Controller\Farm;


use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CustomerRole;
use Eccube\Entity\Notification;
use Eccube\Repository\NewsRepository;
use Eccube\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NoticeController extends AbstractController
{
    /**
     * @param Application $app
     * @param Request $request
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Application\AuthenticationCredentialsNotFoundException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function index(Application $app, Request $request, $id = null)
    {
//        if ($app->isGranted(CustomerRole::FARMER)) {
//            return $app->redirect($app->url('mypage_login'));
//        }
        $Customer = $app->user();

        /** @var NotificationRepository $noticeRepo */
        $noticeRepo = $app['eccube.repository.notification'];
        if ($id) {
//            $this->isTokenValid($app);
            /** @var Notification $notice */
            $notice = $noticeRepo->find($id);
            if (!$notice) {
                throw new NotFoundHttpException();
            }

            if ($notice->getTargetCustomer()->getId() != $Customer->getId()) {
                throw new NotFoundHttpException();
            }

            /** @var EntityManager $em */
            $em = $app['orm.em'];
            $notice->setDelFlg(Constant::ENABLED);
            $idLink = $notice->getIdLink();
            $em->persist($notice);
            $em->flush();
            switch ($notice->getNoticeType()) {
                case Notification::TYPE_ORDER:
                    return $app->redirect($app->url('order', array('id' => $idLink)));
                    break;
                case Notification::TYPE_PROFILE:
                    return $app->redirect($app->url('farm_profile_own').'#comment');
                    break;
                case Notification::TYPE_PRODUCT:
                    return $app->redirect($app->url('farm_item_detail', array('id' => $idLink))."#comment");
                    break;
                default:
                    break;
            }
        }
        $list = $noticeRepo->findBy(array('TargetCustomer' => $Customer), array('create_date' => 'DESC'));

        /** @var NewsRepository $newsRepo */
        $newsRepo = $app['eccube.repository.news'];
        $listNews = $newsRepo->findAll();

        return $app->render('Farm/farm_notice.twig', array('Notices' => $list, 'News' => $listNews));
    }
}