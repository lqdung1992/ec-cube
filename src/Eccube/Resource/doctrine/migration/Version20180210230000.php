<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/10/2018
 * Time: 11:00 PM
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

class Version20180210230000 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const table = 'dtb_customer_image';

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\CustomerImage',
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
        /** @var EntityManager $em */
        $em = $app['orm.em'];
        if (!$schema->hasTable(self::table)) {
            $classes = array(
                $em->getClassMetadata($this->entities[0]),
            );
            $tool = new SchemaTool($em);
            $tool->createSchema($classes);
        }

        return true;
    }
}