<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 04/03/2018
 * Time: 8:29 PM
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Master\DeviceType;

class Version20180304202900 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Service SignUp', 'farm_service', 'Farm/service_signup', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Service Profile', 'farm_service_profile', 'Farm/service_profile', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Service Profile Setting', 'farm_service_profile_setting', 'Farm/service_profile_setting', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Service Change pwd', 'farm_farmer_password_change', 'Farm/farmer_setting_change', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farm Profile', 'farm_profile', 'Farm/farm_profile', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farm Profile Edit', 'farm_profile_edit', 'Farm/farm_profile_edit', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farm Home', 'farm_home', 'Farm/farm_home', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farm Item New', 'farm_item_new', 'Farm/farm_item', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farm Item Edit', 'farm_item_edit', 'Farm/farm_item', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farm Item Detail', 'farm_item_detail', 'Farm/farm_item_detail', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Cart Complete', 'cart_complete', 'Cart/complete', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Order Status', 'order', 'Order/index', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farm History', 'farm_history', 'Farm/history', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
        $this->addSql("INSERT INTO dtb_page_layout (device_type_id, page_name, url, file_name, edit_flg, author, description, keyword, update_url, create_date, update_date, meta_robots) VALUES (10, 'Farm History Detail', 'farm_history_detail', 'Farm/history_detail', 2, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL);");
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}