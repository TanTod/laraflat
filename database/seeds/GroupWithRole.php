<?php

use Illuminate\Database\Seeder;

class GroupWithRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       DB::table('group_role')->insert([
              'group_id' => 1,
              'role_id'=> 1,
       ]);
       DB::table('group_role')->insert([
            'group_id' => 1,
            'role_id'=> 2,
       ]);
       DB::table('group_role')->insert([
            'group_id' => 1,
            'role_id'=> 3,
       ]);
       DB::table('group_role')->insert([
            'group_id' => 1,
            'role_id'=> 4,
       ]);
    }
}
