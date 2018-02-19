<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/13/2018
 * Time: 11:47
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\Master\AmountUnitType;
use Eccube\Entity\Master\CultivationMethod;
use Eccube\Repository\Master\AmountUnitTypeRepository;
use Eccube\Repository\Master\CultivationMethodRepository;

/**
 * Class Version20180213114700
 * @package DoctrineMigrations
 */
class Version20180213114700 extends AbstractMigration
{
    protected $table = 'dtb_product_receiptable_date';

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
        /** @var AmountUnitTypeRepository $repo */
        $repo = $app['eccube.repository.master.amount_unit_type'];
        $unit = $repo->find(1);
        if (!$unit) {
            $unit = new AmountUnitType();
            $unit->setId(1);
            $unit->setName('kg');
            $unit->setRank(1);
            $em->persist($unit);
        }

        /** @var CultivationMethodRepository $repo */
        $repo = $app['eccube.repository.master.cultivation_method'];
        $CulM = $repo->find(1);
        if (!$CulM) {
            $CulM = new CultivationMethod();
            $CulM->setId(1);
            $CulM->setName('土耕');
            $CulM->setRank(1);
            $em->persist($CulM);
        }
        $CulM = $repo->find(2);
        if (!$CulM) {
            $CulM = new CultivationMethod();
            $CulM->setId(2);
            $CulM->setName('その他');
            $CulM->setRank(2);
            $em->persist($CulM);
        }
        $em->flush();

        $table = $schema->getTable('dtb_product_class');
        if (!$table->hasColumn('production_start_date')) {
            $table->addColumn('production_start_date', 'datetime', array('NotNull' => false));
        }
        if (!$table->hasColumn('production_end_date')) {
            $table->addColumn('production_end_date', 'datetime', array('NotNull' => false));
        }
        if (!$table->hasColumn('amount')) {
            $table->addColumn('amount', 'integer', array('NotNull' => false, 'unsigned' => true));
        }
        if (!$table->hasColumn('amount_unit_type_id')) {
            $table->addColumn('amount_unit_type_id', 'integer', array('NotNull' => false, 'unsigned' => true, 'Default' => '1'));
        }
        if (!$table->hasColumn('cultivation_method_id')) {
            $table->addColumn('cultivation_method_id', 'integer', array('NotNull' => false, 'unsigned' => true, 'Default' => '1'));
        }
        if (!$table->hasColumn('amount_per_container')) {
            $table->addColumn('amount_per_container', 'integer', array('NotNull' => false, 'unsigned' => true, 'Default' => '0'));
        }


        if (!$schema->hasTable($this->table)) {
            $table = $schema->createTable($this->table);
            $table->addColumn('product_id', 'integer', array('NotNull' => true));
            $table->addColumn('date_id', 'integer', array('NotNull' => true));
            $table->addColumn('max_quantity', 'integer', array('NotNull' => true));
            $table->setPrimaryKey(array('product_id', 'date_id'));

            $targetTable = $schema->getTable('dtb_product');
            $table->addForeignKeyConstraint(
                $targetTable,
                array('product_id'),
                array('product_id')
            );

            $targetTable2 = $schema->getTable('mtb_receiptable_date');
            $table->addForeignKeyConstraint(
                $targetTable2,
                array('date_id'),
                array('id')
            );
        }

        return true;
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}