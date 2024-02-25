<?php

namespace App\Console\Commands;

use App\Services\Ibge\IbgeService;
use Illuminate\Console\Command;

class StateCitiesImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ibge:import-from-state';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa todos os municípios de um Estado';

    /**
     * Execute the console command.
     */
        public function handle(IbgeService $service)
        {
            $state = $this->choice(
                "Insira a UF (sigla) do estado que deseja importar os municípios:",
                $this->getStates()
            );
            $service->getCitiesByStateSlug($state);
        }
        private function getStates() {
            $states = [
                ['slug' => 'AC', 'name' => 'Acre'],
                ['slug' => 'AL', 'name' => 'Alagoas'],
                ['slug' => 'AP', 'name' => 'Amapá'],
                ['slug' => 'AM', 'name' => 'Amazonas'],
                ['slug' => 'BA', 'name' => 'Bahia'],
                ['slug' => 'CE', 'name' => 'Ceará'],
                ['slug' => 'DF', 'name' => 'Distrito Federal'],
                ['slug' => 'ES', 'name' => 'Espirito Santo'],
                ['slug' => 'GO', 'name' => 'Goiás'],
                ['slug' => 'MA', 'name' => 'Maranhão'],
                ['slug' => 'MS', 'name' => 'Mato Grosso do Sul'],
                ['slug' => 'MT', 'name' => 'Mato Grosso'],
                ['slug' => 'MG', 'name' => 'Minas Gerais'],
                ['slug' => 'PA', 'name' => 'Pará'],
                ['slug' => 'PB', 'name' => 'Paraíba'],
                ['slug' => 'PR', 'name' => 'Paraná'],
                ['slug' => 'PE', 'name' => 'Pernambuco'],
                ['slug' => 'PI', 'name' => 'Piauí'],
                ['slug' => 'RJ', 'name' => 'Rio de Janeiro'],
                ['slug' => 'RN', 'name' => 'Rio Grande do Norte'],
                ['slug' => 'RS', 'name' => 'Rio Grande do Sul'],
                ['slug' => 'RO', 'name' => 'Rondônia'],
                ['slug' => 'RR', 'name' => 'Roraima'],
                ['slug' => 'SC', 'name' => 'Santa Catarina'],
                ['slug' => 'SP', 'name' => 'São Paulo'],
                ['slug' => 'SE', 'name' => 'Sergipe'],
                ['slug' => 'TO', 'name' => 'Tocantins']
            ];
            return collect($states)
                            ->map(fn($state) => $state["slug"] . " - " . $state["name"])
                            ->toArray();
        }
}
