<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>sqsq</title>
    <style>
        body {
            padding: 0px;
            margin: 0px;
            font-family: Arial, sans-serif;
        }
        h1 { color: #333; }
        p { font-size: 14px; }

        li{
            display: flex;
            justify-content: space-between;
            margin: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
   <ul>
    @foreach ($result as $produto)
        <li>
            <span>
                {{$produto['descricao']}}
            </span>
            <div>
                {{-- @if($tipo == 'C39') --}}
                    <img width="150" heigth="150"  src="data:image/png;base64,{{ $produto['codigo'] }}" alt="Código de Barras">
                    
                {{-- @else --}}
                    {{-- <img width="150" heigth="150"  src="{{ $produto['codigo'] }}" alt="Código de Barras"> --}}
                {{-- @endif --}}
            </div>
        </li>

    
    @endforeach
   </ul>
</body>
</html>
