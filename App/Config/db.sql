--
-- Banco de dados: `controle_ponto_db`
--

drop database  controle_ponto_db;
CREATE DATABASE IF NOT EXISTS `controle_ponto_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `controle_ponto_db`;

-- --------------------------------------------------------

create table usuarios
(
    id_usuario    bigint unsigned auto_increment
        primary key,
    nome_completo varchar(100)         null,
    nome_usuario  varchar(50)          null,
    email         varchar(88)          null,
    senha_hash    varchar(255)         null,
    nivel_acesso  int(2)               null comment 'Ex: 1 para Admin, 2 para Usuário Padrão',
    tema_padrao   tinyint(1) default 0 not null,
    constraint idx_email_unico
        unique (email),
    constraint idx_nome_usuario_unico
        unique (nome_usuario)
);

create table albuns
(
    album_id       bigint unsigned auto_increment
        primary key,
    id_usuario     bigint unsigned                      not null,
    nome_album     varchar(255)                         not null,
    tipo_album     bigint unsigned                      null,
    data_definicao datetime default current_timestamp() not null,
    constraint fk_albuns_usuario
        foreign key (id_usuario) references usuarios (id_usuario)
            on delete cascade
);

create table fotos
(
    foto_id         bigint unsigned auto_increment
        primary key,
    album_id        bigint unsigned                        not null,
    id_usuario      bigint unsigned                        not null,
    caminho_arquivo varchar(255)                           not null,
    nome_foto       text                                   not null,
    legenda_foto    text                                   null,
    perfil          tinyint(1) default 0                   null,
    data_upload     datetime   default current_timestamp() not null,
    constraint fotos_ibfk_1
        foreign key (id_usuario) references usuarios (id_usuario)
            on delete cascade,
    constraint fotos_ibfk_2
        foreign key (album_id) references albuns (album_id)
            on delete cascade
);

create index album_id
    on fotos (album_id);

create index id_usuario
    on fotos (id_usuario);

create table historicos_acoes
(
    historico_id    bigint unsigned auto_increment
        primary key,
    id_usuario      bigint unsigned                      not null,
    descricao       text                                 null,
    data_ocorrencia datetime default current_timestamp() not null,
    constraint fk_historico_usuario
        foreign key (id_usuario) references usuarios (id_usuario)
            on delete cascade
);

create table registros_ponto
(
    registro_id   bigint unsigned auto_increment
        primary key,
    id_usuario    bigint unsigned                            not null,
    data_registro date                                       not null,
    status        varchar(50) default 'Verificação pendente' null,
    observacao    text                                       null,
    constraint idx_usuario_data_unica
        unique (id_usuario, data_registro),
    constraint fk_ponto_usuario
        foreign key (id_usuario) references usuarios (id_usuario)
            on delete cascade
);

create table tokens_autenticacao
(
    token_id       bigint unsigned auto_increment
        primary key,
    id_usuario     bigint unsigned                      not null,
    token          varchar(255)                         not null,
    data_criacao   datetime default current_timestamp() not null,
    data_expiracao datetime                             not null,
    constraint idx_id_usuario_unico
        unique (id_usuario),
    constraint tokens_autenticacao_ibfk_1
        foreign key (id_usuario) references usuarios (id_usuario)
            on delete cascade
);

