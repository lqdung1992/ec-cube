<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/13/2018
 * Time: 11:33
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

class Version20180213113300 extends AbstractMigration
{
    /**
     * @var array table name
     */
    protected $table = array('mtb_amount_unit_type', 'mtb_cultivation_method', 'mtb_receiptable_date');

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Master\AmountUnitType',
        'Eccube\Entity\Master\CultivationMethod',
        'Eccube\Entity\Master\ReceiptableDate',
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

        return true;
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}