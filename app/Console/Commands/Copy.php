<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class Copy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:copy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dados = DB::connection('pgsql_target')->table('users')->get();

        foreach ($dados as $dado) {
            DB::connection('pgsql')->table('users')->updateOrInsert(
                ['email' => $dado->email],
                [
                    'name' => $dado->name,
                    'email' => $dado->email,
                    'password' => $dado->password,
                    'created_at' => $dado->created_at,
                    'updated_at' => $dado->updated_at,
                ]
            );
        }

        $this->info('Dados copiados com sucesso!');
    }
}
