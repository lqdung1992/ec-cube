<?php

namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;
use Eccube\Common\Constant;
use Eccube\Entity\Customer;
use Eccube\Entity\Farmer;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Util\Str;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * CustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FarmerRepository extends EntityRepository implements UserProviderInterface
{
    protected $app;

    public function setApplication($app)
    {
        $this->app = $app;
    }

    public function newFarmer()
    {
        $farmer = new \Eccube\Entity\Farmer();
        $Status = $this->getEntityManager()
            ->getRepository('Eccube\Entity\Master\CustomerStatus')
            ->find(1);

        $farmer
            ->setStatus($Status)
            ->setDelFlg(0);

        return $farmer;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        // 本会員ステータスの会員のみ有効.
        $CustomerStatus = $this
            ->getEntityManager()
            ->getRepository('Eccube\Entity\Master\CustomerStatus')
            ->find(CustomerStatus::ACTIVE);

        $query = $this->createQueryBuilder('f')
            ->where('f.email = :email')
            ->andWhere('f.del_flg = :delFlg')
            ->andWhere('f.Status =:CustomerStatus')
            ->setParameters(array(
                'email' => $username,
                'delFlg' => Constant::DISABLED,
                'CustomerStatus' => $CustomerStatus,
            ))
            ->setMaxResults(1)
            ->getQuery();
        $farmer = $query->getOneOrNullResult();
        if (!$farmer) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $farmer;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Customer) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Eccube\Entity\Farmer';
    }

    public function getQueryBuilderBySearchData($searchData)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->andWhere('c.del_flg = 0');

        if (isset($searchData['multi']) && Str::isNotBlank($searchData['multi'])) {
            //スペース除去
            $clean_key_multi = preg_replace('/\s+|[　]+/u', '', $searchData['multi']);
            $id = preg_match('/^\d+$/', $clean_key_multi) ? $clean_key_multi : null;
            $qb
                ->andWhere('c.id = :customer_id OR CONCAT(c.name01, c.name02) LIKE :name OR CONCAT(c.kana01, c.kana02) LIKE :kana OR c.email LIKE :email')
                ->setParameter('customer_id', $id)
                ->setParameter('name', '%' . $clean_key_multi . '%')
                ->setParameter('kana', '%' . $clean_key_multi . '%')
                ->setParameter('email', '%' . $clean_key_multi . '%');
        }

        // Pref
        if (!empty($searchData['pref']) && $searchData['pref']) {
            $qb
                ->andWhere('c.Pref = :pref')
                ->setParameter('pref', $searchData['pref']->getId());
        }

        // sex
        if (!empty($searchData['sex']) && count($searchData['sex']) > 0) {
            $sexs = array();
            foreach ($searchData['sex'] as $sex) {
                $sexs[] = $sex->getId();
            }

            $qb
                ->andWhere($qb->expr()->in('c.Sex', ':sexs'))
                ->setParameter('sexs', $sexs);
        }

        if (!empty($searchData['birth_month']) && $searchData['birth_month']) {
            $qb
                ->andWhere('EXTRACT(MONTH FROM c.birth) = :birth_month')
                ->setParameter('birth_month', $searchData['birth_month']);
        }

        // birth
        if (!empty($searchData['birth_start']) && $searchData['birth_start']) {
            $date = $searchData['birth_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.birth >= :birth_start')
                ->setParameter('birth_start', $date);
        }
        if (!empty($searchData['birth_end']) && $searchData['birth_end']) {
            $date = clone $searchData['birth_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.birth < :birth_end')
                ->setParameter('birth_end', $date);
        }

        // tel
        if (isset($searchData['tel']) && Str::isNotBlank($searchData['tel'])) {
            $qb
                ->andWhere('CONCAT(c.tel01, c.tel02, c.tel03) LIKE :tel')
                ->setParameter('tel', '%' . $searchData['tel'] . '%');
        }

        // buy_total
        if (isset($searchData['buy_total_start']) && Str::isNotBlank($searchData['buy_total_start'])) {
            $qb
                ->andWhere('c.buy_total >= :buy_total_start')
                ->setParameter('buy_total_start', $searchData['buy_total_start']);
        }
        if (isset($searchData['buy_total_end']) && Str::isNotBlank($searchData['buy_total_end'])) {
            $qb
                ->andWhere('c.buy_total <= :buy_total_end')
                ->setParameter('buy_total_end', $searchData['buy_total_end']);
        }

        // buy_times
        if (!empty($searchData['buy_times_start']) && $searchData['buy_times_start']) {
            $qb
                ->andWhere('c.buy_times >= :buy_times_start')
                ->setParameter('buy_times_start', $searchData['buy_times_start']);
        }
        if (!empty($searchData['buy_times_end']) && $searchData['buy_times_end']) {
            $qb
                ->andWhere('c.buy_times <= :buy_times_end')
                ->setParameter('buy_times_end', $searchData['buy_times_end']);
        }

        // create_date
        if (!empty($searchData['create_date_start']) && $searchData['create_date_start']) {
            $date = $searchData['create_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.create_date >= :create_date_start')
                ->setParameter('create_date_start', $date);
        }
        if (!empty($searchData['create_date_end']) && $searchData['create_date_end']) {
            $date = clone $searchData['create_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.create_date < :create_date_end')
                ->setParameter('create_date_end', $date);
        }

        // update_date
        if (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }
        if (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // last_buy
        if (!empty($searchData['last_buy_start']) && $searchData['last_buy_start']) {
            $date = $searchData['last_buy_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.last_buy_date >= :last_buy_start')
                ->setParameter('last_buy_start', $date);
        }
        if (!empty($searchData['last_buy_end']) && $searchData['last_buy_end']) {
            $date = clone $searchData['last_buy_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('c.last_buy_date < :last_buy_end')
                ->setParameter('last_buy_end', $date);
        }

        // status
        if (!empty($searchData['customer_status']) && count($searchData['customer_status']) > 0) {
            $qb
                ->andWhere($qb->expr()->in('c.Status', ':statuses'))
                ->setParameter('statuses', $searchData['customer_status']);
        }

        // buy_product_name、buy_product_code
        if (isset($searchData['buy_product_code']) && Str::isNotBlank($searchData['buy_product_code'])) {
            $qb
                ->leftJoin('c.Orders', 'o')
                ->leftJoin('o.OrderDetails', 'od')
                ->andWhere('od.product_name LIKE :buy_product_name OR od.product_code LIKE :buy_product_name')
                ->setParameter('buy_product_name', '%' . $searchData['buy_product_code'] . '%');
        }

        // Order By
        $qb->addOrderBy('c.update_date', 'DESC');

        return $qb;
    }

    /**
     * ユニークなシークレットキーを返す
     * @param $app
     * @return string
     */
    public function getUniqueSecretKey($app)
    {
        $unique = Str::random(32);
        $farmer = $app['eccube.repository.farmer']->findBy(array(
            'secret_key' => $unique,
        ));
        if (count($farmer) == 0) {
            return $unique;
        } else {
            return $this->getUniqueSecretKey($app);
        }
    }

    /**
     * ユニークなパスワードリセットキーを返す
     * @param $app
     * @return string
     */
    public function getUniqueResetKey($app)
    {
        $unique = Str::random(32);
        $farmer = $app['eccube.repository.farmer']->findBy(array(
                        'reset_key' => $unique,
        ));
        if (count($farmer) == 0) {
            return $unique;
        } else {
            return $this->getUniqueResetKey($app);
        }
    }

    /**
     * saltを生成する
     *
     * @param $byte
     * @return string
     */
    public function createSalt($byte)
    {
        $generator = new SecureRandom();

        return bin2hex($generator->nextBytes($byte));
    }

    /**
     * 入力されたパスワードをSaltと暗号化する
     *
     * @param $app
     * @param  Farmer $farmer
     * @return mixed
     */
    public function encryptPassword($app, \Eccube\Entity\Farmer $farmer)
    {
        $encoder = $app['security.encoder_factory']->getEncoder($farmer);

        return $encoder->encodePassword($farmer->getPassword(), $farmer->getSalt());
    }

    public function getNonActiveCustomerBySecretKey($secret_key)
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.del_flg = 0 AND f.secret_key = :secret_key')
            ->leftJoin('f.Status', 's')
            ->andWhere('s.id = :status')
            ->setParameter('secret_key', $secret_key)
            ->setParameter('status', CustomerStatus::NONACTIVE);
        $query = $qb->getQuery();

        return $query->getSingleResult();
    }

    public function getActiveFarmerByEmail($email)
    {
        $query = $this->createQueryBuilder('f')
            ->where('f.email = :email AND f.Status = :status')
            ->setParameter('email', $email)
            ->setParameter('status', CustomerStatus::ACTIVE)
            ->setMaxResults(1)
            ->getQuery();

        $Customer = $query->getOneOrNullResult();

        return $Customer;
    }

    public function getActiveFarmerByResetKey($reset_key)
    {
        $query = $this->createQueryBuilder('f')
            ->where('f.reset_key = :reset_key AND f.Status = :status AND f.reset_expire >= :reset_expire')
            ->setParameter('reset_key', $reset_key)
            ->setParameter('status', CustomerStatus::ACTIVE)
            ->setParameter('reset_expire', new \DateTime())
            ->getQuery();

        $Customer = $query->getSingleResult();

        return $Customer;
    }

    public function getResetPassword()
    {
        return Str::random(8);
    }
}
