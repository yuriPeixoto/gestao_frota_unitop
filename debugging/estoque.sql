create table produto
(
    id_produto               bigserial
        primary key,
    id_filial                integer,
    data_inclusao            timestamp not null,
    data_alteracao           timestamp,
    descricao_produto        varchar(500),
    is_original              boolean,
    curva_abc                varchar(2),
    tempo_garantia           varchar(500),
    id_unidade_produto       integer
        constraint fk_produto_unidade
            references unidadeproduto,
    ncm                      integer,
    estoque_minimo           integer,
    estoque_maximo           integer,
    localizacao_produto      varchar(100),
    quantidade_atual_produto double precision,
    imagem_produto           text,
    id_estoque_produto       integer
        constraint fk_produto_estoque
            references estoque,
    id_grupo_servico         integer
        constraint fk_produto_grupo
            references grupo_servico,
    id_produto_subgrupo      integer
        constraint fk_produto_subgrupo
            references subgrupo_servico,
    valor_medio              double precision,
    nome_imagem              varchar(500),
    codigo_produto           text,
    cod_fabricante_          text,
    cod_alternativo_1_       text,
    cod_alternativo_2_       text,
    cod_alternativo_3_       text,
    id_modelo_pneu           integer
        constraint fk_produto_id_modelo_pneu
            references modelopneu,
    is_ativo                 boolean default true,
    descricao_min            text,
    is_imobilizado           boolean,
    id_tipo_imobilizados     integer,
    marca                    text,
    modelo                   text,
    pre_cadastro             boolean,
    id_user_edicao           integer,
    id_user_cadastro         integer,
    deleted_at               timestamp,
    is_fracionado            boolean default false
);

