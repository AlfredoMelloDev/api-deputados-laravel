# Painel de Deputados

Aplicação desenvolvida em Laravel para consumir a API de Dados Abertos da Câmara dos Deputados, armazenar deputados e despesas parlamentares em MySQL e disponibilizar esses dados para consulta.

O projeto utiliza Jobs e filas do Laravel para processar a sincronização das despesas em segundo plano: depois que os deputados são importados, uma Job independente é criada para cada parlamentar.

## Tecnologias

- PHP 8.5
- Laravel 13
- MySQL 8.4
- Laravel Queues com driver de banco de dados
- Docker e Docker Compose
- PHPUnit

## Arquitetura da sincronização

```text
API de Dados Abertos da Câmara
              |
              v
   Comando camara:sync-deputies
              |
              +----> salva ou atualiza os deputados no MySQL
              |
              +----> cria uma Job para cada deputado
                              |
                              v
                  Worker da fila expenses
                              |
                              v
                  salva ou atualiza despesas
```

A importação é idempotente. Executar a sincronização novamente atualiza os registros existentes sem duplicar deputados ou despesas.

## Funcionalidades implementadas

- Cliente HTTP para a API da Câmara;
- paginação automática de deputados e despesas;
- limite de 100 registros por requisição;
- repetição automática em falhas temporárias;
- validação de respostas inesperadas da API;
- persistência de deputados e despesas no MySQL;
- uma Job assíncrona por deputado;
- prevenção de Jobs e despesas duplicadas;
- filtros preparados no banco por partido, UF, ano e mês;
- painel com indicadores gerais e ranking de categorias;
- listagem pesquisável e página individual de cada deputado;
- filtros de despesas por ano, mês, tipo, fornecedor e intervalo de datas;
- histórico visual das sincronizações, com progresso, totais e falhas;
- alertas visuais com o motivo de falhas na API ou nas tarefas da fila;
- testes automatizados para models, cliente HTTP, comando e Job.

## Requisitos

Para executar o projeto é necessário ter Git e Docker Desktop com o Docker Engine ativo.

Não é necessário instalar PHP, Composer ou MySQL diretamente no computador.

## Instalação

Clone o repositório e entre na pasta:

```bash
git clone https://github.com/AlfredoMelloDev/api-deputados-laravel.git
cd api-deputados-laravel
```

Crie o arquivo de ambiente.

No Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

No Linux ou macOS:

```bash
cp .env.example .env
```

Instale as dependências PHP usando o Composer pelo Docker:

```powershell
docker run --rm --volume "${PWD}:/app" --workdir /app composer:2 composer install
```

Construa e inicie os serviços:

```bash
docker compose up -d --build
```

Gere a chave da aplicação e execute as migrations:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

A aplicação estará disponível em [http://localhost:8000](http://localhost:8000).

## Serviços Docker

| Serviço | Responsabilidade | Porta local |
| --- | --- | --- |
| `app` | Aplicação Laravel | `8000` |
| `mysql` | Banco de dados MySQL | `3306` |
| `queue` | Processamento das Jobs | — |
| `scheduler` | Agendamento automático das sincronizações | — |

Para verificar o estado dos serviços:

```bash
docker compose ps
```

Para interromper os serviços preservando o banco:

```bash
docker compose down
```

## Sincronização

Para importar os deputados e enfileirar as despesas do ano atual:

```bash
docker compose exec app php artisan camara:sync-deputies
```

Para escolher um ano específico a partir de 2008:

```bash
docker compose exec app php artisan camara:sync-deputies --year=2026
```

O contêiner `queue` processa as Jobs automaticamente em segundo plano. O andamento pode ser acompanhado com:

```bash
docker compose logs -f queue
```

O serviço `scheduler` inicia automaticamente a sincronização do ano atual todos os dias, às 03:00 no horário de Brasília. Se uma carga do mesmo ano ainda estiver em andamento, outra não será iniciada.

## Testes

Execute toda a suíte:

```bash
docker compose exec app php artisan test
```

Formate o código conforme o padrão do Laravel:

```bash
docker compose exec app vendor/bin/pint
```

## API utilizada

Documentação oficial: [Dados Abertos da Câmara dos Deputados](https://dadosabertos.camara.leg.br/swagger/api.html)

Endpoints principais:

```text
GET /api/v2/deputados
GET /api/v2/deputados/{id}/despesas
```

## Estrutura relevante

```text
app/
|-- Console/Commands/SyncDeputiesCommand.php
|-- Jobs/SyncDeputyExpenses.php
|-- Models/Deputy.php
|-- Models/Expense.php
`-- Services/Camara/CamaraApiClient.php
```

## Próximas etapas

- ampliar os gráficos e rankings;
- ampliar a cobertura de testes.

## Autor

Desenvolvido por [Alfredo Mello](https://github.com/AlfredoMelloDev).
