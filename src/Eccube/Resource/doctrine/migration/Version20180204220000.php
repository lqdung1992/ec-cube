<?php

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
    const table = 'dtb_farmer';
    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Farmer',
    );
    protected $sequence = array(
        'dtb_farmer_farmer_id_seq',
    );

    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function up(Schema $schema)
    {
        $this->createFarmerTable($schema);
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
    protected function createFarmerTable(Schema $schema)
    {
        if ($schema->hasTable(self::table)) {
            return true;
        }
        $app = \Eccube\Application::getInstance();
        /** @var EntityManagerInterface $em */
        $em = $app['orm.em'];
        $classes = array(
            $em->getClassMetadata($this->entities[0]),
        );
        /** @var  $tool */
        $tool = new SchemaTool($em);
        $tool->createSchema($classes);

        return true;
    }
}
