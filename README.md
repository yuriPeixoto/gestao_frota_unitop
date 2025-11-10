# 🚚 Gestão Frota

<p align="center">
  <img src="https://img.shields.io/badge/version-1.0.0-blue" alt="Versão">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-%5E8.2-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

Sistema web para gestão completa de frotas, desenvolvido em Laravel. O objetivo é centralizar e padronizar processos como cadastro e controle de veículos, abastecimentos, manutenções, pneus, pessoas/fornecedores, documentos, estoque, sinistros e muito mais.

Todos os módulos do sistema estão configurados em `config/modules.php`.

## Sumário
- Visão geral
- Arquitetura e módulos
- Stack e dependências
- Requisitos
- Instalação e configuração
- Execução do projeto
- Testes
- Estrutura de pastas
- Variáveis de ambiente
- Deploy
- Contribuição
- Licença

## Visão geral
A aplicação oferece um painel administrativo com autenticação, autorização por perfis/permissões (Spatie Permission) e uma interface moderna com TailwindCSS e Vite. Processos assíncronos e logs podem ser acompanhados em ambiente de desenvolvimento via o script de conveniência incluido no Composer.

## Arquitetura e módulos
A lista de módulos e seus status são definidos em `config/modules.php`:

- Produção:
  - Abastecimento — Controle de combustível da frota (rota: `admin.abastecimentomanual.index`)
  - Configurações — Filiais e configurações do sistema (rota: `admin.configuracoes.index`)
  - Imobilizados — Gestão de ativos imobilizados (rota: `admin.imobilizados.index`)
  - Manutenção — Preventivas e corretivas (rota: `admin.manutencao.index`)
  - Pessoal — Pessoas e fornecedores (rota: `admin.pessoal.index`)
  - Pneus — Gestão e controle de pneus (rota: `admin.pneus.index`)
  - Sinistros — Registros de sinistros e ocorrências (rota: `admin.sinistros.index`)
  - Veículos — Gestão da frota (rota: `admin.veiculos.index`)
- Desenvolvimento/Homologação:
  - Compras — Solicitações e processos de compra (rota: `admin.compras.index`) [desenvolvimento]
  - Checklist — Vistorias e inspeções (rota: `admin.checklist.index`)
  - Estoque — Peças e materiais (rota: `admin.estoque.index`)
  - Vencimentários — Documentos e certificados (rota: `admin.vencimentarios.index`)

Os status visuais dos módulos (Produção, Homologação, Desenvolvimento) também são configuráveis no mesmo arquivo.

## Stack e dependências
- Backend: Laravel 11, PHP ^8.2
- Autenticação/Autorização: Laravel Sanctum, spatie/laravel-permission
- Banco de dados: Eloquent ORM e Migrations (MySQL/PostgreSQL/SQLite compatível)
- Geração de PDF e relatórios: barryvdh/laravel-dompdf, laravel-charts, milon/barcode, phpoffice/phpspreadsheet
- Imagens/QR Code: intervention/image, simplesoftwareio/simple-qrcode, bacon-qr-code
- Front-end: Vite, TailwindCSS, Alpine.js, DaisyUI, Axios, jQuery/Select2, SweetAlert2
- Utilitários: rap2hpoutre/laravel-log-viewer, pestphp/pest (testes)

## Requisitos
- PHP 8.2+
- Composer
- Node.js 18+ e NPM
- Banco de dados compatível (MySQL, PostgreSQL ou SQLite)

## Instalação e configuração
1. Clonar o repositório
2. Instalar dependências PHP e JS:
   - `composer install`
   - `npm install`
3. Configurar o arquivo de ambiente:
   - Copie `.env.example` para `.env`
   - Ajuste as variáveis de conexão com banco, cache, mail etc. (ver seção Variáveis de ambiente)
4. Gerar a chave da aplicação: `php artisan key:generate`
5. Executar migrações (e seeders, se aplicável):
   - `php artisan migrate`
   - Opcional: `php artisan db:seed`

## Execução do projeto
- Ambiente de desenvolvimento (com servidor, filas, logs e Vite):
  - `composer run dev`
- Alternativa manual:
  - Backend: `php artisan serve`
  - Filas: `php artisan queue:listen`
  - Vite: `npm run dev`

A aplicação ficará disponível por padrão em http://localhost:8000.

## Testes
Este projeto está configurado com Pest e PHPUnit.
- Rodar todos os testes: `php artisan test` ou `vendor\bin\pest`
- Ambiente de testes: utilize banco dedicado e `.env.testing` se necessário.

## Estrutura de pastas (resumo)
- `app/` — Código de aplicação (Models, Http/Controllers, Policies, etc.)
- `config/` — Arquivos de configuração (inclui `modules.php` com a definição de módulos)
- `database/` — Migrations, seeders e factories
- `resources/` — Views Blade, assets e componentes
- `routes/` — Arquivos de rotas modulares (ex.: `pneus.php`, `estoque.php`)
- `public/` — Raiz pública (index.php, assets compilados)

## Variáveis de ambiente (exemplos)
- APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- QUEUE_CONNECTION, CACHE_DRIVER, SESSION_DRIVER
- SANCTUM_STATEFUL_DOMAINS (se aplicável)
- MAIL_* para envio de e-mails

Consulte `.env.example` e a documentação dos pacotes usados para detalhes.

## Deploy (resumo)
1. Configurar variáveis de ambiente em produção
2. Executar `composer install --no-dev --optimize-autoloader`
3. Executar `php artisan migrate --force`
4. Compilar assets: `npm ci && npm run build`
5. Otimizações do framework: `php artisan config:cache && php artisan route:cache && php artisan view:cache`

## Contribuição
- Abra issues e pull requests descrevendo claramente mudanças propostas.
- Siga convenções de código do Laravel; recomenda-se usar `laravel/pint` para formatação.
- Adicione/atualize testes quando necessário.

## Licença
Este projeto é distribuído sob a licença MIT. Veja o arquivo LICENSE (se aplicável).
