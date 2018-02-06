<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/06/2018
 * Time: 11:02 PM
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180206230000 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const table = 'mtb_customer_role';

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema)
    {
        $this->alterTable($schema);
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
    protected function alterTable(Schema $schema)
    {
        if (!$schema->hasTable(self::table)) {
            return true;
        }
        $table = $schema->getTable(self::table);
        if (!$table->hasColumn('name_jp')) {
            $table->addColumn('name_jp', 'string', array('NotNull' => false, 'length' => 100));
        }

        return true;
    }
}
