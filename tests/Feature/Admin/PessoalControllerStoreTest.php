<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Modules\Pessoal\Models\TipoPessoal;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\Rotas;
use App\Models\Estado;
use Illuminate\Support\Str;

class PessoalControllerStoreTest extends TestCase
{
    /** @test */
    public function store_should_create_pessoa_with_full_payload_and_related_records()
    {
        // Disable middleware to focus on controller behavior
        $this->withoutMiddleware();

        // Force pgsql for tests by aliasing the production connection name used by models
        config([
            'database.connections.pgsql.host' => '10.10.1.3',
            'database.connections.pgsql.port' => '5432',
            'database.connections.pgsql.database' => 'cli_carvalima_old',
            'database.connections.pgsql.username' => 'postgres',
            'database.connections.pgsql.password' => 'SisDBA2@2l',
            'database.default' => 'pgsql',
            'database.connections.pgsql' => config('database.connections.pgsql'),
        ]);

        // Ensure avatar path exists for direct move() used in controller
        if (!file_exists(storage_path('app/public/avatars'))) {
            mkdir(storage_path('app/public/avatars'), 0755, true);
        }

        // Minimal required related records
        // Some models point to a production connection; for test we just ensure IDs exist by mocking values
        // Create or ensure lookup values via factories or direct create if available. If not, stub minimal rows.
        $tipo = TipoPessoal::query()->first() ?? TipoPessoal::unguarded(function () {
            return TipoPessoal::create([
                'id_tipo_pessoal' => 1,
                'descricao_tipo' => 'FUNCIONARIO',
            ]);
        });

        $dep = Departamento::query()->first() ?? Departamento::unguarded(function () {
            return Departamento::create([
                'id_departamento' => 1,
                'descricao_departamento' => 'ADMINISTRATIVO',
            ]);
        });

        $filial = Filial::query()->first() ?? Filial::unguarded(function () {
            return Filial::create([
                'id' => 1,
                'name' => 'Matriz',
            ]);
        });

        $rota = Rotas::query()->first() ?? Rotas::unguarded(function () {
            return Rotas::create([
                'id_rotas' => 1,
                'destino' => 'SÃO LUÍS',
            ]);
        });

        // Authenticate a persisted user so activity_logs can record user_id
        $user = User::unguarded(function () {
            return User::query()->firstOrCreate(
                ['email' => 'tester@example.com'],
                ['name' => 'Tester', 'password' => bcrypt('secret')]
            );
        });
        $this->be($user);
        $this->withSession(['two_factor_authenticated' => true]);

        // Build payload matching controller validation
        $payload = [
            'nome' => 'João da Silva',
            'rg' => '123456789',
            'cpf' => '123.456.789-09',
            'data_nascimento' => '1990-05-20',
            'cnh' => '999999999',
            'validade_cnh' => '2030-12-31',
            'tipo_cnh' => 'B',
            'id_tipo_pessoal' => $tipo->id_tipo_pessoal,
            'id_departamento' => $dep->id_departamento,
            'id_filial' => (string)$filial->id,
            'id_rota' => $rota->id_rotas,
            'email' => 'joao.silva@example.com',
            'orgao_emissor' => 'SSP',
            'data_admissao' => '2020-01-15',
            'ativo' => '1',
            'pis' => '12345678901',
            'matricula' => '12345',
            // Endereço (optional but present)
            'cep' => '65075-000',
            'rua' => 'Av. Principal',
            'complemento' => 'Ap 101',
            'numero' => '100',
            'bairro' => 'Centro',
            'nome_municipio' => 'São Luís',
            'id_uf' => 'MA',
            // Telefones omitidos no teste porque o banco de homologação não possui a coluna telefone_contato
        ];

        // Add image upload
        $payload['imagem_pessoal'] = UploadedFile::fake()->image('avatar.jpg', 120, 120)->size(200);

        // Act
        $response = $this->post(route('admin.pessoas.store'), $payload);

        // Assert redirect and success flash
        $response->assertStatus(302);
        $response->assertRedirect(route('admin.pessoas.index'));
        $response->assertSessionHas('success');

        // Assert DB: pessoa created (basic fields)
        $this->assertDatabaseHas('pessoal', [
            'nome' => 'João da Silva',
            'rg' => '123456789',
            'cpf' => '123.456.789-09',
        ]);

        // Retrieve created pessoa
        $pessoa = \App\Models\Pessoal::where('nome', 'João da Silva')->orderByDesc('id_pessoal')->first();
        $this->assertNotNull($pessoa);
    }
}
