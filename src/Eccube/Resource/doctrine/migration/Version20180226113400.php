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
 * Class Version20180226113400
 * @package src\Eccube\Resource\doctrine\migration
 */
class Version20180226113400 extends AbstractMigration
{
    /**
     * @var array table name
     */
    protected $table = array('dtb_bus', 'mtb_bus_area', 'dtb_bus_stop', 'mtb_bus_status', 'mtb_route', 'dtb_route_detail', 'dtb_route_schedule', 'mtb_route_schedule_status');

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Bus',
        'Eccube\Entity\Master\BusArea',
        'Eccube\Entity\BusStop',
        'Eccube\Entity\Master\BusStatus',
        'Eccube\Entity\Master\Route',
        'Eccube\Entity\RouteDetail',
        'Eccube\Entity\RouteSchedule',
        'Eccube\Entity\Master\RouteScheduleStatus'
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
        // TODO: Implement down() method.
    }
}