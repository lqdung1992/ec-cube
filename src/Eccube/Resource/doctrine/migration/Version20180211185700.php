<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 02/11/2018
 * Time: 6:57 PM
 */

namespace DoctrineMigrations;


use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

class Version20180211185700 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const table = 'dtb_customer_voice';

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\CustomerVoice',
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
        if (!$schema->hasTable(self::table)) {
            $classes = array(
                $em->getClassMetadata($this->entities[0]),
            );
            $tool = new SchemaTool($em);
            $tool->createSchema($classes);
        }

        return true;
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}