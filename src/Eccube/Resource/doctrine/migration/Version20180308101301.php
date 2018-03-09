<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 03/07/2018
 * Time: 10:13
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Entity\Master\SafetyPercent;

/**
 * Class Version20180308101301
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180308101301 extends AbstractMigration
{
    /**
     * @var array table name
     */
    protected $table = array('mtb_safety_percent');

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Master\SafetyPercent',
    );

    /**
     * @param Schema $schema
     * @return bool
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

        /** @var SafetyPercentRepository $repo */
        $Safety = new SafetyPercent();
        $Safety->setId(1)
            ->setName('安全率')
            ->setRank(1);
        $em->persist($Safety);
        $em->flush($Safety);

        //add container_amount to order table
        $table = $schema->getTable('dtb_order');
        if (!$table->hasColumn('container_amount')) {
            $this->addSql('ALTER TABLE dtb_order ADD container_amount int;');
        }

        return true;
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}