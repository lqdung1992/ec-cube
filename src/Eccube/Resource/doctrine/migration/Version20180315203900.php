<?php
/**
 * Created by PhpStorm.
 * User: lqdung1992@gmail.com
 * Date: 15/03/2018
 * Time: 08:39 PM
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Entity\Master\SearchType;

/**
 * Class Version20180315203900
 * @package DoctrineMigrations
 */
class Version20180315203900 extends AbstractMigration
{
    /**
     * @var array table name
     */
    protected $table = array('mtb_search_type');

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Eccube\Entity\Master\SearchType',
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

        $searchType = new SearchType();
        $searchType->setId(1)
            ->setRank(0)
            ->setName('品目から探す');
        $em->persist($searchType);

        $searchType = new SearchType();
        $searchType->setId(2)
            ->setRank(1)
            ->setName('生産者から探す');
        $em->persist($searchType);

        $searchType = new SearchType();
        $searchType->setId(3)
            ->setRank(2)
            ->setName('生産方法から探す');
        $em->persist($searchType);

        $searchType = new SearchType();
        $searchType->setId(4)
            ->setRank(3)
            ->setName('検索履歴');
        $em->persist($searchType);
        $searchType = new SearchType();
        $searchType->setId(5)
            ->setRank(4)
            ->setName('トマト');
        $em->persist($searchType);

        $em->flush();
    }

    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}