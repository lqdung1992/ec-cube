<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/05/2018
 * Time: 11:02 PM
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180205221000 extends AbstractMigration
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
        if ($table->hasColumn('customer_role')) {
            // need index?
//            $this->addSql('ALTER TABLE dtb_customer ADD CONSTRAINT FK_dtb_customer_mtb_customer_role FOREIGN KEY (customer_role) REFERENCES mtb_customer_role (id) ON UPDATE CASCADE;');
        }
        if ($table->hasColumn('bus_stop')) {
            // need index?
//            $this->addSql('ALTER TABLE dtb_customer ADD CONSTRAINT FK_dtb_customer_mtb_bus_stop FOREIGN KEY (bus_stop) REFERENCES mtb_bus_stop (id) ON UPDATE CASCADE;');
        }

        return true;
    }

    /**
     * @param Schema $schema
     * @param $tableName
     * @param array $columns
     * @param $indexName
     * @param array $length
     * @return bool
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function createIndex(Schema $schema, $tableName, array $columns, $indexName, array $length = array())
    {
        if (!$schema->hasTable($tableName)) {
            return false;
        }

        $table = $schema->getTable($tableName);
        if (!$table->hasIndex($indexName)) {
            if ($this->connection->getDatabasePlatform()->getName() == "mysql" && !empty($length)) {
                $cols = array();
                foreach ($length as $column => $len) {
                    $cols[] = sprintf('%s(%d)', $column, $len);
                }
                $this->addSql('CREATE INDEX '.$indexName.' ON '.$tableName.'('.implode(', ', $cols).');');
            } else {
                $table->addIndex($columns, $indexName);
            }
            return true;
        }
        return false;
    }
}
