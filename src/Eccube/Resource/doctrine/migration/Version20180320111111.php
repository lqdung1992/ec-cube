<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 17/03/2018
 * Time: 10:33 PM
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Class Version20180320111111
 * @package DoctrineMigrations
 */
class Version20180320111111 extends AbstractMigration
{
    /**
     * @var array table name
     */
    protected $table = array('dtb_farmer_discount');

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\FarmerDiscount',
    );

    /**
     * @param Schema $schema
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
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farmer Discount', 'farm_discount', 'Farm/farm_discount_search', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL)");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farmer Discount Setting', 'set_farm_discount', 'Farm/set_farm_discount', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL)");
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}