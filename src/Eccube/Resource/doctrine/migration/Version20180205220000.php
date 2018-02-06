<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/05/2018
 * Time: 08:02 PM
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180205220000 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const table = 'dtb_customer';
    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Customer',
    );

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $this->alterCustomerTable($schema);
    }
    /**
     * Down method
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }

    /**
     * @param Schema $schema
     * @return bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function alterCustomerTable(Schema $schema)
    {
        if (!$schema->hasTable(self::table)) {
            return true;
        }
        $table = $schema->getTable(self::table);
        if (!$table->hasColumn('customer_role')) {
            $table->addColumn('customer_role', 'string', array('NotNull' => false));
        }
        if (!$table->hasColumn('bus_stop')) {
            $table->addColumn('bus_stop', 'string', array('NotNull' => false));
        }

        $table->changeColumn('name02', array('NotNull' => false));
        return true;
    }
}
