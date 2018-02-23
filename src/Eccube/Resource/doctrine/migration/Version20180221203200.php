<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 2/21/2018
 * Time: 20:32
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class Version20180221203200
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180221203200 extends AbstractMigration
{
    /**
     * @var array table name
     */
    protected $table = array('dtb_product_rate');

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\ProductRate',
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
    }
}