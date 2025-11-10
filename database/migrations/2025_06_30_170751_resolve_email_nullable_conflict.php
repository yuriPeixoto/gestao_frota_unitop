<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Definições das views que serão recriadas
     */
    private $viewDefinitions = [
        'vw_stage_assignments_with_assignees' => "
        CREATE OR REPLACE VIEW public.vw_stage_assignments_with_assignees
        AS SELECT csa.id,
        csa.checklist_id,
        csa.stage,
        c.title AS checklist_title,
        ct.name AS checklist_type_name,
        ct.is_multi_stage,
        csa.assigned_source,
        csa.assigned_source_id,
        CASE
        WHEN csa.assigned_source::text = 'users'::text THEN u.name
        WHEN csa.assigned_source::text = 'pessoal'::text THEN p.nome
        ELSE NULL::character varying
        END AS assignee_name,
        CASE
        WHEN csa.assigned_source::text = 'users'::text THEN u.email
        WHEN csa.assigned_source::text = 'pessoal'::text THEN p.email
        ELSE NULL::character varying
        END AS assignee_email,
        t.telefone_celular AS assignee_phone,
        t.telefone_fixo AS assignee_landline,
        CASE
        WHEN t.telefone_celular IS NOT NULL THEN concat('+55', regexp_replace(t.telefone_celular::text, '[^0-9]'::text, ''::text, 'g'::text))
        ELSE NULL::text
        END AS assignee_whatsapp,
        csa.status,
        csa.started_at,
        csa.completed_at,
        csa.signature_data IS NOT NULL AS has_signature,
        csa.notes,
        csa.assigned_at,
        csa.assigned_by,
        csa.created_at,
        COALESCE(( SELECT json_agg(json_build_object('section_id', cs.id, 'section_name', cs.name, 'section_description', cs.description, 'section_order', cs.order_index) ORDER BY cs.order_index) AS json_agg
        FROM checklist_sections cs
        WHERE cs.checklist_type_id = ct.id AND cs.stage = csa.stage AND cs.is_active = true), '[]'::json) AS stage_sections
        FROM checklist_stage_assignments csa
        JOIN checklists c ON csa.checklist_id = c.id
        JOIN checklist_types ct ON c.checklist_type_id = ct.id
        LEFT JOIN users u ON csa.assigned_source::text = 'users'::text AND csa.assigned_source_id = u.id
        LEFT JOIN pessoal p ON csa.assigned_source::text = 'pessoal'::text AND csa.assigned_source_id = p.id_pessoal
        LEFT JOIN telefone t ON csa.assigned_source::text = 'pessoal'::text AND t.id_pessoal = p.id_pessoal;
        ",
        'v_usuarios_carvalima' => "
        CREATE OR REPLACE VIEW public.v_usuarios_carvalima
        AS SELECT r.id,
        r.name,
        r.email
        FROM dblink('hostaddr=10.10.1.14 port=5432 dbname=base_unitop_permission_carvalima user=postgres password=SisDBA2@2l'::text, 'select id,name,email from system_users'::text) r(id integer, name text, email text);
        "
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Verificar quais views existem
        $existingViews = [];
        foreach (array_keys($this->viewDefinitions) as $viewName) {
            $viewExists = DB::connection('pgsql')->select("
                SELECT COUNT(*) as count
                FROM information_schema.views
                WHERE table_schema = 'public'
                AND table_name = ?
            ", [$viewName]);

            if ($viewExists[0]->count > 0) {
                $existingViews[] = $viewName;
                echo "📋 View {$viewName} encontrada. Fazendo backup...\n";
            }
        }

        // 2. Dropar todas as views que dependem da coluna email
        foreach ($existingViews as $viewName) {
            DB::statement("DROP VIEW IF EXISTS public.{$viewName} CASCADE");
            echo "🗑️ View {$viewName} removida temporariamente.\n";
        }

        // 3. Alterar a coluna email para nullable
        echo "🔧 Alterando coluna email para nullable...\n";
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
        echo "✅ Coluna email alterada com sucesso!\n";

        // 4. Recriar todas as views
        foreach ($existingViews as $viewName) {
            echo "🔄 Recriando view {$viewName}...\n";
            DB::statement($this->viewDefinitions[$viewName]);
            echo "✅ View {$viewName} recriada com sucesso!\n";
        }

        echo "🎉 Migration concluída! Email agora é nullable e " . count($existingViews) . " view(s) foram restauradas.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para reverter, precisamos dropar as views novamente, alterar email para NOT NULL e recriar
        echo "⏪ Revertendo alterações...\n";

        // 1. Identificar views existentes
        $existingViews = [];
        foreach (array_keys($this->viewDefinitions) as $viewName) {
            $viewExists = DB::connection('pgsql')->select("
                SELECT COUNT(*) as count
                FROM information_schema.views
                WHERE table_schema = 'public'
                AND table_name = ?
            ", [$viewName]);

            if ($viewExists[0]->count > 0) {
                $existingViews[] = $viewName;
            }
        }

        // 2. Dropar views para reversão
        foreach ($existingViews as $viewName) {
            DB::statement("DROP VIEW IF EXISTS public.{$viewName} CASCADE");
            echo "🗑️ View {$viewName} removida para reversão.\n";
        }

        // 3. Verificar se há registros com email NULL antes de alterar
        $nullEmails = DB::connection('pgsql')->table('users')->whereNull('email')->count();
        $nullEmails = DB::connection('pgsql')->table('users')->whereNull('email')->count();
        if ($nullEmails > 0) {
            echo "⚠️ ATENÇÃO: Existem {$nullEmails} usuários com email NULL.\n";
            echo "⚠️ Definindo emails temporários para permitir reversão...\n";

            // Definir emails temporários para usuários sem email
            DB::connection('pgsql')->table('users')
            DB::connection('pgsql')->table('users')
                ->whereNull('email')
                ->update(['email' => DB::raw("CONCAT('temp_', id, '@carvalima.temp')")]);
        }

        // 4. Alterar coluna email de volta para NOT NULL
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        // 5. Recriar as views
        foreach ($existingViews as $viewName) {
            DB::statement($this->viewDefinitions[$viewName]);
            echo "✅ View {$viewName} recriada após reversão.\n";
        }

        echo "⏪ Reversão concluída!\n";
    }
};
