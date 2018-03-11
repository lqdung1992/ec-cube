<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 09/03/2018
 * Time: 10:49 PM
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class Version20180309224900
 * @package DoctrineMigrations
 */
class Version20180309224900 extends AbstractMigration
{
    /**
     * @var array table name
     */
    protected $table = array('dtb_product_receiptable_date');

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\ProductReceiptableDate',
    );

    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function up(Schema $schema)
    {
        if ($schema->hasTable('dtb_product_receiptable_date')) {
            return;
        }

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
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}