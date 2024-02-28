<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Services\Ibge\IbgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class StateCitiesImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ibge:import-from-state {slug?}';

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
            $state = $this->argument("slug") ?? $this->choice(
                "Insira a UF (sigla) do estado que deseja importar os municípios:",
                $this->getStates()
            );
            $state = $this->transformState($state);
            $cities = $service->getCitiesByStateSlug($state);
            $this->persistCities($cities);
            return self::SUCCESS;
        }
        private function getStates() {
            $states = config("states");
            return collect($states)
                            ->map(fn($state) => $state["slug"] . " - " . $state["name"])
                            ->toArray();
        }
        private function transformState(string $state) 
        {
            return substr($state,0,2);
        }

        private function persistCities(array $cities)
        {
            collect($cities)
                ->chunk(500)
                ->each(function (Collection $citiesChunk) {
                    $citiesChunk->each(fn ($city) => City::query()->create([
                        "state" => $city["microrregiao"]["mesorregiao"]["UF"]["sigla"],
                        "name" => $city["nome"]
                    ]));
                });
        }
}
