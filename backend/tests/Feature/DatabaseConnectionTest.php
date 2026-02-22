<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test simple de conexión a la base de datos
 */
class DatabaseConnectionTest extends TestCase
{
    /** @test */
    public function can_connect_to_database()
    {
        $result = DB::select('SELECT 1 as test');
        $this->assertEquals(1, $result[0]->test);
    }

    /** @test */
    public function can_query_users_table()
    {
        $count = DB::table('USERS')->count();
        $this->assertIsInt($count);
    }
}
