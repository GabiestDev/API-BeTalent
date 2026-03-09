Teste Prático Back-end BeTalent — Nível 2

Gabriel Neto

Resumo
------
Este repositório contém uma API RESTful desenvolvida em Laravel (PHP 8.4 / Laravel 11) que implementa um sistema de pagamentos multi-gateway com fallback automático entre gateways. O objetivo desta implementação corresponde ao Nível 2 do teste prático: o valor da compra é calculado a partir do produto e da quantidade no back-end e os gateways exigem autenticação conforme os mocks fornecidos.

Sumário
-------
- Objetivo
- Requisitos
- Estrutura do Projeto
- Instalação e Execução
- Execução dos Mocks de Gateways
- Rotas Principais
- Testes
- O que foi Implementado
- Pendências / Observações

1. Objetivo
---------------
Demonstrar uma API de pagamentos capaz de tentar a cobrança em múltiplos gateways ordenados por prioridade. Caso um gateway retorne erro, o sistema faz fallback automático para o próximo gateway ativo. A solução segue o padrão Adapter para facilitar a adição de novos gateways.

2. Requisitos
----------------
- Docker e Docker Compose
- PHP 8.4
- Composer
- Docker containers: app (Laravel), MySQL
- Mocks dos gateways (ports 3001 e 3002)

3. Estrutura do Projeto (resumida)
----------------------------------
- `app/Services/Gateways/` — adaptadores dos gateways e `PaymentGatewayInterface`
- `app/Services/CheckoutService.php` — lógica de checkout com fallback
- `app/Http/Controllers/` — controladores (Auth, Checkout, Product, User, Gateway, Transaction, Client)
- `app/Http/Requests/CheckoutRequest.php` — validação do payload de compra
- `app/Models/` — modelos Eloquent (`User`, `Gateway`, `Client`, `Product`, `Transaction`)
- `routes/api.php` — rotas da API
- `tests/Feature/` — testes de integração (Pest)

4. Instalação e Execução
-------------------------
Pré-requisitos: Docker e Docker Compose instalados.

1) Subir os containers (no diretório do projeto):

```bash
docker compose up -d
```

2) Executar migrations e seeders (dentro do container app):

```bash
docker exec -it betalent-app php artisan migrate --seed
```

Antes de executar as migrations, instale o `laravel/sanctum` e publique seus assets (dentro do container ou localmente):

```bash
docker exec -it betalent-app composer require laravel/sanctum
docker exec -it betalent-app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker exec -it betalent-app php artisan migrate
```

3) Rodar testes (dentro do container app):

```bash
docker exec -it betalent-app ./vendor/bin/pest --colors
```

5. Execução dos Mocks de Gateways
----------------------------------
Os mocks podem ser executados via Docker (se não estiverem configurados no `docker-compose`):

```bash
docker run -p 3001:3001 -p 3002:3002 gateways-mock
```

Para executar sem autenticação (modo de teste):

```bash
docker run -p 3001:3001 -p 3002:3002 -e REMOVE_AUTH='true' gateways-mock
```

Gateway 1 (porta 3001): autenticação via rota `POST /login` retornando token Bearer; rotas de transações em `/transactions`.

Gateway 2 (porta 3002): autenticação por headers `Gateway-Auth-Token` e `Gateway-Auth-Secret`; rotas em `/transacoes`.

6. Rotas Principais
--------------------
Públicas:
- `POST /api/login` — autenticação (geração de token via Sanctum)
- `POST /api/checkout` — realizar compra pública

Privadas (middleware `auth:sanctum`):
- `GET /api/products`, `POST /api/products`, `GET /api/products/{id}`, `PUT/PATCH /api/products/{id}`, `DELETE /api/products/{id}`
- `GET /api/users`, `POST /api/users`, `GET /api/users/{id}`, `PUT/PATCH /api/users/{id}`, `DELETE /api/users/{id}`
- `GET /api/clients`, `GET /api/clients/{id}`
- `GET /api/transactions`, `GET /api/transactions/{id}`, `POST /api/transactions/{id}/refund`
- `GET /api/gateways`, `PATCH /api/gateways/{id}`

7. Testes
---------
O projeto utiliza Pest para testes. Os testes de integração implementados cobrem os seguintes cenários:
- Cobrança bem-sucedida no Gateway One (sem fallback)
- Falha no Gateway One e sucesso no Gateway Two (validação do fallback)
- Reembolso via Gateway One
- Reembolso via Gateway Two

Para executar os testes:

```bash
docker exec -it betalent-app ./vendor/bin/pest --colors
```

8. O que foi Implementado (Nível 2)
----------------------------------
- Cálculo de valor com base no `product.amount` e `quantity` no back-end.
- Padrão Adapter para gateways (`PaymentGatewayInterface`, `GatewayOne`, `GatewayTwo`).
- Autenticação/headers conforme especificado pelos mocks dos gateways.
- Lógica de fallback no `CheckoutService` tentando gateways ativos por ordem de `priority`.
- Persistência de `transactions` com `status` (`paid`, `failed`, `refunded`), `gateway_id` e `external_id`.
- Endpoints de CRUD para `products` e `users`, endpoints de `clients`, listagem/detalhe de transações e reprovações (refund).
- Testes Pest cobrindo fallback e reembolso.

9. Pendências / Observações
---------------------------
- Configuração completa do `sanctum` para ambiente de testes foi contornada nos testes via `withoutMiddleware()`; em produção recomenda-se configuração e proteção adequadas com tokens reais.

- O model `App\Models\User` já possui o trait `HasApiTokens` para uso com Sanctum; após instalar o pacote e executar `vendor:publish` / `migrate` os endpoints protegidos com `auth:sanctum` estarão operacionais.
- Documentação adicional (exemplos de payloads) pode ser adicionada ao Postman Collection já fornecida com o teste.

Referências
----------
- Multigateways mock: imagem Docker `gateways-mock` (instruções de uso acima)

Licença
-------
MIT
