<?php
namespace Czim\CmsCore\Console;

use CreateMoreTestRecordsTable;
use CreateTestRecordsTable;
use Czim\CmsCore\Test\SimpleDbTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateRollbackCommandTest extends SimpleDbTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->setHelperCmsMigrationPath();
    }

    /**
     * @test
     */
    function it_rolls_back_a_migration()
    {
        // Set up
        $this->setHelperCmsMigrationPath();
        $this->createMigrationTable();

        (new CreateTestRecordsTable())->up();
        (new CreateMoreTestRecordsTable())->up();

        DB::table('cms_migrations')->insert([
            'migration' => '2017_01_01_100000_create_test_records_table',
            'batch'     => 1,
        ]);
        DB::table('cms_migrations')->insert([
            'migration' => '2017_01_01_200000_create_more_test_records_table',
            'batch'     => 2,
        ]);

        static::assertTrue(Schema::connection('testbench')->hasTable('cms_test_records'), 'Failed to fake migration');
        static::assertTrue(Schema::connection('testbench')->hasTable('cms_more_test_records'), 'Failed to fake migration');

        // Test
        static::assertEquals(0, $this->artisan('cms:migrate:rollback'));

        static::assertTrue(Schema::connection('testbench')->hasTable('cms_test_records'));
        static::assertFalse(Schema::connection('testbench')->hasTable('cms_more_test_records'));
    }

}
