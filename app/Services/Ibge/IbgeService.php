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