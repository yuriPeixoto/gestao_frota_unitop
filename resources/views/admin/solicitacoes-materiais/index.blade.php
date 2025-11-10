@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Solicitações</h1>
        <a href="{{ route('admin.compras.solicitacoes.create') }}" class="btn btn-primary">Nova Solicitação</a>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Departamento</th>
                    <th>Usuário</th>
                    <th>Filial</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($solicitacoes as $solicitacao)
                    <tr>
                        <td>{{ $solicitacao->id_solicitacao_pecas }}</td>
                        <td>{{ $solicitacao->departamento->descricao_departamento ?? '-' }}</td>
                        <td>{{ $solicitacao->usuarioAbertura->name ?? '-' }}</td>
                        <td>{{ $solicitacao->filial->name ?? '-' }}</td>
                        <td>{{ $solicitacao->situacao }}</td>
                        <td>
                            <a href="{{ route('admin.compras.solicitacoes.edit', $solicitacao) }}"
                                class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('admin.compras.solicitacoes.destroy', $solicitacao) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn btn-sm">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $solicitacoes->links() }}
    </div>
@endsection
