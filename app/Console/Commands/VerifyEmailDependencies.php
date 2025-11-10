<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyEmailDependencies extends Command
{
    protected $signature = 'db:verify-email-dependencies
                           {--check-views : Verificar apenas views que dependem da coluna email}
                           {--check-usage : Verificar uso da view no código}';

    protected $description = 'Verifica dependências da coluna email na tabela users antes de alterá-la';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO DEPENDÊNCIAS DA COLUNA EMAIL');
        $this->info('===========================================');
        $this->newLine();

        // 1. Verificar views que dependem da coluna email
        $this->checkViewDependencies();

        // 2. Verificar constraints/rules
        $this->checkConstraints();

        // 3. Verificar uso no código (se solicitado)
        if ($this->option('check-usage')) {
            $this->checkCodeUsage();
        }

        // 4. Verificar usuários atuais sem email
        $this->checkUsersWithoutEmail();

        $this->newLine();
        $this->info('✅ Verificação concluída!');
    }

    private function checkViewDependencies(): void
    {
        $this->info('📋 1. VERIFICANDO VIEWS QUE DEPENDEM DA COLUNA EMAIL');
        $this->info('---------------------------------------------------');

        $views = DB::connection('pgsql')->select("
            SELECT DISTINCT
                schemaname,
                viewname,
                definition
            FROM pg_views
            WHERE definition ILIKE '%users%'
            AND definition ILIKE '%email%'
            AND schemaname = 'public'
        ");

        if (empty($views)) {
            $this->warn('⚠️ Nenhuma view encontrada que dependa da coluna email da tabela users.');
        } else {
            $this->info('📊 Encontradas '.count($views).' view(s) que referenciam email da tabela users:');

            foreach ($views as $view) {
                $this->line("   • {$view->schemaname}.{$view->viewname}");

                // Verificar se é especificamente a view problemática
                if ($view->viewname === 'vw_stage_assignments_with_assignees') {
                    $this->warn('   ⚠️ Esta é a view que está causando o conflito!');
                }
            }
        }
        $this->newLine();
    }

    private function checkConstraints(): void
    {
        $this->info('🔗 2. VERIFICANDO CONSTRAINTS E RULES');
        $this->info('------------------------------------');

        // Verificar constraints da coluna email
        $constraints = DB::connection('pgsql')->select("
            SELECT
                tc.constraint_name,
                tc.constraint_type,
                kcu.column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
                ON tc.constraint_name = kcu.constraint_name
            WHERE tc.table_name = 'users'
            AND kcu.column_name = 'email'
            AND tc.table_schema = 'public'
        ");

        if (empty($constraints)) {
            $this->info('✅ Nenhuma constraint encontrada na coluna email.');
        } else {
            $this->warn('⚠️ Constraints encontradas na coluna email:');
            foreach ($constraints as $constraint) {
                $this->line("   • {$constraint->constraint_name} ({$constraint->constraint_type})");
            }
        }

        // Verificar rules específicas
        $rules = DB::connection('pgsql')->select("
            SELECT
                rulename,
                definition
            FROM pg_rules
            WHERE tablename = 'users'
            AND definition ILIKE '%email%'
        ");

        if (empty($rules)) {
            $this->info('✅ Nenhuma rule encontrada na coluna email.');
        } else {
            $this->warn('⚠️ Rules encontradas que referenciam email:');
            foreach ($rules as $rule) {
                $this->line("   • {$rule->rulename}");
            }
        }
        $this->newLine();
    }

    private function checkCodeUsage(): void
    {
        $this->info('💻 3. VERIFICANDO USO DA VIEW NO CÓDIGO');
        $this->info('-------------------------------------');

        $this->warn('⚠️ Esta verificação deve ser feita manualmente no código.');
        $this->info('Comandos para verificar no terminal:');
        $this->newLine();

        $this->line('# Buscar referências à view no código PHP:');
        $this->line('grep -r "vw_stage_assignments_with_assignees" app/ resources/ database/');
        $this->newLine();

        $this->line('# Buscar em modelos Eloquent:');
        $this->line('find . -name "*.php" -exec grep -l "vw_stage_assignments_with_assignees" {} \;');
        $this->newLine();

        $this->line('# Buscar em queries diretas:');
        $this->line('grep -r "stage_assignments_with_assignees" app/ --include="*.php"');
        $this->newLine();
    }

    private function checkUsersWithoutEmail(): void
    {
        $this->info('👥 4. VERIFICANDO USUÁRIOS ATUAIS SEM EMAIL');
        $this->info('------------------------------------------');

        $totalUsers = DB::connection('pgsql')->table('users')->count();
        $usersWithEmail = DB::connection('pgsql')->table('users')->whereNotNull('email')->count();
        $usersWithoutEmail = DB::connection('pgsql')->table('users')->whereNull('email')->count();
        $totalUsers = DB::connection('pgsql')->table('users')->count();
        $usersWithEmail = DB::connection('pgsql')->table('users')->whereNotNull('email')->count();
        $usersWithoutEmail = DB::connection('pgsql')->table('users')->whereNull('email')->count();

        $this->info("📊 Total de usuários: {$totalUsers}");
        $this->info("📧 Usuários com email: {$usersWithEmail}");

        if ($usersWithoutEmail > 0) {
            $this->warn("⚠️ Usuários sem email: {$usersWithoutEmail}");

            // Mostrar alguns exemplos
            $examples = DB::connection('pgsql')->table('users')
                ->select('id', 'name', 'matricula')
                ->whereNull('email')
                ->limit(5)
                ->get();

            $this->info('Exemplos de usuários sem email:');
            foreach ($examples as $user) {
                $this->line("   • ID: {$user->id}, Nome: {$user->name}, Matrícula: {$user->matricula}");
            }

            if ($usersWithoutEmail > 5) {
                $this->line('   ... e mais '.($usersWithoutEmail - 5).' usuários');
            }
        } else {
            $this->info('✅ Todos os usuários têm email definido.');
        }
        $this->newLine();
    }

    public function checkMigrationSafety(): bool
    {
        $this->info('🛡️ VERIFICAÇÃO DE SEGURANÇA PARA MIGRATION');
        $this->info('==========================================');

        $issues = [];

        // Verificar views
        $views = DB::connection('pgsql')->select("
            SELECT COUNT(*) as count
            FROM pg_views
            WHERE definition ILIKE '%users%'
            AND definition ILIKE '%email%'
            AND schemaname = 'public'
        ");

        if ($views[0]->count > 0) {
            $issues[] = 'Views dependentes da coluna email encontradas';
        }

        // Verificar constraints
        $constraints = DB::connection('pgsql')->select("
            SELECT COUNT(*) as count
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
                ON tc.constraint_name = kcu.constraint_name
            WHERE tc.table_name = 'users'
            AND kcu.column_name = 'email'
            AND tc.table_schema = 'public'
            AND tc.constraint_type != 'CHECK'
        ");

        if ($constraints[0]->count > 0) {
            $issues[] = 'Constraints na coluna email encontradas';
        }

        if (empty($issues)) {
            $this->info('✅ Migration pode ser executada com segurança!');

            return true;
        } else {
            $this->warn('⚠️ Problemas encontrados:');
            foreach ($issues as $issue) {
                $this->line("   • {$issue}");
            }
            $this->warn('⚠️ Use a migration especial para resolver os conflitos.');

            return false;
        }
    }
}
