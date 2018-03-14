<?php
/**
 * Created by PhpStorm.
 * User: lqdung
 * Date: 03/14/2018
 * Time: 16:09
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20180314160900
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180314160900 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Receiver Home', 'receiver_home', 'Receiver/receiver_home', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}