<?php

namespace Tests\Feature\Console;

use Tests\TestCase;

class StateCitiesImportCommandTest extends TestCase
{
  public function test_base_import()
  {
    //prepara
    //agi
    $this->artisan("ibge:import-from-state", ["slug"=>"DF"]);

    //verifica
    $this->assertDatabaseHas("cities",[
      "state"=>"DF",
      "name"=>"Brasilia"
    ]);
  }

}