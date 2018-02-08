<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/08/2018
 * Time: 15:23
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class Version20180208152300 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const table = 'mtb_approval_status';

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Master\ApprovalStatus',
    );

    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function up(Schema $schema)
    {
        $this->createTable($schema);
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
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function createTable(Schema $schema)
    {
        $app = \Eccube\Application::getInstance();
        /** @var EntityManagerInterface $em */
        $em = $app['orm.em'];
        if (!$schema->hasTable(self::table)) {
            $classes = array(
                $em->getClassMetadata($this->entities[0]),
            );
            /** @var  $tool */
            $tool = new SchemaTool($em);
            $tool->createSchema($classes);
        }


        // add column to customer table
        if (!$schema->hasTable('dtb_customer')) {
            return true;
        }
        $table = $schema->getTable('dtb_customer');
        if (!$table->hasColumn('approval_status')) {
            $table->addColumn('approval_status', 'smallint', array('NotNull' => true, 'Default' => 1));
        }

        return true;
    }
}
