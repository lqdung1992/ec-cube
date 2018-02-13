<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/04/2018
 * Time: 11:02 AM
 */
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class Version20180204220000 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const table = 'mtb_customer_role';

    const table2 = 'mtb_bus_stop';
    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Master\CustomerRole',
        'Eccube\Entity\Master\BusStop',
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

        if (!$schema->hasTable(self::table2)) {
            $classes = array(
                $em->getClassMetadata($this->entities[1]),
            );
            /** @var  $tool */
            $tool = new SchemaTool($em);
            $tool->createSchema($classes);
        }

        return true;
    }
}
