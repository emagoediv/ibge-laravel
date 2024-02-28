# Desafio: comando que importa as cidades para o banco com base na UF

# Tasks:

- [x]  Criar comando no artisan
- [x]  Criar serviço de consulta dentro do laravel
- [x]  Consultar a api pelo serviço a partir do comando
- [x]  Criar tabela do banco
- [x]  Ao rodar comando, salvar no banco os dados que o serviço retornou a partir do comando

# Configuração do projeto:

### Laravel: 10.x
### PHP: 8.3
### Api utilizada:

[API de localidades](https://servicodados.ibge.gov.br/api/docs/localidades#api-Municipios-estadosUFMunicipiosGet)

 

### Criando um comando no laravel:

```php
php artisan make:command StateCitiesImportCommand
```

### Alterando configuração padrão da classe de comandos do artisan

Ao executar o comando acima o laravel irá criar um novo arquivo em app/Console/Commands com a classe do seu comando, iremos alterar o modo de chama-la:

 

```php
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
    public function handle()
    {
        $this->info("Bora importar as cities");
    }
}
```

Agora, executando o comando:

```php
php artisan
```

Uma nova linha foi adicionada ao conjunto de comandos acessíveis:

`ibge
ibge:import-from-state  Importa todos os municípios de um Estado`

## Receber UF do comando

Para pergutar algo ao usuário e já exibir as opções podemos usar a função choice do próprio Command do Laravel

```php
 public function handle()
    {
        $state = $this->choice(
            "Insira a UF (sigla) do estado que deseja importar os municípios:",
            $this->getStates()
        );
        dd($state);
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
```

Agora já conseguimos pegar o valor selecionado no prompt, e exibimos ele

Se por algum acaso o usuário selecionar um valor inválido o choice pergunta novamente

## Abstração de serviços

Iremos usar para a conexão com a api o guzzle que vem com o Laravel:

[https://docs.guzzlephp.org/en/stable/](https://docs.guzzlephp.org/en/stable/)

Criaremos uma pasta dentro de app/ chamada Services/ para guardar esse servico, e lá criaremos nosso IbgeService

```php
<?php

namespace App\Services\Ibge;

use GuzzleHttp\Client;

class IbgeService {
  private Client $client;
  public function __construct()
  {
    $this->client = new Client([
      "base_url" => "https://servicodados.ibge.gov.br/api/v1",
      "timeout" => 5.0
    ]);
  }
  public function getCitiesByStateSlug(string $slug) {
    dd("chegou em aqui em $slug");
  }
}
```

E importaremos essa classe para o nosso handle() do comando 

```php
 public function handle(IbgeService $service)
        {
            $state = $this->choice(
                "Insira a UF (sigla) do estado que deseja importar os municípios:",
                $this->getStates()
            );
            $service->getCitiesByStateSlug($state);
        }
```

Agora ao executar o comando e selecionarmos 25(SP), recebemos

```php
"chegou em aqui em SP - São Paulo" // app\Services\Ibge\IbgeService.php:17
```

## Requisição

Agora para fazer uma requisição real iremos implementar:

```php
public function getCitiesByStateSlug(string $slug) 
  {
    $uri = sprintf("localidades/estados/%s/municipios",$slug);
    $response = $this->client->get($uri);
    return json_decode($response->getBody());
  }
```

Agora já pegamos as cidades, mas ainda devemos armazenar o resultado em um banco de dados

Configurando seu .env e criando um banco, você já poderá rodar o comando:

`php artisan make:model City --migration`

Na migration gerada implementamos a configuração da tabela, sua estrutura:

```php
Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string("state",3)->index();
            $table->string("name");
            $table->timestamps();
        });
```

E na model, para liberar as colunas para realizar o CRUD, na model colocamos:

```php
 protected $table = "cities";

    protected $fillable = [
        "state",
        "name"
    ];
```

# Testes

## Teste de Integração

para verificar o resultado esperado da realização do comando, criaremos um arquivo para realizar o teste em tests/Feature/Console/StatesCitiesImportCommandTests.php

Faremos uma alteração para receber algumentos no comando na Classe StatesCitiesImportCommand

```php
$state = $this->argument("slug") ?? $this->choice(
                "Insira a UF (sigla) do estado que deseja importar os municípios:",
                $this->getStates()
            );
```

Agora se rodarmos `php artisan ibge:import-from-state TO` esse comando substitui a necessidade de responder a uma seleção do estado desejado onde TO pode ser substituído por qualquer UF

Nosso teste deve rodar o comando artisan e verificar no banco de dados se os dados foram cadastrados:

```php
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
```

Agora podemos criar uma função para persistir os dados no banco

```php
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
```

E enviaremos $cities

```php
$cities = $service->getCitiesByStateSlug($state);
$this->persistCities($cities);
return self::SUCCESS;
```

Rodando `php artisan test` você pode verificar se ocorreu tudo como o esperado

Para a organização do código criaremos uma arquivo em config/ que somente retorna os arrays com as slugs e então a função que retorna os estados fica:

```php
 private function getStates() {
            $states = config("states");
            return collect($states)
                            ->map(fn($state) => $state["slug"] . " - " . $state["name"])
                            ->toArray();
        }
```