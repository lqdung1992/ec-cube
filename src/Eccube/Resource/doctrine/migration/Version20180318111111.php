<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 03/14/2018
 * Time: 16:09
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Entity\Master\ReceiverContainerType;
use Eccube\Entity\Master\ReceiverGetTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class Version20180318111111
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180318111111 extends AbstractMigration
{
    /**
     * @var array table name
     */
    protected $table = array('mtb_receiver_get_time', 'mtb_receiver_container_type');

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Master\ReceiverGetTime',
        'Eccube\Entity\Master\ReceiverContainerType',
    );

    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function up(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        /** @var EntityManager $em */
        $em = $app['orm.em'];
        foreach ($this->table as $key => $table) {
            if (!$schema->hasTable($table)) {
                $classes = array(
                    $em->getClassMetadata($this->entities[$key]),
                );
                $tool = new SchemaTool($em);
                $tool->createSchema($classes);
            }
        }

        $ReceiverGetTime = new ReceiverGetTime();
        $ReceiverGetTime->setName('午前着');
        $ReceiverGetTime->setID(1);
        $ReceiverGetTime->setRank(1);
        $app['orm.em']->persist($ReceiverGetTime);

        $ReceiverGetTime = new ReceiverGetTime();
        $ReceiverGetTime->setName('午後着');
        $ReceiverGetTime->setID(2);
        $ReceiverGetTime->setRank(2);
        $app['orm.em']->persist($ReceiverGetTime);

        $ReceiverGetTime = new ReceiverGetTime();
        $ReceiverGetTime->setName('どちらでも');
        $ReceiverGetTime->setID(3);
        $ReceiverGetTime->setRank(3);
        $app['orm.em']->persist($ReceiverGetTime);

        $ReceiverContainerType = new ReceiverContainerType();
        $ReceiverContainerType->setName('折りたたみコンテナ');
        $ReceiverContainerType->setID(1);
        $ReceiverContainerType->setRank(1);
        $app['orm.em']->persist($ReceiverContainerType);

        $ReceiverContainerType = new ReceiverContainerType();
        $ReceiverContainerType->setName('段ボール');
        $ReceiverContainerType->setID(2);
        $ReceiverContainerType->setRank(2);
        $app['orm.em']->persist($ReceiverContainerType);

        $app['orm.em']->flush();

    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}