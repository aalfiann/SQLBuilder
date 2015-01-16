<?php
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;

class MySQLCreateTableQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';

    // public $schema = array( 'tests/schema/member_mysql.sql' );

    public function createDriver() {
        return new MySQLDriver;
    }

    public function setUp()
    {
        parent::setUp();

        // Clean up
        foreach(array('groups','authors') as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertQuery($dropQuery);
        }
    }

    public function tearDown()
    {
        foreach(array('groups','authors', 'points') as $table) {
            $dropQuery = new DropTableQuery($table);
            $dropQuery->IfExists();
            $this->assertQuery($dropQuery);
        }
    }

    public function testCreateTableWithDecimalsAndLength() 
    {
        $q = new CreateTableQuery('points');
        $q->column('x')->float(10,2);
        $q->column('y')->float(10,2);
        $q->column('z')->float(10,2);
        $q->column('strength')->double(10,2);
        $this->assertSqlStatements($q, [ 
            [new MySQLDriver, 'CREATE TABLE `points`(
`x` float(10,2),
`y` float(10,2),
`z` float(10,2),
`strength` double(10,2)
)'],
        ]);
        $this->assertQuery($q);

        $dropQuery = new DropTableQuery('points');
        $dropQuery->IfExists();
        $this->assertQuery($dropQuery);
    }


    public function testCreateTableWithPrimaryKey()
    {
        $q = new CreateTableQuery('groups');
        $q->column('id')->integer();
        $q->engine('InnoDB');
        $q->primaryKey('id');
        $this->assertQuery($q);
        $this->assertSql('CREATE TABLE `groups`(
`id` integer,
PRIMARY KEY (`id`)
) ENGINE=InnoDB',$q);
    }

    public function testCreateTableQuery()
    {
        $q = new CreateTableQuery('groups');
        $q->column('id')->integer()
            ->primary()
            ->autoIncrement();
        $q->engine('InnoDB');
        $this->assertQuery($q);

        $q = new CreateTableQuery('users');
        $q->table('authors');

        $q->column('id')->integer()
            ->primary()
            ->autoIncrement();

        $q->column('first_name')->varchar(32);
        $q->column('last_name')->varchar(16);
        $q->column('age')->tinyint(3)->unsigned()->null();
        $q->column('phone')->varchar(24)->null();
        $q->column('email')->varchar(128)->notNull();
        $q->column('confirmed')->boolean()->default(false);
        $q->column('types')->set('student', 'teacher');
        $q->column('remark')->text();

        $q->column('group_id')->integer();

        // create table t1 (
        //      id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
        //      product_id integer unsigned,  constraint `fk_product_id` 
        //      FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) );
        $q->constraint('fk_group_id')
            ->foreignKey('group_id')
                ->references('groups', 'id')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE')
                ;

        $q->uniqueKey('email');

        $q->engine('InnoDB');

        ok($q);

        $dropQuery = new DropTableQuery('authors');
        $dropQuery->IfExists();
        $this->assertSql('DROP TABLE IF EXISTS `authors`', $dropQuery);
        $this->assertQuery($dropQuery);
        $this->assertSql('CREATE TABLE `authors`(
`id` integer PRIMARY KEY AUTO_INCREMENT,
`first_name` varchar(32),
`last_name` varchar(16),
`age` tinyint(3) UNSIGNED NULL,
`phone` varchar(24) NULL,
`email` varchar(128) NOT NULL,
`confirmed` boolean DEFAULT FALSE,
`types` set(\'student\', \'teacher\'),
`remark` text,
`group_id` integer,
CONSTRAINT `fk_group_id` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
UNIQUE KEY (`email`)
) ENGINE=InnoDB', $q);
        $this->assertQuery($q);
        $this->assertQuery($dropQuery); // drop again to test the if exists.

    }
}

