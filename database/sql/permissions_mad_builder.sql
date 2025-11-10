CREATE TABLE public.system_group (
     id int4 NOT NULL,
     "name" text NOT NULL,
     "uuid" varchar(36) NULL,
     CONSTRAINT system_group_pkey PRIMARY KEY (id)
);
id|name                  |uuid                                |
--+----------------------+------------------------------------+
 1|Admin                 |                                    |
 2|Standard              |                                    |
25|Pessoal Noite         |                                    |
28|Saída de Veículos     |                                    |
23|Grupo Porteiros       |                                    |
26|Testes                |                                    |
27|Testes Unitop         |4589ee2e-db24-4867-b1f8-e75afeff455b|
29|Estoque TI            |                                    |
33|Inventário Pneus      |                                    |
31|Compras Aprov/Valid   |                                    |
12|Veículo               |3d0929c1-4ee8-41f6-9653-648270516f44|
 9|Pessoas & Fornecedores|ae8e0970-d82a-4c60-92c8-743df83853db|
11|Sinistro              |9837ca50-f78d-41b6-959d-231194ab55a4|
21|Financeiro            |1040d6b7-d3b0-44c5-832b-87eae85da2bf|
16|Relatórios Gerenciais |97ff05fe-3663-4a67-8c26-6796b2d0b42b|
18|Pedágio               |75b424de-d707-45eb-ac23-e84bcd676585|
22|Portaria              |de6bfd96-6ba3-4e65-92b7-df9e7ef1f770|
17|Motoristas            |cdee6da6-5540-479d-9dca-0954219def7b|
14|Solicitações          |5c064834-f956-4264-bf01-f409472f1039|
15|Gestão de Viagem      |981aeafc-8887-4a84-aa19-c5ad163b6423|
 3|Abastecimento         |8ceae814-e181-4bf7-ab06-1684b96e6214|
 4|Compras               |bf91997a-1c11-4c11-aa57-4088b7967397|
13|Configurações         |450c482e-06a0-4663-aea1-b175e6352852|
 5|Estoque               |2a6bf80a-f036-4f63-94a0-f19a33896388|
 8|Manutenção            |5dc1fc12-bf89-44a1-9d46-d12db557aa2c|
10|Pneus                 |3ebac0d1-5f70-4709-b3ce-77349422e9e2|
 6|Gestão de Jornada     |73096780-ddd8-4885-bfe3-cf2f51c1f952|
19|Imobilizados          |888c16a4-ae54-482d-a50c-69c9e0aef9e9|
32|Prêmio Carvalima      |061512fe-10d3-4ebf-9b62-410fdb9c2bb7|
30|Prêmio Superação      |93cab2a8-21cc-47ce-828d-4ca098e68438|
20|Vencimentário         |7b7bcda3-e1f7-4386-9807-a4741c873c66|
 7|Gestão de Telemetria  |13091cfc-b496-4f7a-be57-27a862159489|

CREATE TABLE public.system_group_program (
     id int4 NOT NULL,
     system_group_id int4 NOT NULL,
     system_program_id int4 NOT NULL,
     actions text NULL,
     CONSTRAINT system_group_program_pkey PRIMARY KEY (id),
     CONSTRAINT system_group_program_system_group_id_fkey FOREIGN KEY (system_group_id) REFERENCES public.system_group(id),
     CONSTRAINT system_group_program_system_program_id_fkey FOREIGN KEY (system_program_id) REFERENCES public.system_program(id)
);
CREATE INDEX sys_group_program_group_idx ON public.system_group_program USING btree (system_group_id);
CREATE INDEX sys_group_program_program_idx ON public.system_group_program USING btree (system_program_id);
id  |system_group_id|system_program_id|actions|
----+---------------+-----------------+-------+
   1|              1|                1|       |
   2|              1|                2|       |
   3|              1|                3|       |
   4|              1|                4|       |
   5|              1|                5|       |
   6|              1|                6|       |
   7|              1|                8|       |
   8|              1|                9|       |
   9|              1|               11|       |
  10|              1|               14|       |
  11|              1|               15|       |
  13|              2|               12|       |
  14|              2|               13|       |
  15|              2|               16|       |
  16|              2|               17|       |
  17|              2|               18|       |
  18|              2|               19|       |
  19|              2|               20|       |
  20|              1|               21|       |
  21|              2|               22|       |
  22|              2|               23|       |
  23|              2|               24|       |
  24|              2|               25|       |
  25|              1|               26|       |
  26|              1|               27|       |
  27|              1|               28|       |
  28|              1|               29|       |
  29|              2|               30|       |
  30|              1|               31|       |
  31|              1|               32|       |
  32|              1|               33|       |
  33|              1|               34|       |
  34|              1|               35|       |
  36|              1|               36|       |
  37|              1|               37|       |
  38|              1|               38|       |
  39|              1|               39|       |
  40|              1|               40|       |
  41|              1|               41|       |
  42|              1|               42|       |
  43|              3|               43|       |
  44|              3|               44|       |
  45|              3|               45|       |
  46|              3|               46|       |
  47|              3|               47|       |
  48|              3|               48|       |
  49|              3|               49|       |
  50|              3|               50|       |
  51|              3|               51|       |
  52|              3|               52|       |
  53|              3|               53|       |
  54|              3|               54|       |
  55|              3|               55|       |
  56|              3|               56|       |
  57|              3|               57|       |
  58|              3|               58|       |
  59|              3|               59|       |
  60|              3|               60|       |
  61|              3|               61|       |
  62|              3|               62|       |
1977|              4|               63|       |
1978|              4|               64|       |
1979|              4|               65|       |
1980|              4|               66|       |
1981|              4|               67|       |
1982|              4|               68|       |
1983|              4|               69|       |
1984|              4|               70|       |
1985|              4|               71|       |
1986|              4|               72|       |
1987|              4|               73|       |
1988|              4|               74|       |
1989|              4|               75|       |
1990|              4|               76|       |
1991|              4|              102|       |
1992|              4|              239|       |
1993|              4|              240|       |
1994|              4|              241|       |
1676|              7|             1104|       |
1677|             30|             1105|       |
1678|             30|             1106|       |
 110|              7|              110|       |
 111|              7|              111|       |
 112|              7|              112|       |
2368|              8|             1125|       |
2369|             32|             1195|       |
2370|              5|             1196|       |
2371|              4|             1197|       |
2372|              4|             1198|       |
2373|              8|             1199|       |
2374|              8|             1200|       |
2375|              8|             1201|       |
2376|             10|             1202|       |
2377|             10|             1203|       |
2378|             10|             1204|       |
2379|             10|             1205|       |
2380|             12|             1206|       |
2381|              5|             1207|       |
2382|             16|             1208|       |
2383|              5|             1209|       |
2384|              5|             1210|       |
2385|             15|             1211|       |
2386|             15|             1212|       |
2387|             15|             1213|       |
2388|             10|             1214|       |
2389|             10|             1215|       |
2390|             10|             1216|       |
2391|             15|             1217|       |
2392|             15|             1218|       |
2393|             15|             1219|       |
2394|             15|             1220|       |
2395|              4|             1221|[]     |
2396|              4|             1222|       |
2397|              4|             1223|       |
2398|             10|             1224|       |
2399|              9|             1225|       |
2400|              4|             1226|       |
2401|              3|             1227|       |
2402|             16|             1228|       |
2403|              5|             1229|[]     |
2404|             10|             1230|[]     |
2410|              4|             1235|       |
2411|              5|             1236|       |
2412|             32|             1237|       |
 153|             10|              153|       |
 154|             10|              154|       |
 155|             10|              155|       |
 156|             10|              156|       |
 157|             10|              157|       |
 158|             10|              158|       |
 159|             10|              159|       |
 160|             10|              160|       |
 161|             10|              161|       |
 162|             10|              162|       |
 163|             10|              163|       |
 164|             10|              164|       |
 165|             10|              165|       |
 166|             10|              166|       |
 167|             10|              167|       |
 168|             10|              168|       |
 169|             10|              169|       |
 170|             10|              170|       |
 171|             10|              171|       |
 172|             11|              172|       |
 173|             11|              173|       |
 174|             11|              174|       |
 175|             11|              175|       |
 176|             11|              176|       |
 177|             11|              177|       |
 178|             11|              178|       |
 179|             11|              179|       |
 180|             12|              180|       |
 181|             12|              181|       |
 182|             12|              182|       |
 183|             12|              183|       |
 184|             12|              184|       |
 185|             12|              185|       |
 186|             12|              186|       |
 187|             12|              187|       |
 188|             12|              188|       |
 189|             12|              189|       |
 190|             12|              190|       |
 191|             12|              191|       |
 192|             12|              192|       |
 193|             12|              193|       |
 194|             12|              194|       |
 195|             12|              195|       |
 196|             12|              196|       |
 197|             12|              197|       |
 198|             12|              198|       |
 199|             12|              199|       |
 200|             12|              200|       |
 201|             12|              201|       |
 202|             12|              202|       |
 203|             12|              203|       |
 204|             12|              204|       |
 205|             12|              205|       |
 206|             12|              206|       |
 207|             12|              207|       |
 208|             12|              208|       |
 209|             12|              209|       |
 210|             12|              210|       |
 211|             12|              211|       |
2413|             32|             1238|       |
2414|             16|             1239|       |
1679|             30|             1107|       |
1680|             30|             1108|       |
 216|              7|              216|       |
 217|             12|              217|       |
 218|              3|              218|       |
 219|              3|              219|       |
 220|              3|              220|       |
 221|              3|              221|       |
2405|              4|             1231|       |
1681|             30|             1109|       |
1682|             30|             1110|       |
1824|             12|             1118|       |
1832|             30|             1120|       |
2406|             32|              916|       |
1995|              4|              242|       |
1996|              4|              243|       |
 231|              3|              231|       |
 232|              3|              232|       |
 233|              3|              233|       |
 234|              3|              234|       |
 235|              3|              235|       |
 236|              3|              236|       |
 237|              3|              237|       |
 238|              3|              238|       |
1997|              4|              244|       |
1998|              4|              254|       |
1999|              4|              255|       |
2000|              4|              256|       |
2001|              4|              257|       |
2002|              4|              258|       |
2407|             32|              917|       |
2408|             12|             1232|       |
 247|              7|              247|       |
2409|             12|             1233|       |
2415|             16|             1234|       |
2003|              4|              259|       |
2004|              4|              260|       |
2005|              4|              261|       |
2006|              4|              262|       |
2007|              4|              263|       |
2008|              4|              570|       |
2009|              4|              315|       |
2010|              4|              317|       |
2011|              4|              335|       |
2012|              4|              342|       |
2013|              4|              379|       |
2014|              4|              380|       |
 266|             13|              266|       |
 267|             13|              267|       |
 268|              7|              268|       |
 269|              7|              269|       |
 270|              7|              270|       |
 271|              7|              271|       |
 272|              7|              272|       |
 273|              7|              273|       |
 274|              7|              274|       |
2015|              4|              462|       |
 276|              7|              276|       |
 277|              7|              277|       |
 278|              7|              278|       |
 279|              7|              279|       |
 280|              7|              280|       |
 281|              7|              281|       |
 282|              7|              282|       |
 283|              7|              283|       |
2016|              4|              479|       |
2017|              4|              502|       |
2018|              4|              503|       |
 289|              7|              289|       |
 290|              3|              290|       |
 291|              3|              291|       |
2019|              4|              504|       |
 294|              3|              294|       |
 295|              3|              295|       |
 296|              3|              296|       |
 297|              3|              297|       |
 298|              3|              298|       |
 299|              3|              299|       |
 300|              3|              300|       |
 301|              3|              301|       |
 302|              3|              302|       |
 303|              3|              303|       |
 304|              3|              304|       |
 305|              3|              305|       |
 306|              3|              306|       |
 307|              3|              307|       |
2020|              4|              505|       |
2021|              4|              561|       |
 310|              3|              310|       |
 311|              3|              311|       |
2022|              4|              562|       |
2023|              4|              663|       |
2024|              4|              664|       |
2025|              4|              665|       |
 318|              3|              318|       |
 319|              3|              319|       |
 320|              3|              320|       |
 321|              3|              321|       |
 322|              3|              322|       |
 325|              7|              325|       |
 326|              7|              326|       |
2026|              4|              683|       |
2027|              4|              688|       |
 329|              3|              329|       |
 330|              7|              330|       |
 331|              7|              331|       |
2028|              4|              714|       |
 334|              3|              334|       |
2029|              4|              757|       |
 336|             12|              336|       |
 337|             12|              337|       |
 338|              3|              338|       |
 339|              3|              339|       |
 340|              3|              340|       |
2030|              4|              758|       |
 347|              3|              347|       |
 350|              3|              350|       |
 351|              3|              351|       |
 356|             11|              356|       |
 362|             12|              362|       |
 363|             12|              363|       |
 364|             12|              364|       |
 365|             12|              365|       |
 366|             12|              366|       |
 367|             12|              367|       |
 368|             12|              368|       |
 369|             12|              369|       |
 370|             12|              370|       |
 371|             12|              371|       |
 372|             12|              372|       |
 373|             12|              373|       |
 374|             12|              374|       |
 375|              3|              375|       |
 376|              7|              376|       |
 377|             13|              377|       |
 378|             13|              378|       |
 379|             13|              379|       |
 380|             14|              380|       |
 384|              7|              384|       |
 385|              3|              385|       |
 386|              7|              386|       |
 387|              7|              387|       |
 388|              7|              388|       |
 395|              7|              394|       |
 396|              7|              395|       |
 397|              7|              396|       |
 398|              7|              397|       |
 399|              3|              398|       |
 400|             13|              399|       |
 405|              7|              404|       |
 406|              7|              405|       |
 407|              7|              406|       |
 408|              7|              407|       |
 409|              7|              408|       |
 410|              7|              409|       |
 411|              7|              410|       |
 412|              7|              411|       |
 413|              7|              412|       |
 414|              7|              413|       |
 415|              7|              414|       |
 416|              7|              415|       |
 417|              3|              416|       |
 419|             12|              418|       |
 420|              3|              419|       |
 423|              7|              422|       |
 556|             10|              544|       |
 557|             10|              545|       |
 426|             10|              425|       |
 427|             10|              426|       |
 429|             12|              428|       |
 430|             12|              429|       |
 431|             12|              430|       |
2416|             32|             1240|[]     |
 434|              7|              433|       |
 435|              7|              434|       |
 436|             12|              435|       |
 437|              7|              436|       |
 438|              7|              437|       |
 439|             12|              438|       |
 440|              3|              439|       |
 441|             12|              440|       |
 442|              3|              441|       |
 558|              3|              546|       |
2417|              9|             1240|       |
 445|              3|              442|       |
 446|              3|              443|       |
 448|             14|              445|       |
 449|              3|              446|       |
 450|             14|              447|       |
 560|             11|              548|       |
 561|              3|              549|       |
 453|             15|              446|       |
 454|             15|              449|       |
 455|             15|              450|       |
 456|             15|              451|       |
 459|              3|              454|       |
 460|              7|              455|       |
 461|              3|              456|       |
 462|             15|              429|       |
 463|             15|              442|       |
 464|             15|              443|       |
 465|             15|              428|       |
2031|              4|              587|       |
1683|             30|             1111|       |
1685|             30|             1113|       |
2032|              4|              654|       |
2033|              4|              704|       |
2034|              4|              622|       |
 486|             12|              475|       |
2035|              4|              775|       |
2036|              4|              846|       |
2037|              4|              882|       |
2038|              4|              929|       |
2039|              4|              971|       |
 493|              3|              482|       |

CREATE TABLE public.system_program (
   id int4 NOT NULL,
   "name" text NOT NULL,
   controller text NOT NULL,
   actions text NULL,
   CONSTRAINT system_program_pkey PRIMARY KEY (id)
);
id |name                                                            |controller                                  |actions|
---+----------------------------------------------------------------+--------------------------------------------+-------+
  1|System Group Form                                               |SystemGroupForm                             |       |
  2|System Group List                                               |SystemGroupList                             |       |
  3|System Program Form                                             |SystemProgramForm                           |       |
  4|System Program List                                             |SystemProgramList                           |       |
  5|System User Form                                                |SystemUserForm                              |       |
  6|System User List                                                |SystemUserList                              |       |
  7|Common Page                                                     |CommonPage                                  |       |
  8|System PHP Info                                                 |SystemPHPInfoView                           |       |
  9|System ChangeLog View                                           |SystemChangeLogView                         |       |
 11|System Sql Log                                                  |SystemSqlLogList                            |       |
 12|System Profile View                                             |SystemProfileView                           |       |
 13|System Profile Form                                             |SystemProfileForm                           |       |
 14|System SQL Panel                                                |SystemSQLPanel                              |       |
 15|System Access Log                                               |SystemAccessLogList                         |       |
 16|System Message Form                                             |SystemMessageForm                           |       |
 17|System Message List                                             |SystemMessageList                           |       |
 18|System Message Form View                                        |SystemMessageFormView                       |       |
 19|System Notification List                                        |SystemNotificationList                      |       |
 20|System Notification Form View                                   |SystemNotificationFormView                  |       |
 21|System Document Category List                                   |SystemDocumentCategoryFormList              |       |
 22|System Document Form                                            |SystemDocumentForm                          |       |
 23|System Document Upload Form                                     |SystemDocumentUploadForm                    |       |
 24|System Document List                                            |SystemDocumentList                          |       |
 25|System Shared Document List                                     |SystemSharedDocumentList                    |       |
 26|System Unit Form                                                |SystemUnitForm                              |       |
 27|System Unit List                                                |SystemUnitList                              |       |
 28|System Access stats                                             |SystemAccessLogStats                        |       |
 29|System Preference form                                          |SystemPreferenceForm                        |       |
 30|System Support form                                             |SystemSupportForm                           |       |
 31|System PHP Error                                                |SystemPHPErrorLogView                       |       |
 32|System Database Browser                                         |SystemDatabaseExplorer                      |       |
 33|System Table List                                               |SystemTableList                             |       |
 34|System Data Browser                                             |SystemDataBrowser                           |       |
 35|System Menu Editor                                              |SystemMenuEditor                            |       |
 36|System Request Log                                              |SystemRequestLogList                        |       |
 37|System Request Log View                                         |SystemRequestLogView                        |       |
 38|System Administration Dashboard                                 |SystemAdministrationDashboard               |       |
 39|System Log Dashboard                                            |SystemLogDashboard                          |       |
 40|System Session dump                                             |SystemSessionDumpView                       |       |
 41|System files diff                                               |SystemFilesDiff                             |       |
 42|System Information                                              |SystemInformationView                       |       |
 43|Cadastro Bombas Abastecimento                                   |BombaHeaderList                             |       |
 44|Cadastro de Tanques                                             |TanqueHeaderList                            |       |
 45|Tipo saída combustível                                          |TipoSaidaCombustivelHeaderList              |       |
 46|Acerto estoque combustível                                      |AcertoEstoqueCombustivelHeaderList          |       |
 47|Abastecimentos                                                  |AbastecimentoHeaderList                     |       |
 48|Encerrantes Abastecimento                                       |EncerranteHeaderList                        |       |
 49|Padrão consumo modelo veículos                                  |PadraoConsumoModeloVeiculoHeaderList        |       |
 50|Preço postos credenciados                                       |PrecoProstoCredenciadoHeaderList            |       |
 51|Recebimento combustíveis                                        |RecebimentoCombustivelHeaderList            |       |
 52|Saída especial combustível                                      |SaidaEspecialCombustivelHeaderList          |       |
 53|Cadastro de bomba                                               |BombaForm                                   |       |
 54|Cadastro de tanque                                              |TanqueForm                                  |       |
 55|Cadastro de encerrante                                          |EncerranteForm                              |       |
 56|Cadastro de recebimento combustível                             |RecebimentoCombustivelForm                  |       |
 57|Cadastro de tipo saída combustível                              |TipoSaidaCombustivelForm                    |       |
 58|Cadastro de saída especial combustível                          |SaidaEspecialCombustivelForm                |       |
 59|Cadastro de acerto estoque combustível                          |AcertoEstoqueCombustivelForm                |       |
 60|Cadastro de Padrão consumo modelo veículo                       |PadraoConsumoModeloVeiculoForm              |       |
 61|Cadastro de abastecimento                                       |AbastecimentoForm                           |       |
 62|Cadastro de preço posto credenciado                             |PrecoProstoCredenciadoForm                  |       |
 63|Assumir Solicitações Abertas                                    |SolicitacoescomprasList                     |       |
 64|Editar/Alterar Orçamentos                                       |OrcamentoSeekWindow                         |       |
 65|Solicitação de Compras (Direta)                                 |SolicitacoescomprasDiretaList               |       |
 66|Produtos Orçamentos                                             |OrcamentoHeaderList                         |       |
 67|Gerenciar Pedidos de Compras                                    |PedidoComprasList                           |       |
 68|Orcamento                                                       |OrcamentoForm                               |       |
 69|Gerar Pedido                                                    |PedidoComprasForm                           |       |
 70|Solicitação de Compras                                          |PedidoComprasDocument                       |       |
 71|Solicitação de Compras (Direta)                                 |SolicitacoescomprasDiretaForm               |       |
 72|Itens Solicitações de Compras                                   |SolicitacoescomprasDocument                 |       |
 73|Rel_listaItensCompras                                           |ItenssolicitacoescomprasReport              |       |
 74|Pedidos de Compras                                              |SolicitacoescomprasForm                     |       |
 75|Itens Orçamento                                                 |ItensOrcamentosSimpleList                   |       |
 76|Pedido de Compras Cadastros                                     |ProdutosOrcamentoForm                       |       |
 77|Cadastro de Produtos                                            |ProdutoHeaderList                           |       |
 78|Nota Fiscal de Entrada                                          |NotaFiscalEntradaList                       |       |
 79|Solicitação Produtos Estoque (Direta)                           |RelacaosolicitacoespecasDiretaList          |       |
 80|Cadastro Unidade de Produto                                     |UnidadeprodutoHeaderList                    |       |
 81|Cadastro de  Grupo                                              |GrupoServicoHeaderList                      |       |
 82|Cadastro de Subgrupo                                            |SubgrupoServicoHeaderList                   |       |
 83|Cadastros de Estoques                                           |EstoqueHeaderList                           |       |
 84|Cadastro de Tipo Acerto Estoques                                |TipoAcertoEstoqueHeaderList                 |       |
 85|Ajuste de Estoque                                               |AcertoEstoqueHeaderList                     |       |
 86|Consulta de Requisição de Peças                                 |RelacaosolicitacoespecasList                |       |
 87|Baixa de Produtos do Estoque                                    |ProdutossolicitacoesForm                    |       |
 88|Cadastro de Tipo de Acerto Estoque                              |TipoAcertoEstoqueForm                       |       |
 89|Ajuste de Estoque                                               |AcertoEstoqueForm                           |       |
 90|Cadastro Solicitações de Peças                                  |RelacaosolicitacoespecasForm                |       |
 91|Solicitação de Produtos Estoque (Direta)                        |RelacaosolicitacoespecasDiretaForm          |       |
 92|Baixapecass                                                     |BaixapecasHeaderList                        |       |
 93|Cadastro de Unidade Produto                                     |UnidadeprodutoForm                          |       |
 94|Cadastro de Grupo                                               |GrupoServicoForm                            |       |
 95|Cadastro de Subgrupo                                            |SubgrupoServicoForm                         |       |
 96|Cadastro Nota Fiscal                                            |NotaFiscalEntradaForm                       |       |
 97|Cadastro de Estoque                                             |EstoqueForm                                 |       |
 98|Cadastro de Produtos                                            |ProdutoForm                                 |       |
 99|Consulta de Produtos                                            |ProdutoFormView                             |       |
100|Cadastro de baixapecas                                          |BaixapecasForm                              |       |
101|Produtos Solicitação                                            |solicitacoesProdutos                        |       |
102|Lista de Produtos para Baixa                                    |ProdutossolicitacoesList                    |       |
103|Manutenção de Jornada                                           |JornadaLogList                              |       |
104|Impressão da Jornada                                            |JornadaHistoricoList                        |       |
105|Jornada justificativas                                          |JornadaJustificativaHeaderList              |       |
106|Calculo Jornada                                                 |JornadaHistoricoCalculo                     |       |
107|Cadastro de jornada justificativa                               |JornadaJustificativaForm                    |       |
108|Jornada Analitico Totalizador                                   |JornadaHistoricoDocumentAnalitico           |       |
109|Jornada Sintetico                                               |JornadaHistoricoDocument                    |       |
110|Dashboard                                                       |dashboardgestaofrotas                       |       |
111|teste do sistema                                                |testedosistema                              |       |
112|DashBoard                                                       |dashTemplate                                |       |
113|Ordem Serviço Kanban                                            |OrdemServicoKanbanView                      |       |
114|Veículos No Pátio com Manutenção Aberta                         |VeiculosReclamacoesAbertasCardList          |       |
115|Tipo de Manutenção                                              |TipomanutencaoHeaderList                    |       |
116|Tipo de Ordem de Serviço                                        |TipoOrdemServicoHeaderList                  |       |
117|Situação Ordem de serviço                                       |StatusOrdemServicoHeaderList                |       |
118|Manutenção                                                      |ManutencaoHeaderList                        |       |
119|Serviços                                                        |ServicoHeaderList                           |       |
120|Planejamento Manutenção                                         |PlanejamentomanutencaoHeaderList            |       |
121|Cadastro de tabela de Preço de Serviços                         |TabelaDePrecoServicosFornecedorHeaderList   |       |
122|Desativado...03                                                 |desativado                                  |       |
123|Reclamações Motoristas                                          |ReclamacoesveiculosHeaderList               |       |
124|Desativada....01                                                |Execececc                                   |       |
125|Consultar Ordem de Serviço                                      |OrdemServicoHeaderList                      |       |
126|Serviço Externo Valores                                         |ServicoExternoValoresForm                   |       |
127|Cadastro de tabela de preco servicos fornecedor                 |TabelaDePrecoServicosFornecedorForm         |       |
128|Imprimir Ordem de Serviço                                       |OrdemServicoDocument                        |       |
129|Desativado...04                                                 |desattivado                                 |       |
130|Cadastro de Situação da Ordem de Serviço                        |StatusOrdemServicoForm                      |       |
131|Cadastro de tipo de Ordem de Serviço                            |TipoOrdemServicoForm                        |       |
132|Cadastros de Reclamações Veículos                               |ReclamacoesveiculosForm                     |       |
133|Cadastro de manutencao                                          |ManutencaoForm                              |       |
134|Cadastro de Tipo de Manutenção                                  |TipomanutencaoForm                          |       |
135|Cadastro de servico                                             |ServicoForm                                 |       |
136|Cadastro de Planejamento de Manutenção                          |PlanejamentomanutencaoForm                  |       |
137|Desativada...02                                                 |Exexc                                       |       |
138|Cadastro de Ordem de Serviço                                    |OrdemServicoForm                            |       |
139|Cadastro de Pessoal                                             |PessoalHeaderList                           |       |
140|Cadastro de Fornecedor                                          |FornecedorHeaderList                        |       |
141|Tipo de Pessoa                                                  |TipopessoalHeaderList                       |       |
142|Cadastro Tipo de Fornecedor                                     |TipofornecedorHeaderList                    |       |
143|Cadastro de Departamentos                                       |DepartamentoHeaderList                      |       |
144|Municípios                                                      |MunicipioHeaderList                         |       |
145|Cadastro de fornecedor                                          |FornecedorForm                              |       |
146|Departamentos                                                   |DepartamentoHeaderListII                    |       |
147|Cadastro de Departamento                                        |DepartamentoForm                            |       |
148|Cadastro de departamento                                        |DepartamentoFormII                          |       |
149|Cadastro de Município                                           |MunicipioForm                               |       |
150|Cadastro de Tipo de Pessoa                                      |TipopessoalForm                             |       |
151|Tipo Fornecedor                                                 |TipofornecedorForm                          |       |
152|Cadastro de pessoal                                             |PessoalForm                                 |       |
153|Cadastro de Dimensão de Pneus                                   |DimensaopneuHeaderList                      |       |
154|Cadastro de Desenho Pneu                                        |DesenhopneuHeaderList                       |       |
155|Cadastro Tipo Estado Pneus                                      |SituacaoPneuHeaderList                      |       |
156|Cadastro de Tipo de Borracha                                    |TipoborrachaHeaderList                      |       |
157|Cadastro de Tipo Reformas                                       |TiporeformaHeaderList                       |       |
158|Cadastro de Tipo de Descartes                                   |TipodescarteHeaderList                      |       |
159|Cadatro de Descarte de Pneus                                    |DescartepneuHeaderList                      |       |
160|Cadastros de Pneus                                              |PneuList                                    |       |
161|Pneus Aplicados                                                 |VeiculoXPneuList                            |       |
162|Aplicação de pneus e placas                                     |VeiculoXPneuForm                            |       |
163|Cadastro de Dimensão Pneu                                       |DimensaopneuForm                            |       |
164|Cadastro de Pneu                                                |PneuForm                                    |       |
165|Cadastro de Descarte Pneu                                       |DescartepneuForm                            |       |
166|Cadastro de Tipo de borracha                                    |TipoborrachaForm                            |       |
167|Cadastro de Tipo Reforma                                        |TiporeformaForm                             |       |
168|Cadastro de Tipo de Descarte                                    |TipodescarteForm                            |       |
169|Pneus Aplicados                                                 |pneusAplicadosForm                          |       |
170|Cadastro de Tipo Estado Pneu                                    |SituacaoPneuForm                            |       |
171|Cadastro de Desenho Pneu                                        |DesenhopneuForm                             |       |
172|Cadastro de Tipo Ocorrências                                    |TipoocorrenciaHeaderList                    |       |
173|Cadastro de Motivo Sinistros                                    |MotivosinistroHeaderList                    |       |
174|Cadastro de Tipo Orgão                                          |TipoorgaosinistroHeaderList                 |       |
175|Cadastro de Sinistros                                           |SinistroHeaderList                          |       |
176|Cadastro de Sinistro                                            |SinistroForm                                |       |
177|Cadastro de Tipo Orgão                                          |TipoorgaosinistroForm                       |       |
178|Cadastro de Motivo Sinistro                                     |MotivosinistroForm                          |       |
179|Cadastro de Tipo Ocorrencia                                     |TipoocorrenciaForm                          |       |
180|Cadastro de Veículos                                            |VeiculoHeaderList                           |       |
181|Despesas veículos                                               |DespesasVeiculosHeaderList                  |       |
182|Atrelar Veículos                                                |AtrelarveiculoHeaderList                    |       |
183|Modelo veiculos                                                 |ModeloVeiculoHeaderList                     |       |
184|Cadatro Tipo de Equipamento                                     |TipoequipamentoHeaderList                   |       |
185|Cadastro de Tipo de Combustível                                 |TipocombustivelHeaderList                   |       |
186|Categoria de Veículos                                           |CategoriaIIVeiculoHeaderList                |       |
187|Classificação multas                                            |ClassificacaoMultaHeaderList                |       |
188|Motivo multas                                                   |MotivoMultaHeaderList                       |       |
189|Cadastro Licenciamento de Veículos                              |LicenciamentoveiculoHeaderList              |       |
190|Cadastro de IPVA                                                |IpvaveiculoHeaderList                       |       |
191|Cadastro do Seguro Obrigatório                                  |SeguroobrigatorioveiculoHeaderList          |       |
192|Cadastro de Tipo de Certificados                                |TipocertificadoHeaderList                   |       |
193|Cadastro de Certificado do Veículo                              |CertificadoveiculoHeaderList                |       |
194|Regitro de Compra/Venda                                         |RegistrocompravendaHeaderList               |       |
195|Cadastro de despesas veiculos                                   |DespesasVeiculosForm                        |       |
196|Cadastro de classificação multa                                 |ClassificacaoMultaForm                      |       |
197|Cadastro de motivo multa                                        |MotivoMultaForm                             |       |
198|Cadastro de modelo veiculo                                      |ModeloVeiculoForm                           |       |
199|Cadastro de Licenciamento de Veículos                           |LicenciamentoveiculoForm                    |       |
200|Cadastro Tipo de Equipamento                                    |TipoequipamentoForm                         |       |
201|Cadastro de Tipo de Combustível                                 |TipocombustivelForm                         |       |
202|Cadastro de Categoria de Veículo                                |CategoriaVeiculoForm                        |       |
203|Categoria veiculos                                              |CategoriaVeiculoHeaderList                  |       |
204|Cadastro de categoria veiculo                                   |CategoriaIIVeiculoForm2                     |       |
205|Cadatro de Tipo de Certificados                                 |TipocertificadoForm                         |       |
206|Cadastro de Certificado do Veículo                              |CertificadoveiculoForm                      |       |
207|Cadastro de IPVA Veículo                                        |IpvaveiculoForm                             |       |
208|Cadastro do Seguro Obrigatório                                  |SeguroobrigatorioveiculoForm                |       |
209|Registro de Compra/Venda                                        |RegistrocompravendaForm                     |       |
210|Cadastro de veiculos                                            |VeiculoForm                                 |       |
211|Cadastro de Atrelamento de Veículos                             |AtrelarveiculoForm                          |       |
212|Listagem de reclamações veículos                                |ReclamacoesveiculosList                     |       |
213|Veículos com Ordem de Serviço Aberta                            |OrdemServicoCardList                        |       |
214|Relatorio de Folgas                                             |JornadaLogDocument                          |       |
215|Edição do Ponto                                                 |JornadaLogForm                              |       |
216|Veíulos em movimento                                            |veiculosEmMovimento                         |       |
217|Relatório de Veículos                                           |VeiculoReport                               |       |
218|pagianCarvalima                                                 |paginaCarvalima                             |       |
219|Teste integração                                                |AbastecimentoIntegracaoForm                 |       |
220|Abastecimentos Via Integração                                   |AbastecimentoIntegracaoList                 |       |
221|Processar Integração Abastecimento                              |processarIntegracaoController               |       |
222|O.S Corretiva                                                   |OrdemServicoFormCorretiva                   |       |
223|Listagem de O.S Corretivas                                      |OrdemServicoPecasList                       |       |
224|Cadastro Regra Jornada                                          |JornadaConfiguracaoForm                     |       |
225|Jornada Analitico TOT                                           |JornadaHistoricoBatchDocument               |       |
226|Cadastro Justificativa Ponto                                    |JornadaJustificativaPontoForm               |       |
227|Relatório de Jornada                                            |JornadaHistoricoReport                      |       |
228|Resumo Diario de Jornada                                        |JornadaHistoricoBatchDocumentResumoSintetico|       |
229|Jornada Resumo Sintentico                                       |JornadaHistoricoDocumentResumoSintetico     |       |
230|Jornada Analiticopd                                             |JornadaHistoricoReporttpd                   |       |
231|Relatório de Abastecimento                                      |AbastecimentoBatchDocument                  |       |
232|Relatório de Abastecimentos                                     |AbastecimentoDocument                       |       |
233|Listar Abastecimentos                                           |AbastecimentoList                           |       |
234|Relatório AbastecimentoMedia                                    |AbastecimentoDocumentMedia                  |       |
235|Saldo de Combustível                                            |EstoqueCombustivelList                      |       |
236|Relatório de Encerrantes                                        |ReportEncerranteDocument                    |       |
237|Encerrante                                                      |EncerranteReport                            |       |
238|Relação de Saídas Especiais                                     |SaidaEspecialCombustivelReport              |       |
239|Configurar Solicitação de Compras                               |SolicitacoescomprasConfiguracaoList         |       |
240|Configurar Solicitações Compras                                 |SolicitacoescomprasConfigurarForm           |       |
241|Consulta de Orçamento                                           |OrcamentoFormView                           |       |
242|Pedido de Compras Aprovados                                     |PedidoComprasAprovadosList                  |       |
243|Saving                                                          |savingCompras                               |       |
244|Itens Solicitações de Compras                                   |ItenssolicitacoescomprasList                |       |
245|Relatório Manutenções                                           |VPrevisaomanutencaoList                     |       |
246|Controle de Preventivas                                         |controlePreventivasDash                     |       |
247|DetailMovement                                                  |DetailMovement                              |       |
248|Planejamento Manutenção por Categoria                           |CategoriaPlanejamentoManutencaoList         |       |
249|Planejamento Manutenção por Categoria                           |CategoriaPlanejamentoManutencaoForm         |       |
250|Programar Manutenção Preventiva                                 |ProgramarManutencaoForm                     |       |
251|Manutençõeos Vencidas                                           |VPrevisaomanutencaoForm                     |       |
252|Calendário Manutenção(Form)                                     |ProgramarManutencaoCalendarForm             |       |
253|Calendário Manutenção(View)                                     |ProgramarManutencaoCalendarFormView         |       |
254|Arquivos de Cotação                                             |CotacoesDocument                            |       |
255|Lista de Arquivos Contações                                     |CotacoesList                                |       |
256|Orcamento Recebido                                              |CotacoesForm                                |       |
257|Cotações Orçamentos Editar                                      |CotacoesListOrcamentos                      |       |
258|teste sistema                                                   |CotacoesDocumentteste                       |       |
259|Gerar Pedidos de Compras                                        |VCotacoesOrcamentosFinalizadosList          |       |
260|Aprovar pedido de compras                                       |PedidoComprasListAprovar                    |       |
261|Pedido de  compras                                              |PedidoComprasFormAprovar                    |       |
262|Pedidos Aguardando Aprovação                                    |PedidoaguadandoaprovacaoForm                |       |
263|Itens para cotação                                              |ItenparacotacaoList                         |       |
264|Jornada Analitico                                               |VJornadaHistoricoRelAnaliticoGeralReport    |       |
265|TesteJornaHist                                                  |JornadaHistoricoDocumentteste               |       |
266|Cadastro de Aprovadores                                         |AprovadorespedidosForm                      |       |
267|Cadastro de Aprovadores                                         |AprovadorespedidosList                      |       |
268|Relatório de Faixas                                             |VRelatorioFaixasReport                      |       |
269|Eventos de Sistema de Ar                                        |EventoSistemaArList                         |       |
270|Evento pressao do oleo                                          |EventoSistemaPressaoOleoList                |       |
271|Motoristas sem jornada                                          |MotoristaSemJornadaHojeList                 |       |
272|Relatório Eventos Telemetria                                    |VRelatorioEventosReport                     |       |
273|Relatórios Motoristas                                           |ObtermotoristasList                         |       |
274|Motorista sem login                                             |MotoristasSemLoginList                      |       |
275|Jornada Eventos Diarios                                         |JornadaLogReportEventos                     |       |
276|Relatório Veiculo Trafegando Após 22h                           |VVeiculosApos22hReport                      |       |
277|Grid de Posição                                                 |VPosicaoVeiculosList                        |       |
278|Editar Tratativa do Evento                                      |ObtereventotelemetriaintegracaohistoricoForm|       |
279|Tratativas de Eventos                                           |EventosTratarList                           |       |
280|Eventos Intelbras                                               |EventosIntelbrasList                        |       |
281|Eventos de Câmeras Tratar                                       |EventosCamerasList                          |       |
282|Tratar Eventos de Câmeras                                       |EventosIntelbrasForm                        |       |
283|Relatório Média Motorista                                       |VRelatorioMediaMotoristaReport              |       |
284|Relatório de Jornada Consolidado                                |JornadaHistoricoConsolidadeReport           |       |
285|Manutenção de Ponto                                             |ManutencaoPontoList                         |       |
286|Editar Manutenção Ponto                                         |ManutencaoPontoForm                         |       |
287|Baixar Estoque                                                  |RelacaosolicitacoespecasBaixarEstoqueForm   |       |
288|Lista de Produtos                                               |ProdutossolicitacoesBaixarList              |       |
289|Veículos Sem Movimento                                          |VVeiculosSemMovimentoList                   |       |
290|Abastecimentos (Lançamentos ATS)                                |AbastecimentoIntegracaoLancamentoAtsList    |       |
291|Editar Abastecimento ATS                                        |AbastecimentoIntegracaoATSForm              |       |
292|Relação de Solicitações de Peças                                |RelacaosolicitacoespecasDocument            |       |
293|PDFJASPER                                                       |pdfjasper                                   |       |
570|DashBoard Compras                                               |dashboardcompras                            |       |
294|Processar Integração Truck Pag                                  |AbastecimentoTruckPagList                   |       |
295|apresentacao                                                    |apresentacao                                |       |
296|TruckPagDocumento                                               |AbastecimentoTruckPagDocument               |       |
297|Relatório Jasper                                                |AbastecimentoTruckPagRelatorioList          |       |
298|Relatório Listagem de Abastecimento Por Posto                   |VAbastecimentoReport                        |       |
299|Abastecimento por Placas                                        |VAbastecimentoPorPlacaReport                |       |
300|Relatório Consumo de Combustível                                |VConsumoCombustivelVeiculoReport            |       |
301|Corrigir Lançamento ATS                                         |AbastecimentoIntegracaoCorrigirForm         |       |
302|Listagem de Incosistências Integrações ATS                      |VInconsistenciasAtsList                     |       |
303|Listagem de Inconsistência TruckPag                             |VInconsistenciasTruckPagList                |       |
304|Corrigir Abastecimento integração TruckPag                      |AbastecimentoTruckPagCorrigirForm           |       |
305|Preço Combustível para Terceiro                                 |ValorCombustivelTerceiroForm                |       |
306|Preço Combustível Terceiros                                     |ValorCombustivelTerceiroList                |       |
307|Apuração de Saldo                                               |ApuracaoSaldoCombustivelList                |       |
308|Apuração de Jornadat                                            |VApuracaoJornadaFormViewt                   |       |
309|Apuração de Jornada                                             |VApuracaoJornadaList                        |       |
310|Apuração Combustível                                            |ApuracaoSaldoCombustivelForm                |       |
311|Dash Abastecimento                                              |dashAbastecimento                           |       |
312|Cadastro de Fornecedor X Pessoal                                |FornecedorxprestadoresForm                  |       |
313|Cadastros de Fornecedores/Pessoas                               |FornecedorxprestadoresList                  |       |
314|IntegraJasperReportServer                                       |IntegraJasperReportServer                   |       |
315|Mapa de Cotação de Pedidos                                      |mapacontacaoForm                            |       |
316|IntegraJasperReportServer(Clonado)                              |IntegraJasperReportServerClonado            |       |
317|Aprovar Pedido                                                  |AprovarPedidoList                           |       |
318|Recebimento de Combustível(Form)                                |RecebimentoCombustivelCalendarForm          |       |
319|Recebimento de Combustível(View)                                |RecebimentoCombustivelCalendarFormView      |       |
320|Abastecimento Agrupados por Posto                               |VAbastecimentoAgrupadoPostoReport           |       |
321|Check-List                                                      |checkListBasico                             |       |
322|Abastecimentos TruckPag                                         |AbastecimentoTruckPagReport                 |       |
323|Relatório Manutenções Agendadas                                 |ProgramarManutencaoReport                   |       |
324|Relatório de Manutenções                                        |VPrevisaomanutencaoReport                   |       |
325|Posição Veículos                                                |VPosicaoVeiculosRangeCardList               |       |
326|Posição Atual dos Veículos                                      |VPosicaoVeiculosRangeList                   |       |
327|Jornada Eventos Diarios                                         |JornadaLogReport                            |       |
328|Eventos Diários-teste                                           |JornadaLogReportClonado                     |       |
329|Abastecimento Integração WS                                     |AbastecimentoReport                         |       |
330|Veículos Sem Login                                              |VVeiculosSemLoginReport                     |       |
331|Veículos Com Login                                              |VVeiculosComLoginReport                     |       |
332|Jornada Analitico - modelo                                      |JornadaHistoricoReportClonado               |       |
333|Lista de Pedidos de Compras                                     |ListagemPedidoComprasList                   |       |
334|Fechamento Abastecimento                                        |AbastecimentoReportFecha                    |       |
335|Planilha de Saving                                              |SavingPlanilhaList                          |       |
336|Cadastro tipo de multa                                          |TipoMultaForm                               |       |
337|Listagem de Multas                                              |TipoMultaList                               |       |
338|Faturamento TruckPag                                            |FaturamentoTruckPagForm                     |       |
339|Faturamento Abastecimento                                       |FaturamentoAbastecimentoForm                |       |
340|Faturados e Não faturados                                       |AbastecimentoTruckPagListFaturamento        |       |
341|Relatório de Baixas Estoque                                     |VSolicitacoesPecasReport                    |       |
342|Pedidos Aprovados                                               |PedidoComprasListAprovados                  |       |
343|Relação de Saídas por Departamento                              |VRelacaoSaidasPorDepartamentoReport         |       |
344|Relatório Conferência Rotativo Diário                           |VConferenciaRotativoDiarioReport            |       |
345|Consulta de Produtos Solicitação                                |ProdutoListsolicitacoesdiretas              |       |
346|Saldo Estoque                                                   |ProdutoList                                 |       |
347|Lista de Veículos Sem Abastecimento                             |VVeiculosSemAbastecerCardList               |       |
348|Aprovação de Pedidos                                            |RelacaosolicitacoespecasListAprovacao       |       |
349|Aprovação Pedidos ao Estoque                                    |RelacaosolicitacoespecasFormAprovacaoPedidos|       |
350|Veiculos N Abastecidos                                          |VVeiculosSemAbastecerList                   |       |
351|Veículos sem Abastecimento                                      |AbastecimentoCardList                       |       |
352|Lista Notas Fiscais de Serviço                                  |NotaFiscalServicoList                       |       |
353|Cadastro Nota Fiscal de Serviço                                 |NotaFiscalServicoForm                       |       |
354|Cadastro de solicitação de Produtos Transferência entre estoques|SolicitacaoEstoqueTransferenciaForm         |       |
355|Solicitação de transferências entre estoque                     |SolicitacaoEstoqueTransferenciaList         |       |
356|Sinistro                                                        |SinistroReport                              |       |
357|Cadastro de transferência estoque                               |TransferenciaEstoqueForm                    |       |
358|Transferencia estoques                                          |TransferenciaEstoqueList                    |       |
359|Solicitações de Transferência Baixar                            |TransferenciaEstoqueListBaixa               |       |
360|Transferência entre estoque                                     |TransferenciaEstoqueFormBaixa               |       |
361|Transferência entre estoques                                    |TransferenciaEstoqueDocument                |       |
362|Consulta de Veiculos                                            |VeiculoFormView                             |       |
363|Consultar de Veículos                                           |VeiculoListVisualizar                       |       |
364|Extrato Ipva                                                    |IpvaveiculoReport                           |       |
365|Cadastro de Checklist Entrada e Saída                           |ChecklistDeEntradasaidaForm                 |       |
366|Lista Checklist (Sider)                                         |ChecklistSiderList                          |       |
367|Cadastro Checklist (Sider)                                      |ChecklistSiderForm                          |       |
368|Cadastro de Checklist Truck-Toco                                |ChecklistTruckTocoForm                      |       |
369|Cadastro de Checklist Graneleira                                |ChecklistGraneleiraForm                     |       |
370|Cadastro de Checklist Cavalos                                   |ChecklistCavaloForm                         |       |
371|Lista Checklist (Cavalos)                                       |ChecklistCavaloList                         |       |
372|Lista Checklist (Graneleira)                                    |ChecklistGraneleiraList                     |       |
373|Lista Checklist (Truck-Tocos)                                   |ChecklistTruckTocoList                      |       |
374|Checklists de Entrada e Saída                                   |ChecklistDeEntradasaidaList                 |       |
375|Encerrantes                                                     |VEncerranteReport                           |       |
376|Pagina Load                                                     |testeLoad                                   |       |
377|Cadastro de usuário X departamento                              |UsuarioDeparmantoForm                       |       |
378|Usuario deparmantos                                             |UsuarioDeparmantoList                       |       |
379|Solicitações de Compras                                         |SolicitacoescomprasExternaForm              |       |
380|Solicitações de Compras                                         |SolicitacoescomprasExternaList              |       |
382|Relatório de Manutenções Vencidas                               |VPlanejamentomanutencaoRelatorioReport      |       |
383|Relatório de Comissionados                                      |VRelatoriocomissionadosReport               |       |
384|Painel de Teste                                                 |ClassPainelTeste                            |       |
385|Consumo de Combustível Analitico                                |VConsumoCombustivelVeiculoDocument          |       |
386|VVelocidade Seco Form                                           |VVelocidadeSecoForm                         |       |
387|VVelocidade Seco List                                           |VVelocidadeSecoList                         |       |
388|dashboard_unitop                                                |dashboard_unitop                            |       |
389|Ordem de Serviços Abertas                                       |OrdemServicoReport                          |       |
390|Cadastro de tipo despesas                                       |TipoDespesasForm                            |       |
391|Tipo de Despesas de Veículos                                    |TipoDespesasList                            |       |
392|Lista de Despesas Veículos                                      |DespesasVeiculosList                        |       |
393|Produtos em Estoque                                             |ProdutosaldoReport                          |       |
394|Listar Veículos Movimento                                       |VMovimentoList                              |       |
395|Gride de Posição                                                |VMovimentoForm                              |       |
396|Lista Excesso de Velocidade Chuva                               |VVelocidadeChuvaList                        |       |
397|Execesso de Velocidade Chuva                                    |VVelocidadeChuvaForm                        |       |
398|Fechamento Abastecimento                                        |VAbastecimentoReportFechamento              |       |
399|Dados Empresa                                                   |EmpresaForm                                 |       |
400|Reclamação de Motoristas(Clonado)                               |ReclamacoesveiculosFormClonado              |       |
401|Despesas Cadastro Veiculos List                                 |DespesasCadastroVeiculosList                |       |

CREATE TABLE public.system_unit (
    id int4 NOT NULL,
    "name" text NOT NULL,
    connection_name text NULL,
    CONSTRAINT system_unit_pkey PRIMARY KEY (id)
);
id|name                      |connection_name|
--+--------------------------+---------------+
 1|Matriz                    |matriz         |
 2|Campo Grande              |               |
 3|São Paulo                 |               |
 4|Cuiabá                    |               |
 5|Dourados                  |               |
 6|Vilhena                   |               |
 7|Curitiba                  |               |
 8|Rondonópolis              |               |
 9|Navegantes                |               |
10|Joinville                 |               |
11|Sinop                     |               |
12|Fedrizze Participações S/A|               |
13|Fazenda Malibu            |               |
15|Ji-Paraná                 |               |
17|Porto Velho               |               |
18|Terceiros                 |               |
20|Londrina                  |               |
21|Rio Branco                |               |
22|Dist. Santiago do Norte   |               |
16|Unidades                  |               |
19|Unidade Sorriso           |               |
23|Belém                     |               |
24|Novo Progresso            |               |
25|Goiânia                   |               |
26|Porto Alegre              |               |

CREATE TABLE public.system_user_group (
      id int4 NOT NULL,
      system_user_id int4 NOT NULL,
      system_group_id int4 NOT NULL,
      CONSTRAINT system_user_group_pkey PRIMARY KEY (id),
      CONSTRAINT system_user_group_system_group_id_fkey FOREIGN KEY (system_group_id) REFERENCES public.system_group(id),
      CONSTRAINT system_user_group_system_user_id_fkey FOREIGN KEY (system_user_id) REFERENCES public.system_users(id)
);
CREATE INDEX sys_user_group_group_idx ON public.system_user_group USING btree (system_group_id);
CREATE INDEX sys_user_group_user_idx ON public.system_user_group USING btree (system_user_id);

id   |system_user_id|system_group_id|
-----+--------------+---------------+
 6423|           405|              2|
 6424|           405|             29|
 9079|            31|              2|
 7018|           202|              5|
 2069|           108|              2|
 9080|            31|              6|
10300|           472|              2|
 2076|            91|              2|
 1857|           149|              2|
10301|           472|              3|
10302|           472|             15|
 2077|            91|              3|
   56|             7|              2|
 9081|            31|              7|
 1964|           151|              2|
 7020|           216|              5|
 7021|           204|              5|
 7022|            54|              5|
 2315|           187|              2|
 2316|           187|              3|
 5031|           362|              2|
 9082|            31|             15|
 3871|           275|              2|
 2101|            74|              2|
 5055|           368|              2|
 7025|            46|              5|
 5057|           368|             10|
 9083|            31|             29|
 7026|           154|              5|
 2533|           203|              2|
 2534|           203|              3|
 2912|           182|              2|
 7027|           269|              5|
 9084|            31|             32|
 2536|           203|              7|
 2537|           203|             14|
 2538|           203|             15|
 7789|           119|              2|
 2762|           212|              2|
 2763|           212|              3|
 2764|           212|             12|
10915|           302|              2|
 2765|           212|             15|
 2766|           213|              2|
10916|           302|              3|
 8130|           377|              2|
10917|           302|              5|
10918|           302|              8|
 2786|           214|              2|
 7030|           227|              5|
10919|           302|             10|
 2787|           214|             14|
 5058|           368|             22|
 2120|            52|              2|
 7368|           387|              2|
 8131|           377|              7|
 7369|           387|             29|
 2158|           181|              2|
 7791|           119|             29|
 8132|           377|             15|
 8133|           377|             29|
 2948|           154|              2|
 2949|           154|              3|
 2952|           154|              7|
 2005|           164|              2|
 3098|            46|              1|
 3099|            46|              2|
 3100|            46|              3|
 8673|            36|              2|
10364|           245|              2|
10365|           245|              3|
10366|           245|              7|
10367|           245|             14|
 8674|            36|             29|
 2023|           175|              2|
 2028|           170|              2|
 2029|           170|              3|
 2034|           161|              2|
 2040|           138|              2|
 2041|           124|              2|
10368|           245|             15|
10369|           245|             29|
10377|           403|              2|
10378|           403|              5|
 2060|           104|              2|
10379|           403|             29|
10920|           302|             19|
10921|           302|             29|
10396|           513|              2|
 9994|            26|              8|
 2099|            72|              2|
 9996|            86|              8|
11050|            33|              2|
 2115|            50|              2|
11051|            33|              3|
11052|            33|              9|
 2117|            50|             10|
 2118|            50|             11|
 9999|            50|              8|
 1703|             4|              1|
 1704|             4|              2|
 1705|             4|              3|
10001|            27|              8|
 1709|             4|              7|
11053|            33|             12|
11054|            33|             15|
 1712|             4|             10|
 1713|             4|             11|
11055|            33|             29|
  834|            80|              2|
 1714|             4|             12|
 1715|             4|             13|
 3872|           276|              2|
10005|           108|              8|
 9680|           498|              2|
 1716|             4|             14|
 1717|             4|             15|
 7526|            70|              1|
 7527|            70|              2|
 9681|           498|             29|
10006|           147|              8|
 6254|           399|              2|
 4434|           332|              2|
 7528|            70|             29|
10007|           188|              8|
10008|           106|              8|
11114|           283|              2|
11115|           283|             29|
 6968|           227|             18|
 6973|           291|             18|
10010|           107|              8|
10011|           197|              8|
 7645|           453|              2|
 7646|           453|              5|
 7647|           453|             19|
 7648|           453|             29|
11867|             1|              1|
11868|             1|              2|
11869|             1|              3|
11870|             1|              4|
11871|             1|              5|
11872|             1|              6|
11873|             1|              7|
11874|             1|              8|
11875|             1|              9|
 6257|           399|             12|
 6258|           399|             22|
 6259|           399|             29|
 4439|           220|              2|
 4532|           180|              2|
 2879|           204|              2|
 2880|           204|              3|
 3953|           278|              2|
 2428|           196|              2|
 3449|           106|              2|
 3450|           106|              3|
 8089|             3|              1|
 2430|           196|             10|
 8090|             3|              2|
 8091|             3|              3|
 2024|           174|              2|
 2431|           196|             11|
  937|            81|              2|
 2055|           121|              2|
 2503|           202|              1|
 2504|           202|              2|
 2505|           202|              3|
 2509|           202|              7|
10881|           127|              2|
10882|           127|              3|
10883|           127|              8|
 2512|           202|             10|
 2513|           202|             11|
 2514|           202|             12|
 2515|           202|             13|
 2516|           202|             14|
10884|           127|             10|
10885|           127|             19|
 8093|             3|              5|
10886|           127|             29|
10574|           211|              2|
 2517|           202|             15|
 2269|           184|              2|
 3631|           223|              2|
 8094|             3|              7|
10575|           211|              3|
 2769|            48|              2|
 1975|           167|              2|
 3873|           274|              2|
 2540|            59|              2|
 8097|             3|             10|
 8098|             3|             11|
 8099|             3|             12|
 3910|           284|              2|
 3911|           284|             14|
 2309|            86|              2|
 2310|            86|              3|
 8100|             3|             13|
 9627|           375|              2|
 1616|           141|              2|
 9628|           375|             29|
 8101|             3|             14|
 1790|           147|              1|
 1791|           147|              2|
 1792|           147|              3|
 8102|             3|             15|
 1796|           147|              7|
10576|           211|             12|
 1799|           147|             10|
 1800|           147|             11|
 1801|           147|             12|
 1802|           147|             13|
 1803|           147|             14|
 1804|           147|             15|
 3448|           101|              2|
10577|           211|             15|
 2100|            73|              2|
 1910|           157|              2|
 8103|             3|             29|
10578|           211|             29|
 4856|           220|             22|
 4863|            27|             22|
 2317|           188|              2|
 2318|           188|              3|
 2320|           188|              7|
 2322|           188|             10|
 2323|           188|             11|
 2324|           188|             15|
 2326|           189|              2|
 2327|           189|             15|
 8484|           303|              2|
 8485|           303|             29|
10642|           419|              2|
10643|           419|              3|
10644|           419|             12|
 2108|            65|              2|
 4868|           326|             23|
10645|           419|             29|
 2059|           103|              2|
 3934|           287|              2|
 2955|           154|             10|
 2956|           154|             11|
 2957|           154|             12|
 2958|           154|             15|
 3001|            77|              2|
 2273|            39|              2|
 2279|           135|              2|
 7034|           287|              5|
 3009|           217|              2|
 3010|           217|              3|
 3011|           217|              7|
 4444|            27|              2|
 5139|           373|              2|
 3330|           240|              2|
 3332|           240|              7|
 9493|           503|              2|
 9494|           503|             10|
 9495|           503|             29|
 8813|            37|              2|
 8814|            37|             29|
10672|           520|             29|
10714|            58|              2|
10715|            58|             29|
10089|           225|              2|
 3334|           240|             11|
 3335|           240|             12|
 2360|           192|              2|
 2361|           173|              2|
 3356|           242|              2|
 2798|           216|              1|
 2799|           216|              2|
 2800|           216|              3|
 2804|           216|              7|
10090|           225|             29|
 2807|           216|             10|
 2808|           216|             11|
 2809|           216|             12|
 3950|           281|              2|
 3954|           273|              2|
 2810|           216|             13|
 2811|           216|             14|
 2812|           216|             15|
 3537|           252|              2|
 3538|           252|              7|
 3541|           252|             10|
 3542|           252|             11|
 3543|           252|             12|
10929|           317|              1|
 4446|            27|             10|
 4447|            27|             11|
10930|           317|              2|
 3642|           228|              2|
 3643|           228|              3|
 3644|           228|              7|
10931|           317|              3|
 3646|           228|             10|
 8699|           369|              5|
10932|           317|              4|
 8701|           369|             15|
 5188|           231|              2|
10933|           317|              5|
 5190|           231|             10|
 5191|           231|             22|
 8702|           369|             17|
 8703|           369|             29|
 3315|           238|              2|
 3316|           238|              3|
 6884|           437|              2|
 3920|           285|              2|
10934|           317|              7|
10935|           317|              8|
 9287|            25|              1|
 9288|            25|              2|
 9289|            25|              3|
 9290|            25|              4|
 9291|            25|              5|
10936|           317|              9|
 3319|           238|             12|
 3320|           238|             15|
 3939|           290|              2|
 3940|           290|             14|
 3348|           241|              2|
 6885|           437|             29|
 3350|           241|              7|
 3044|           199|              2|
10937|           317|             10|
 3352|           241|             11|
 3353|           241|             12|
 2940|           153|              2|
 2941|           153|              3|
10938|           317|             11|
 3362|           244|              2|
10939|           317|             12|
 3248|           209|              2|
 2671|           205|              2|
 3104|            46|              7|
 3107|            46|             10|
 3108|            46|             11|
 3109|            46|             12|
 3110|            46|             13|
 3111|            46|             14|
 3112|            46|             15|
 3113|            46|             16|
 3254|           234|              2|
 3255|           234|             14|
 3057|            54|              2|
 3058|            54|              3|
 8386|           475|              2|
 7036|            52|              5|
10380|           411|              2|
10381|           411|              5|
 3064|            54|             10|
 3065|            54|             11|
 3066|            54|             12|
 3067|            54|             15|
 7037|           195|              5|
 9292|            25|              6|
10382|           411|             29|
 9293|            25|              7|
10390|           191|              2|
 9296|            25|             10|
 9297|            25|             11|
 9298|            25|             12|
 9299|            25|             13|
 9300|            25|             14|
 9301|            25|             15|
 9302|            25|             16|
10391|           191|              7|
10392|           191|              8|
10393|           191|              9|
10394|           191|             15|
10395|           191|             29|
 7875|           237|              2|
 7876|           237|              3|
 7040|             4|              5|
 7878|           237|             12|
 4082|           298|              2|
 4083|           299|              2|
 4084|           300|              2|
 3288|           224|              2|
 7879|           237|             15|
 3290|           224|              7|
 3292|           224|             15|
 3662|           257|              2|
 3680|           195|              1|
 3681|           195|              2|
 3682|           195|              3|
 3686|           195|              7|
 8815|           349|             17|
 8816|           349|             29|
10271|           510|              2|
10272|           510|              3|
10273|           510|              7|
 3689|           195|             10|
 3690|           195|             11|
 3691|           195|             12|
 3692|           195|             13|
 3693|           195|             14|
 3694|           195|             15|
10274|           510|              8|

CREATE TABLE public.system_user_program (
    id int4 NOT NULL,
    system_user_id int4 NOT NULL,
    system_program_id int4 NOT NULL,
    CONSTRAINT system_user_program_pkey PRIMARY KEY (id),
    CONSTRAINT system_user_program_system_program_id_fkey FOREIGN KEY (system_program_id) REFERENCES public.system_program(id),
    CONSTRAINT system_user_program_system_user_id_fkey FOREIGN KEY (system_user_id) REFERENCES public.system_users(id)
);
CREATE INDEX sys_user_program_program_idx ON public.system_user_program USING btree (system_program_id);
CREATE INDEX sys_user_program_user_idx ON public.system_user_program USING btree (system_user_id);
id    |system_user_id|system_program_id|
------+--------------+-----------------+
 82749|           197|               79|
 82750|           197|               91|
 82751|           197|              101|
 82752|           197|              447|
  2342|            49|               45|
  2343|            49|               47|
  2344|            49|               48|
  2345|            49|               49|
  2346|            49|               50|
 84884|           392|               70|
 59636|           284|               70|
 59637|           284|               71|
 59638|           284|               72|
 59639|           284|               73|
 59640|           284|               78|
 59641|           284|               79|
 59642|           284|               86|
  2347|            49|               51|
  2348|            49|               52|
  2349|            49|              103|
  2350|            49|              104|
  2351|            49|              106|
 84885|           392|               71|
  2352|            49|              107|
  2353|            49|              108|
  2354|            49|              109|
  2355|            49|              110|
  2356|            49|              112|
  2357|            49|              113|
  2358|            49|              114|
  2359|            49|              119|
  2360|            49|              123|
  2361|            49|              125|
  2362|            49|              139|
  2363|            49|              140|
  2364|            49|              141|
  2365|            49|              142|
  2366|            49|              143|
  2367|            49|              144|
  2368|            49|              187|
  2369|            49|              188|
  2370|            49|              212|
  2371|            49|              213|
  2372|            49|              214|
  2373|            49|              216|
  2374|            49|              217|
  2375|            49|              220|
  2376|            49|              225|
  2377|            49|              227|
 35137|           184|              634|
 84886|           392|               79|
 84887|           392|               91|
 84888|           392|              288|
 35144|            52|              634|
 84889|           392|              345|
  2378|            49|              231|
  2379|            49|              232|
  2380|            49|              233|
  2381|            49|              234|
  2382|            49|              235|
 35145|            80|              634|
 84890|           392|              683|
 84891|           392|              572|
 35148|           170|              634|
 35150|           192|              634|
 84892|           392|              634|
 91842|           428|               71|
 91843|           428|               79|
 91844|           428|               91|
 91845|           428|              244|
 91846|           428|              683|
 91847|           428|              572|
 91848|           428|              634|
 33728|           196|              683|
 33729|           196|               71|
 33730|           196|               79|
 33731|           196|               90|
 33732|           196|               91|
 33733|           196|              101|
 33734|           196|              383|
 33735|           196|              490|
  2383|            49|              236|
  2384|            49|              237|
  2385|            49|              238|
  2386|            49|              243|
 92684|           434|               71|
 35160|           108|              634|
 35164|           135|              634|
 35165|            65|              634|
 59643|           284|               87|
 35167|           124|              634|
 35168|           173|              634|
 35169|           149|              634|
 35170|            73|              634|
 35171|           103|              634|
 59644|           284|               91|
 59645|           284|               92|
140817|            26|               70|
140818|            26|               71|
140819|            26|               72|
 59646|           284|               96|
 27692|           161|               71|
 27693|           161|               79|
 27694|           161|               91|
 27717|           124|               71|
 27718|           124|               79|
 27719|           124|               91|
 27720|           124|              379|
 27721|           124|              380|
140820|            26|               77|
 35174|           121|              634|
 35176|           161|              634|
 35179|           188|              634|
  2387|            49|              245|
  2388|            49|              268|
  2389|            49|              271|
  2390|            49|              272|
  2391|            49|              273|
 35183|           147|              634|
 27858|           104|               71|
 27859|           104|               79|
 27860|           104|               91|
 27861|           104|              485|
 27862|           104|              486|
 27863|           104|              503|
 27864|           104|              504|
  2392|            49|              274|
  2393|            49|              276|
  2394|            49|              277|
  2395|            49|              279|
  2396|            49|              281|
  2397|            49|              283|
  2398|            49|              284|
  2399|            49|              285|
  2400|            49|              289|
  2401|            49|              330|
  2402|            49|              331|
140821|            26|               78|
140822|            26|               79|
140823|            26|               80|
140824|            26|               91|
140825|            26|               96|
140826|            26|               98|
140827|            26|              101|
140828|            26|              113|
 27691|           161|              683|
 27716|           124|              683|
 27857|           104|              683|
140829|            26|              118|
 35185|           196|              634|
 54591|           257|               71|
 54592|           257|               79|
 35190|            72|              634|
140830|            26|              120|
 35194|            81|              634|
 54593|           257|               91|
140831|            26|              121|
140832|            26|              123|
140833|            26|              125|
140834|            26|              126|
140835|            26|              127|
140836|            26|              128|
140837|            26|              130|
140838|            26|              131|
140839|            26|              132|
140840|            26|              133|
140841|            26|              134|
140842|            26|              135|
140843|            26|              136|
140844|            26|              138|
140845|            26|              184|
140846|            26|              193|
140847|            26|              206|
 54594|           257|              683|
 54595|           257|              634|
 59647|           284|               99|
 59648|           284|              101|
 59649|           284|              102|
 59650|           284|              287|
 59651|           284|              333|
 35207|           104|              634|
 59652|           284|              345|
140848|            26|              212|
 35212|            91|              634|
 35213|           151|              634|
 59653|           284|              346|
 59654|           284|              355|
 59655|           284|              358|
 59656|           284|              359|
140849|            26|              213|
140850|            26|              218|
140851|            26|              245|
140852|            26|              288|
140853|            26|              292|
140854|            26|              313|
140855|            26|              323|
140856|            26|              324|
140857|            26|              348|
140858|            26|              363|
140859|            26|              379|
140860|            26|              380|
 59657|           284|              360|
 59658|           284|              361|
140861|            26|              382|
 59659|           284|              535|
 59660|           284|              663|
140862|            26|              383|
 35214|            74|              634|
 44153|           217|               47|
140863|            26|              401|
140864|            26|              427|
140865|            26|              452|
 35218|             4|              634|
 35220|           138|              634|
 35221|           181|              634|
140866|            26|              503|
 44154|           217|               48|
 44155|           217|               61|
 35230|           174|              634|
 44156|           217|              220|
140867|            26|              504|
140868|            26|              506|
 35235|            86|              634|
 35236|            39|              634|
 44157|           217|              231|
 35241|           175|              634|
140869|            26|              683|
140870|            26|              576|
 35244|            50|              634|
 44158|           217|              232|
 35246|           167|              634|
140871|            26|              592|
 44159|           217|              233|
 44160|           217|              234|
 44161|           217|              290|
 44162|           217|              299|
 44163|           217|              311|
 44164|           217|              320|
 44165|           217|              322|
 44166|           217|              329|
 44167|           217|              334|
 44168|           217|              350|
 44169|           217|              438|
 44170|           217|              525|
 44171|           217|              526|
 44172|           217|              538|
 44173|           217|              549|
 44174|           217|              634|
140872|            26|              593|
140873|            26|              594|
140874|            26|              606|
140875|            26|              655|
140876|            26|              656|
140877|            26|              793|
140878|            26|              794|
140879|            26|              634|
140880|            26|              770|
140881|            26|              823|
140882|            26|              847|
140883|            26|              882|
140884|            26|              537|
140885|            26|              919|
140886|            26|              929|
140887|            26|              939|
140888|            26|              942|
140889|            26|              954|
140890|            26|              961|
140891|            26|              970|
140892|            26|              976|
140893|            26|              978|
140894|            26|              982|
140895|            26|              983|
 35257|            75|              634|
 59661|           284|              672|
140896|            26|              991|
140897|            26|              994|
140898|            26|              996|
140899|            26|              993|
140900|            26|              995|
140901|            26|              998|
140902|            26|              999|
140903|            26|             1018|
 59662|           284|              683|
 59663|           284|              688|
 59664|           284|              572|
 35266|           157|              634|
 59665|           284|              634|
 59666|           284|              766|
 35269|             7|              634|
 60286|           290|               70|
 60287|           290|               71|
 60288|           290|               72|
 60289|           290|               73|
 60290|           290|               78|
 35277|           164|              634|
 60291|           290|               79|
 29169|           181|               71|
 35286|           141|              634|
 91849|           431|               71|
 35289|            49|              634|
140904|            26|             1019|
140905|            26|             1020|
 91850|           431|               79|
140906|            26|             1024|
 91851|           431|               91|
 29170|           181|               79|
 29171|           181|               91|
 29172|           181|              485|
 29173|           181|              486|
 29174|           181|              503|
 29175|           181|              504|
 91852|           431|              244|
 91853|           431|              683|
 91854|           431|              572|
 91855|           431|              634|
 92685|           434|               79|
 92686|           434|               91|
140907|            26|             1144|
140908|            26|             1159|
140909|            26|             1160|
140910|            26|             1178|
 48862|           241|               71|
140911|            26|             1179|
140912|            26|             1122|
140913|            26|             1129|
 48863|           241|               79|
 43515|           154|               70|
 43516|           154|               71|
 43517|           154|               72|
 43518|           154|               79|
 43519|           154|               90|
 43520|           154|               91|
 43521|           154|              101|
 43522|           154|              192|
 43523|           154|              193|
 43524|           154|              205|
 43525|           154|              206|
 43526|           154|              362|
 43527|           154|              363|
 43528|           154|              379|
 43529|           154|              380|
 43530|           154|              383|
 43531|           154|              485|
 43532|           154|              486|
 43533|           154|              490|
 43534|           154|              503|
 43535|           154|              504|
 43536|           154|              514|
 43537|           154|              592|
 43538|           154|              653|
 43539|           154|              634|
 48864|           241|               91|
 48865|           241|              103|
 48866|           241|              104|
 48867|           241|              105|
 48868|           241|              106|
 48869|           241|              107|
 48870|           241|              108|
 48871|           241|              109|
 48872|           241|              113|
 48873|           241|              116|
 48874|           241|              117|
 48875|           241|              118|
 48876|           241|              119|
 48877|           241|              120|
 48878|           241|              125|
 48879|           241|              128|
 48880|           241|              131|
 48881|           241|              133|
 48882|           241|              138|
 48883|           241|              212|
 48884|           241|              213|
 48885|           241|              222|
 48886|           241|              223|
 48887|           241|              224|
 48888|           241|              225|
 48889|           241|              227|
 48890|           241|              228|
 48891|           241|              229|
 48892|           241|              230|
 48893|           241|              252|
 48894|           241|              253|
 48895|           241|              264|
 48896|           241|              265|
 48897|           241|              271|
 48898|           241|              275|
 48899|           241|              284|
 48900|           241|              308|
 48901|           241|              309|
 48902|           241|              323|
 48903|           241|              324|
 48904|           241|              327|
 48905|           241|              332|
 48906|           241|              355|
 58658|           272|              768|
 58659|           272|              788|
 27710|           138|               71|
 27711|           138|               79|
 27712|           138|               91|
 27713|           138|              101|

CREATE TABLE public.system_user_unit (
     id int4 NOT NULL,
     system_user_id int4 NOT NULL,
     system_unit_id int4 NOT NULL,
     CONSTRAINT system_user_unit_pkey PRIMARY KEY (id),
     CONSTRAINT system_user_unit_system_unit_id_fkey FOREIGN KEY (system_unit_id) REFERENCES public.system_unit(id),
     CONSTRAINT system_user_unit_system_user_id_fkey FOREIGN KEY (system_user_id) REFERENCES public.system_users(id)
);

id  |system_user_id|system_unit_id|
----+--------------+--------------+
   7|             7|             1|
1469|           170|             9|
1474|           161|             9|
1480|           138|             1|
1481|           124|             4|
7107|           211|             1|
 174|            49|             1|
 175|            49|             2|
 176|            49|             3|
 177|            49|             4|
 178|            49|             5|
 179|            49|             6|
 180|            49|             7|
 181|            49|             8|
 182|            49|             9|
 183|            49|            10|
 184|            49|            11|
 185|            49|            15|
4132|           376|             1|
4133|           376|             2|
4134|           376|             3|
2359|           182|             7|
2360|           182|            20|
4135|           376|             4|
4136|           376|             5|
1499|           104|             5|
1343|           149|             1|
4137|           376|             6|
4138|           376|             7|
4139|           376|             8|
 443|            80|             1|
4140|           376|             9|
4141|           376|            11|
4142|           376|            18|
4143|           376|            20|
4144|           376|            21|
1542|            73|             4|
5497|           468|             1|
 539|            81|             1|
1557|            50|             1|
2641|           209|             1|
2642|           209|             4|
3220|           284|             4|
3157|           185|             2|
3168|           270|             1|
3229|           287|            20|
2420|            77|             1|
2422|           217|             1|
3177|           272|             1|
3236|           281|             1|
3240|           273|             1|
3011|           258|             4|
6794|           225|             9|
6795|           225|            10|
5892|            36|             1|
1376|           157|             1|
2197|           216|             1|
2198|           216|             2|
2199|           216|             3|
2200|           216|             4|
2201|           216|             5|
2202|           216|             6|
1413|           151|             1|
3055|           268|             1|
2203|           216|             7|
2204|           216|             8|
2205|           216|             9|
2206|           216|            10|
2207|           216|            11|
2208|           216|            12|
2209|           216|            13|
2210|           216|            15|
2211|           216|            16|
2212|           216|            18|
3056|           246|             9|
1662|           184|             1|
1445|           164|             1|
1463|           175|             5|
2827|           230|             1|
2936|           228|             1|
1494|           121|             4|
3224|           285|             8|
1503|           108|             3|
1506|            91|             2|
2937|           228|             2|
2938|           228|             3|
2939|           228|             4|
2940|           228|             5|
2941|           228|             6|
2942|           228|             7|
2943|           228|             8|
4039|           367|             1|
4045|           370|             3|
3675|           180|             4|
2944|           228|            10|
2945|           228|            11|
1593|           181|             7|
3232|           290|             4|
5621|           454|             2|
4005|           350|             1|
5984|           349|             1|
5987|           482|             1|
4338|           392|             1|
5723|           446|             1|
6578|           375|             4|
4017|           354|             2|
1057|           141|             1|
1170|             4|             1|
2371|           154|             1|
1171|             4|             2|
1172|             4|             3|
1173|             4|             4|
1174|             4|             5|
1175|             4|             6|
1176|             4|             7|
1177|             4|             8|
4438|           379|             4|
1178|             4|             9|
4027|            87|             3|
1179|             4|            10|
1180|             4|            11|
1181|             4|            12|
1182|             4|            13|
1183|             4|            15|
1184|             4|            16|
1185|             4|            18|
1543|            74|             4|
1552|            65|             1|
1464|           174|             4|
7511|            23|             1|
2951|           257|             4|
2972|           195|             1|
1850|           196|             1|
1665|            39|             1|
1668|           135|             1|
2973|           195|             2|
2974|           195|             3|
2975|           195|             4|
2976|           195|             5|
2119|           205|             1|
5498|           469|             1|
7743|           527|             7|
7567|           443|             1|
1731|           187|             1|
1732|           187|             2|
1733|           187|             3|
1734|           187|             4|
1735|           187|             5|
1736|           187|             6|
1737|           187|             7|
1738|           187|             8|
1739|           187|             9|
1740|           187|            10|
1741|           187|            11|
1742|           187|            12|
1743|           187|            13|
1744|           187|            15|
1745|           187|            16|
1746|           187|            17|
1747|           187|            18|
1748|           187|            19|
1749|           187|            20|
1750|           187|            21|
7804|           534|             4|
1763|           189|             1|
2664|           240|             1|
2670|           242|             1|
2672|           244|             1|
7857|           301|             9|
3895|           342|             6|
3896|           342|            15|
2977|           195|             6|
2978|           195|             7|
2979|           195|             8|
2980|           195|             9|
2981|           195|            10|
2982|           195|            11|
2983|           195|            12|
2984|           195|            13|
2985|           195|            15|
2986|           195|            16|
2987|           195|            17|
2988|           195|            18|
2989|           195|            19|
2990|           195|            20|
1278|           147|             1|
1279|           147|             2|
1280|           147|             3|
1281|           147|             4|
1282|           147|             5|
3897|           325|             1|
4116|           372|             2|
4118|           374|             1|
4119|           374|             2|
4120|           374|             3|
4121|           374|             4|
4122|           374|             5|
4123|           374|             6|
4124|           374|             7|
4125|           374|             8|
4126|           374|            10|
4127|           374|            11|
1283|           147|             6|
1284|           147|             7|
1285|           147|             8|
1286|           147|             9|
1287|           147|            10|
1288|           147|            11|
1289|           147|            12|
1290|           147|            13|
1291|           147|            15|
1292|           147|            16|
3995|           346|             1|
3237|           280|             1|
1712|            86|             3|
1419|           167|            10|
7866|           532|             1|
2991|           195|            21|
7877|           253|             3|
2464|           199|            17|
1498|           103|            15|
2177|           213|             1|
4795|           435|             1|
4796|           435|             2|
4014|           352|             2|
4024|           359|             3|
4031|           364|             3|
2929|           254|            17|
4085|           371|             1|
4797|           435|             3|
4798|           435|             4|
4799|           435|             5|
4800|           435|             6|
4801|           435|             7|
4802|           435|             8|
4803|           435|            10|
4804|           435|            11|
4807|           437|             4|
4086|           371|             2|
1293|           147|            17|
1294|           147|            18|
5392|            92|             1|
4347|           393|             7|
2644|           234|             8|
2763|           101|             9|
4349|           310|             1|
1541|            72|             4|
1544|            75|             1|
5393|            92|             2|
1559|            52|             1|
4350|           391|             2|
2764|           106|             7|
5394|            92|             3|
2182|           214|             3|
2778|            45|             1|
2653|           224|             1|
2660|           238|             1|
1751|           188|             1|
1752|           188|             2|
1753|           188|             3|
1754|           188|             4|
1755|           188|             5|
1756|           188|             6|
1757|           188|             7|
1758|           188|             8|
1759|           188|             9|
1760|           188|            10|
1761|           188|            15|
3178|           275|             1|
2471|            54|             1|
2668|           241|             1|
1948|           203|             1|
1949|           203|             2|
1950|           203|             3|
1951|           203|             4|
1952|           203|             5|
1775|           192|             4|
1776|           173|             4|
1953|           203|             6|
1954|           203|             7|
1955|           203|             8|
1956|           203|             9|
1957|           203|            10|
1958|           203|            11|
1959|           203|            12|
1960|           203|            13|
1961|           203|            15|
1962|           203|            16|
1963|           203|            17|
1964|           203|            18|
1908|           202|             1|
1909|           202|             2|
1910|           202|             3|
1911|           202|             4|
1912|           202|             5|
1913|           202|             6|
1914|           202|             7|
1915|           202|             8|
1916|           202|             9|
1917|           202|            10|
1918|           202|            11|
1919|           202|            12|
1920|           202|            13|
1921|           202|            15|
1922|           202|            16|
1923|           202|            17|
1924|           202|            18|
1925|           202|            19|
1926|           202|            20|
1927|           202|            21|
1965|           203|            19|
1966|           203|            20|
1967|           203|            21|
1970|            59|             1|
2368|           153|             7|
3238|           279|             1|
7235|           520|             4|
4360|           401|             7|
2176|           212|             1|
2178|            48|             1|
5395|            92|             4|
4361|           401|             9|
4362|           401|            10|
4363|           401|            20|
3647|           332|             9|
2503|            46|             1|
2504|            46|             2|
2505|            46|             3|
2506|            46|             4|
2507|            46|             5|
2508|            46|             6|
3650|           220|             1|
4087|           371|             3|
4088|           371|             4|
4089|           371|             5|
4090|           371|             6|
3747|           335|             1|
2509|            46|             7|
2510|            46|             8|
2511|            46|             9|
2512|            46|            10|
2513|            46|            11|
2514|            46|            12|
2515|            46|            13|
2516|            46|            15|
2517|            46|            16|
2518|            46|            17|
2519|            46|            18|
2520|            46|            19|
2521|            46|            20|
2522|            46|            21|
4091|           371|             7|
4092|           371|             8|
4093|           371|            10|
4094|           371|            11|
4368|           227|             1|
4369|           227|             2|
4370|           227|             3|
4371|           227|             4|
4372|           227|             5|
4373|           227|             6|
6000|           412|             1|
3179|           276|             1|
5396|            92|             5|
5397|            92|             6|
5398|            92|             7|
5399|            92|             8|
5400|            92|             9|
5401|            92|            10|
5402|            92|            11|
5403|            92|            12|
5404|            92|            13|
5405|            92|            15|
5406|            92|            16|
5407|            92|            17|
5408|            92|            18|
5409|            92|            19|
7748|           530|             1|
4572|           414|            17|
4739|           432|             7|
6439|           315|             1|
6440|           315|             2|
6441|           315|             3|
5410|            92|            20|
5411|            92|            21|
2335|           204|            20|
5412|            92|            22|
5492|           237|             1|
2930|           223|             7|
6442|           315|             4|
3364|           298|             1|
3365|           299|             1|
3366|           300|             1|
5781|           475|             9|
3404|           309|             1|
2828|           252|             8|
3239|           278|             1|
3286|           292|             2|
5575|           130|             1|
3291|           277|             1|
3292|           271|             1|
3294|           293|             2|
3295|           294|             2|
3153|           159|             9|
3154|           256|             2|
3996|           347|             1|
6443|           315|             5|
2669|           166|             1|
2671|           243|             1|
3180|           274|             1|
4032|            90|             3|
4040|           368|             1|
4374|           227|             7|
4375|           227|             8|
4376|           227|             9|
5622|            21|             1|
4377|           227|            10|
4378|           227|            11|
4379|           227|            12|
4380|           227|            13|
2807|           179|             6|
2808|           179|            15|
3014|           259|             1|
5672|           155|             4|
4381|           227|            15|
3016|           261|             1|
4382|           227|            16|
3039|           262|             1|
4383|           227|            17|
3045|           263|             1|
3051|           226|            21|
7283|           171|             8|
4384|           227|            18|
7644|           283|             1|
7645|           283|             2|
6444|           315|             6|
6445|           315|             7|
6446|           315|             8|
7795|           356|             2|
6447|           315|            10|
3401|           306|             5|
4385|           227|            19|
3403|           304|             8|
3405|           267|             1|
3408|           311|             1|
3409|           269|             1|
4386|           227|            20|
3414|           313|             1|
6448|           315|            11|
7853|           536|             1|
7858|           395|             7|
7863|           200|            17|
6963|           245|             1|
6964|           245|             2|
4387|           227|            21|
6965|           245|             3|
6966|           245|             4|
6967|           245|             5|
4388|           227|            22|
6968|           245|             6|
6969|           245|             7|
6970|           245|             8|
6971|           245|            10|
5886|           480|             4|
6972|           245|            11|
7917|           512|             1|
6449|           315|            17|
4354|           399|             1|
4181|           231|             1|
3652|            27|             1|
4029|           362|             3|
4117|           373|             2|
6450|           315|            18|
6451|           315|            19|
6452|           315|            20|
7421|           127|             2|
8081|           544|             1|
6194|           493|             3|
8088|           125|             1|
8126|           547|             1|
8211|           548|             1|
8236|           550|             3|
8244|           552|             1|
8245|           552|             2|
8246|           552|             3|
8247|           552|             4|
8248|           552|             5|
8249|           552|             6|
8250|           552|             7|
8251|           552|             8|
8252|           552|            10|
8253|           552|            11|
8254|           553|             3|
8330|           558|            23|
6453|           503|             1|
6048|           488|             1|
6049|           488|            16|
6490|           388|             1|
3947|           291|             1|
3948|           291|             2|
3949|           291|             3|
3950|           291|             4|
3951|           291|             5|
3952|           291|             6|
3953|           291|             7|
3954|           291|             8|
3955|           291|             9|
3956|           291|            10|
3957|           291|            11|
3958|           291|            12|
3959|           291|            13|
3960|           291|            15|
3961|           291|            16|
3962|           291|            17|
3963|           291|            18|
3964|           291|            19|
3965|           291|            20|
3966|           291|            21|
3967|           291|            22|
3969|           343|             5|
6636|           505|             1|
5493|           463|             1|
5499|           470|             5|
5504|           471|             2|
6637|           506|             1|
3477|             2|             4|
6918|            34|             1|
6919|            34|             2|
6920|            34|             3|
7284|            58|             1|
4794|           434|             1|
6984|           191|             1|
5898|           369|             1|
4520|           249|             4|
4396|           402|             3|
4234|           197|             2|
3433|            35|             1|
3434|            35|             2|
3435|            35|             3|
3436|            35|             4|
3437|            35|             5|
3438|            35|             6|
3439|            35|             7|
3440|            35|             8|
3441|            35|             9|
3442|            35|            11|
3443|            35|            18|
3444|            35|            20|
3445|            35|            21|
3556|           319|             7|
5899|           369|             2|
5900|           369|             3|
5901|           369|             4|
5902|           369|             5|
5903|           369|             6|
4238|           380|             1|
4239|           380|             2|
4240|           380|             3|
4241|           380|             4|
4242|           380|             5|
4243|           380|             6|
4244|           380|             7|
4245|           380|             8|
4246|           380|             9|
5904|           369|             7|
5905|           369|             8|
4247|           380|            11|
4248|           380|            18|
4249|           380|            20|
5906|           369|             9|
3997|           348|             1|
4210|           378|             1|
3898|           172|             5|
4250|           380|            21|
3746|           334|             1|
5907|           369|            10|
5908|           369|            11|
5909|           369|            15|
5910|           369|            17|
5911|           369|            20|
5912|           369|            21|
3601|           323|             1|
3602|           322|             1|
3603|           324|             1|
3605|           326|             1|
3606|           327|             1|
3607|           328|             1|
3608|           331|             2|
7017|           128|             1|
5938|           366|             1|
3925|           344|             1|
4273|           382|             2|
3769|           337|             2|
3775|           339|            15|
7868|           483|             1|
7874|           538|             1|
7918|           452|             1|
6454|           495|             1|
6455|           495|             4|
6050|           459|             1|
6051|           459|            16|
7454|           302|             2|
6479|           330|             1|
4461|           404|             1|
4462|           404|             2|
5845|           478|             1|
4463|           404|             3|
4841|           438|             1|
5494|           464|             1|
6921|           472|             1|
5202|           387|             1|
4464|           404|             4|
4465|           404|             5|
4466|           404|             6|
4467|           404|             7|
4468|           404|             8|
4306|           308|             1|
4469|           404|             9|
4470|           404|            10|
4308|           115|             4|
6163|            12|             1|
6985|           513|             1|
6195|           494|             3|
6764|            96|             1|
6765|            96|             2|
6766|            96|             3|
6767|            96|             4|
6768|            96|             5|
4471|           404|            11|
4472|           404|            15|
4473|           404|            16|
4474|           404|            17|
4475|           404|            18|
4476|           404|            19|
4477|           404|            20|
4478|           404|            21|
4479|           404|            22|
4741|           422|             1|
6769|            96|             6|
4771|           428|             7|
6770|            96|             7|
6771|            96|             8|
6772|            96|             9|
6773|            96|            10|
6774|            96|            11|
6775|            96|            15|
6776|            96|            16|
6777|            96|            17|
4492|           405|             1|
6778|            96|            18|
6779|            96|            19|
6780|            96|            20|
6781|            96|            21|
6782|            96|            22|
6238|           491|             7|
5811|           303|             4|
4618|           416|             4|
5615|           406|             2|
5815|           476|             1|
5623|            47|             1|
4622|           418|             4|
5624|            47|             2|
5625|            47|             3|
5626|            47|             4|
5627|            47|             5|
5628|            47|             6|
5629|            47|             7|
5630|            47|             8|
5631|            47|            10|
5632|            47|            11|
5036|           132|             1|
5673|           377|             1|
5674|           377|             2|
5675|           377|             3|
5676|           377|             4|
5677|           377|             5|
5678|           377|             6|
5679|           377|             7|
5680|           377|             8|
5681|           377|             9|
5682|           377|            11|
5683|           377|            18|
5684|           377|            20|
5685|           377|            21|
7649|           381|             1|
4645|           112|             3|
4651|            85|             2|
5105|           451|             1|
7919|           509|             4|
7943|           539|             4|
7947|           540|             1|
7978|            22|             1|
7979|            22|            11|
8050|           133|             4|
4695|           400|             2|
4700|           415|             6|
4701|           415|            15|
4702|           424|             7|
8301|           524|             4|
8304|           487|             1|
8305|           487|             2|
8306|           487|             4|
8307|           487|            16|
8308|           487|            18|
4704|           426|             7|
4772|           431|             7|
4705|           427|             7|
5495|           466|             1|
4708|           430|            15|
6055|           489|             2|
6915|           510|             2|
7647|           333|             1|
4736|           160|             3|
6284|            25|             1|
6285|            25|             2|
6286|            25|             3|
6287|            25|             4|
6288|            25|             5|
6289|            25|             6|
6290|            25|             7|
6291|            25|             8|
6190|            31|             1|
4805|           436|             1|
6192|           492|             1|
6292|            25|             9|
6293|            25|            10|
6638|           507|            20|
6294|            25|            11|
6295|            25|            12|
6296|            25|            13|
6297|            25|            15|
6298|            25|            16|
6299|            25|            17|
6300|            25|            18|
6301|            25|            20|
6302|            25|            21|
6303|            25|            23|
5633|           107|             7|
5639|             3|             1|
5640|             3|             2|
4910|           444|             1|
4911|           444|             2|
4912|           444|             3|
4913|           444|             4|
4914|           444|             5|
4915|           444|             6|
4916|           444|             7|
4917|           444|             8|
4918|           444|             9|
4919|           444|            10|
4920|           444|            11|
4921|           444|            12|
4922|           444|            13|
4923|           444|            15|
4924|           444|            16|
4925|           444|            17|
4926|           444|            18|
4927|           444|            19|
4928|           444|            20|
4929|           444|            21|
4930|           444|            22|
5032|           448|             2|
5641|             3|             3|
5642|             3|             4|
5104|           450|             1|
7019|           222|             1|
5324|            70|             4|
5643|             3|             5|
5644|             3|             6|
5645|             3|             7|
5646|             3|             8|
5647|             3|             9|
5648|             3|            10|
5649|             3|            11|
5650|             3|            12|
5651|             3|            13|
5652|             3|            15|
5653|             3|            16|
5654|             3|            18|
5668|           193|             1|
6371|           501|             1|
6554|            18|             1|
6555|            18|             2|
6556|            18|             3|
6557|            18|             4|
6558|            18|             5|
6559|            18|             6|
6560|            18|             7|
6561|            18|             8|
6562|            18|             9|
6563|            18|            10|
6564|            18|            11|
6565|            18|            12|
6566|            18|            15|
6567|            18|            16|
6568|            18|            17|
6569|            18|            19|
6570|            18|            20|
6571|            18|            21|
6572|            18|            22|
7457|           317|             1|
5733|           229|            20|
7458|           317|             2|
7459|           317|             3|
7460|           317|             4|
7461|           317|             5|
7462|           317|             6|
6928|           511|             3|
7463|           317|             7|
7464|           317|             8|
7144|           518|             1|
7465|           317|             9|
7466|           317|            10|
7467|           317|            11|
7468|           317|            12|
6978|           403|             8|
5014|           442|             1|
5015|           442|             2|
6056|           490|             2|
7469|           317|            15|
5016|           442|             3|
5017|           442|             4|
5018|           442|             5|
5019|           442|             6|
5020|           442|             8|
5021|           442|             9|
5022|           442|            10|
5023|           442|            11|
5024|           442|            12|
5025|           442|            13|
5026|           442|            15|
5027|           442|            17|
5028|           442|            18|
5029|           442|            19|
5030|           442|            20|
5031|           442|            21|
7470|           317|            16|
7471|           317|            17|
5496|           467|             1|
7472|           317|            18|
7473|           317|            19|
7474|           317|            20|
7475|           317|            21|
7476|           317|            22|
5891|           394|             4|
7477|           317|            23|
5586|           118|             1|
5587|           118|             2|
5588|           118|             3|
5589|           118|             4|
5590|           118|             5|
5591|           118|             6|
5592|           118|             7|
5593|           118|             8|
5594|           118|             9|
5595|           118|            10|
5596|           118|            11|
5597|           118|            15|
5598|           118|            16|
5599|           118|            17|
5600|           118|            18|
5601|           118|            19|
5602|           118|            20|
5603|           118|            21|
5604|           118|            22|
6165|            38|             1|
6166|            38|             2|
5620|           355|             2|
6167|            38|             3|
5302|            82|             1|
6495|           504|             1|
6496|           504|             2|
6497|           504|             3|
6498|           504|             4|
7066|           286|             7|
5303|            82|            12|
7067|           286|            20|
6168|            38|             4|
6169|            38|             5|
6170|            38|             6|
6171|            38|             7|
6172|            38|             8|
6173|            38|             9|
6174|            38|            10|
6175|            38|            11|
6176|            38|            12|
6177|            38|            13|
6178|            38|            15|
6179|            38|            16|
6180|            38|            17|
6181|            38|            18|
6182|            38|            19|
6183|            38|            20|
6184|            38|            21|
6185|            38|            22|
5669|           260|             1|
5816|           477|             1|
5366|           453|             2|
5451|           119|             1|
5698|            28|             1|
5699|            28|             2|
5700|            28|             3|
5701|            28|             4|
5702|            28|             5|
6499|           504|             5|
5703|            28|             6|
5704|            28|             7|
5705|            28|             8|
5706|            28|             9|
5707|            28|            10|
5708|            28|            11|
5709|            28|            12|
5710|            28|            13|
5711|            28|            15|
5712|            28|            16|
5713|            28|            17|
5714|            28|            18|
5715|            28|            19|
5716|            28|            20|
5717|            28|            21|
5718|            28|            22|
6500|           504|             6|
6501|           504|             7|
6502|           504|             8|
7790|            24|             1|
6503|           504|             9|
6504|           504|            10|
5983|            37|             1|
6336|            26|             1|
6505|           504|            11|
6506|           504|            15|
6507|           504|            16|
6508|           504|            17|
6509|           504|            20|
6510|           504|            21|
6511|           504|            23|
7744|           150|             1|
6929|            78|             1|
6930|            78|             2|
6931|            78|             3|
6932|            78|             4|
6933|            78|             5|
6934|            78|             6|
6935|            78|             7|
6936|            78|             8|
6937|            78|             9|
6139|            16|             1|
6140|            16|             2|
6141|            16|             3|
6142|            16|             4|
6143|            16|            11|
7776|           109|             3|
6485|           384|             1|
6938|            78|            10|
6939|            78|            11|
6940|            78|            12|
6573|            17|             1|
7797|           397|             1|
6372|           502|             1|
6941|            78|            13|
6654|           498|             4|
6942|            78|            15|
6943|            78|            16|
7915|           410|             1|
6944|            78|            17|
6945|            78|            18|
6946|            78|            19|
6947|            78|            20|
6948|            78|            21|
6949|            78|            22|
7563|            33|             1|
7564|            33|             2|
6979|           411|             8|
7565|            33|             3|
7566|            33|            16|
7039|            55|             2|
8237|           551|             1|
8238|           551|             2|
8239|           551|             3|
8240|           551|             4|
8241|           551|            11|
8309|           208|             1|
8312|           561|             3|
8314|           560|             3|
8325|            20|             1|
8328|           566|             1|
7746|           528|             1|
7747|           529|             1|
7792|           455|             1|
7793|           455|             4|
7802|           458|             6|
7803|           458|            15|
7167|           500|             1|
7168|           500|            19|
7191|           419|             1|
7854|           420|             1|
7916|           413|             1|
8002|           542|             1|
8003|           542|             2|
8004|           542|             3|
8005|           542|             4|
8006|           542|             5|
8007|           542|             6|
8008|           542|             7|
8009|           542|             8|
8010|           542|             9|
8011|           542|            10|
8012|           542|            11|
8013|           542|            12|
8014|           542|            13|
8015|           542|            15|
8016|           542|            16|
8017|           542|            17|
8018|           542|            18|
8019|           542|            19|
8020|           542|            20|
8021|           542|            21|
8022|           542|            22|
8023|           542|            23|
8083|           282|             1|
8091|           312|            11|
8123|           535|             1|
8124|           535|            12|
8127|           408|             1|
8128|           408|             2|
8129|           408|             3|
8130|           408|             4|
8131|           408|             5|
8132|           408|             6|
8133|           408|             7|
8134|           408|             8|
8135|           408|            10|
8136|           408|            11|
8137|           408|            17|
8138|           408|            18|
8139|           408|            20|
8140|           408|            21|
8142|           499|             2|
8147|             1|             1|
8148|             1|             2|
8149|             1|             3|
8150|             1|             4|
8151|             1|             5|
8152|             1|             6|
8153|             1|             7|
8154|             1|             8|
8155|             1|             9|
8156|             1|            10|
8157|             1|            11|
8158|             1|            12|
8159|             1|            13|
8160|             1|            15|
8161|             1|            16|
8162|             1|            17|
8163|             1|            18|
8164|             1|            19|
8165|             1|            20|
8166|             1|            21|
8167|             1|            22|
8168|             1|            23|
8358|           318|             1|
8359|           318|             2|
8360|           318|             3|
8361|           318|             4|
8362|           318|             5|
8363|           318|             6|
8364|           318|             7|
8365|           318|             8|
8366|           318|             9|
8367|           318|            10|
8368|           318|            11|
8369|           318|            12|
8370|           318|            13|
8371|           318|            15|
8372|           318|            16|
8373|           318|            17|
8374|           318|            18|
8375|           318|            19|
8376|           318|            20|
8377|           318|            21|
8378|           318|            22|
8379|           318|            23|
8380|           569|             1|
8381|           569|            12|
8385|           571|             2|
8386|            53|             1|
8389|           572|             1|
8391|           423|             1|
8393|            93|             2|
8394|           573|             1|
8395|           574|             1|
8396|            30|             1|
8400|            41|             1|
8423|           433|             1|
8424|           433|             2|
8425|           433|             3|
8426|           433|             4|
8427|           433|             5|
8428|           433|             6|
8429|           433|             7|
8430|           433|             8|
8431|           433|             9|
8432|           433|            10|
8433|           433|            11|
8434|           433|            12|
8435|           433|            13|
8436|           433|            15|
8437|           433|            16|
8438|           433|            17|
8439|           433|            18|
8440|           433|            19|
8441|           433|            20|
8442|           433|            21|
8443|           433|            22|
8444|           433|            23|
8467|           576|             4|
8469|           575|             1|
8472|           486|             1|
8473|           577|             1|
8481|           579|             4|
8482|           516|             6|
8483|           516|            15|
8484|           580|             6|
8485|           580|            15|
8486|           581|             1|
8487|           581|             2|
8488|           581|             3|
8489|           581|             4|
8490|           581|             5|
8491|           581|             6|
8492|           581|             7|
8493|           581|             8|
8494|           581|             9|
8495|           581|            10|
8496|           581|            11|
8497|           581|            12|
8498|           581|            13|
8499|           581|            15|
8500|           581|            16|
8501|           581|            17|
8502|           581|            18|
8503|           581|            19|
8504|           581|            20|
8505|           581|            21|
8506|           581|            22|
8507|           581|            23|
8516|           131|             1|
8518|           563|             2|
8519|           563|            20|
8522|           514|             7|
8531|           296|             8|
8533|           564|             3|
8538|           583|             1|
8539|           583|             2|
8540|           583|             3|
8541|           583|             4|
8542|           583|             5|
8543|           583|             6|
8544|           583|             7|
8545|           583|             8|
8546|           583|             9|
8547|           583|            10|
8548|           583|            11|
8549|           583|            12|
8550|           583|            13|
8551|           583|            15|
8552|           583|            16|
8553|           583|            17|
8554|           583|            18|
8555|           583|            19|
8556|           583|            20|
8557|           583|            21|
8558|           583|            22|
8559|           583|            23|
8560|           584|             8|
8583|           586|             1|
8584|           586|             2|
8585|           586|             3|
8586|           586|             4|
8587|           586|             5|
8588|           586|             6|
8589|           586|             7|
8590|           586|             8|
8591|           586|             9|
8592|           586|            10|
8593|           586|            11|
8594|           586|            12|
8595|           586|            13|
8596|           586|            15|
8597|           586|            16|
8598|           586|            17|
8599|           586|            18|
8600|           586|            19|
8601|           586|            20|
8602|           586|            21|
8603|           586|            22|
8604|           586|            23|
8605|           587|             1|
8606|           587|             2|
8607|           587|             3|
8608|           587|             4|
8609|           587|             5|
8610|           587|             6|
8611|           587|             7|
8612|           587|             8|
8613|           587|             9|
8614|           587|            10|
8615|           587|            11|
8616|           587|            12|
8617|           587|            13|
8618|           587|            15|
8619|           587|            16|
8620|           587|            17|
8621|           587|            18|
8622|           587|            19|
8623|           587|            20|
8624|           587|            21|
8625|           587|            22|
8626|           587|            23|
8627|           588|             1|
8628|           588|             2|
8629|           588|             3|
8630|           588|             4|
8631|           588|             5|
8632|           588|             6|
8633|           588|             7|
8634|           588|             8|
8635|           588|             9|
8636|           588|            10|
8637|           588|            11|
8638|           588|            12|
8639|           588|            13|
8640|           588|            15|
8641|           588|            16|
8642|           588|            17|
8643|           588|            18|
8644|           588|            19|
8645|           588|            20|
8646|           588|            21|
8647|           588|            22|
8648|           588|            23|
8672|            44|             1|
8673|            44|             2|
8674|            44|             3|
8675|            44|             4|
8676|            44|             5|
8677|            44|             6|
8678|            44|             7|
8679|            44|             8|
8680|            44|             9|
8681|            44|            10|
8682|            44|            11|
8683|            44|            12|
8684|            44|            13|
8685|            44|            15|
8686|            44|            16|
8687|            44|            17|
8688|            44|            18|
8689|            44|            19|
8690|            44|            20|
8691|            44|            21|
8692|            44|            22|
8693|            44|            23|
8695|           129|             1|
8697|            61|             1|
8699|           590|             3|
8700|           111|             3|
8701|            88|             3|
8702|            64|             1|
8704|           361|             3|
8705|            69|             1|
8707|            63|             1|
8709|           460|             2|
8710|           456|             1|
8711|            71|             1|
8712|           591|             5|
8713|            66|             1|
8715|           485|             3|
8716|           429|             7|
8717|           441|             3|
8718|           358|             3|
8719|           250|             2|
8720|           570|             2|
8721|           116|             1|
8722|           116|             4|
8723|           123|             4|
8724|           126|             1|
8725|           440|             3|
8726|           114|             5|
8727|            94|             2|
8728|            56|             2|
8731|           143|             4|
8732|           555|             4|
8733|           439|             3|
8734|           351|             2|
8735|           320|             1|
8736|           320|             7|
8739|           183|             1|
8740|           142|             4|
8741|           100|             6|
8742|           100|            15|
8745|           134|             1|
8746|           120|             4|
8749|           592|             2|
8750|           102|             9|
8751|           102|            10|
8752|           236|             1|
8753|           169|            10|
8755|           545|             9|
8758|           186|             1|
8759|           186|             2|
8760|           186|             3|
8761|           186|             4|
8762|           186|             5|
8763|           186|             6|
8764|           186|             7|
8765|           186|             8|
8766|           186|             9|
8767|           186|            10|
8768|           186|            11|
8769|           186|            12|
8770|           186|            13|
8771|           186|            15|
8772|           186|            17|
8773|           186|            18|
8774|           186|            19|
8775|           186|            20|
8776|           186|            21|
8777|           186|            23|
8778|           248|             4|
8781|           425|             7|
8782|           341|             1|
8784|           235|             8|
8785|           136|             4|
8786|           497|             4|
8787|           517|            12|
8788|           163|             8|
8789|            67|             4|
8790|           360|             3|
8791|            95|             3|
8792|           295|             4|
8793|           389|             1|
8794|           165|             1|
8806|            10|             1|
8807|            10|             2|
8808|            10|             3|
8809|            10|             4|
8810|            10|             5|
8811|            10|             6|
8812|            10|             7|
8813|            10|             8|
8814|            10|             9|
8815|            10|            10|
8816|            10|            11|
8817|            10|            13|
8818|            10|            15|
8819|            10|            16|
8820|            10|            17|
8821|            10|            20|
8822|            10|            21|
8823|            10|            22|
8824|           156|            16|
8835|            68|             4|
8836|           562|             3|
8837|           479|             5|
8838|            89|             3|
8839|           554|             1|
8840|           390|             4|
8841|           177|             1|
8842|           177|             7|
8843|           596|             1|
8844|           596|             2|
8845|           596|             3|
8846|           596|             4|
8847|           596|             5|
8848|           596|             6|
8849|           596|             7|
8850|           596|             8|
8851|           596|             9|
8852|           596|            10|
8853|           596|            11|
8854|           596|            12|
8855|           596|            13|
8856|           596|            15|
8857|           596|            16|
8858|           596|            17|
8859|           596|            18|
8860|           596|            19|
8861|           596|            20|
8862|           596|            21|
8863|           596|            22|
8864|           596|            23|
8866|           194|            20|
8868|           537|            17|
8869|           445|             4|
8870|            76|             1|
8871|            76|             3|
8872|           158|             8|
8873|           345|             1|
8874|           345|             2|
8875|           168|            10|
8876|            60|             1|
8877|           508|             4|
8878|           198|             1|
8879|           198|             2|
8880|           198|             3|
8881|           198|             4|
8882|           198|             5|
8883|           198|             6|
8884|           198|             7|
8885|           198|             8|
8886|           198|             9|
8887|           198|            10|
8888|           198|            15|
8889|           201|             7|
8890|           546|             9|
8891|           473|             1|
8892|           357|             2|
8893|           396|             1|
8894|           264|             1|
8895|           363|             3|
8896|           595|             1|
8898|           314|             4|
8899|           336|             1|
8900|           321|             2|
8901|           316|            20|
8902|           556|             4|
8924|           567|             4|
8947|           481|            23|
8948|           484|             1|
8949|           496|            23|
8951|           515|             1|
8952|           515|             2|
8953|           515|             3|
8954|           515|             4|
8955|           515|             5|
8956|           515|             6|
8957|           515|             7|
8958|           515|             8|
8959|           515|             9|
8960|           515|            10|
8961|           515|            11|
8962|           515|            12|
8963|           515|            13|
8964|           515|            15|
8965|           515|            17|
8966|           515|            18|
8967|           515|            19|
8968|           515|            20|
8969|           515|            21|
8970|           515|            23|
8983|           206|             1|
8984|           525|             1|
8985|           525|             2|
8986|           525|             3|
8987|           525|             4|
8988|           525|             5|
8989|           525|             6|
8990|           525|             7|
8991|           525|             8|
8992|           525|             9|
8993|           525|            10|
8994|           525|            11|
8995|           525|            12|
8996|           525|            13|
8997|           525|            15|
8998|           525|            16|
8999|           525|            17|
9000|           525|            18|
9001|           525|            19|
9002|           525|            20|
9003|           525|            21|
9004|           525|            22|
9005|           525|            23|
9006|           525|            24|
9007|           525|            25|
9008|           533|             2|
9009|           531|             1|
9013|           543|             4|
9014|           582|             1|
9015|           593|             2|
9016|           568|             2|
9018|           557|             1|
9019|           117|             8|
9042|           449|             5|
9044|           340|            21|
9068|           597|             3|
9069|           386|             1|
9071|           288|             1|
9072|           578|             1|
9073|           578|             2|
9074|            29|             1|
9075|            29|             2|
9076|           289|            17|
9097|           599|             6|
9098|           599|            15|
9099|           447|             1|
9100|           447|             2|
9101|           447|             3|
9102|           447|             4|
9103|           447|             5|
9104|           447|             6|
9105|           447|             7|
9106|           447|             8|
9107|           447|            10|
9108|           447|            11|
9109|           447|            17|
9110|           447|            18|
9111|           447|            20|
9112|           565|             1|
9113|           600|             2|
9114|           457|             1|
9115|           385|             1|
9116|           383|             1|
9117|           526|             1|
9118|           407|             1|
9120|           137|             1|
9121|           137|            12|
9122|           137|            13|
9145|           601|             4|
9146|           522|             7|
9167|           409|             3|
9168|           523|             3|
9169|           365|             1|
9170|           365|             4|
9171|           594|             1|
9172|           207|             1|
9174|           602|             1|
9175|           603|             1|
9177|           605|             2|
9178|           604|             2|
9179|           146|             1|
9180|            19|             1|
9181|            19|             2|
9182|            19|             3|
9183|            19|             4|
9184|            19|             5|
9185|            19|             6|
9186|            19|             7|
9187|            19|             8|
9188|            19|             9|
9189|            19|            10|
9190|            19|            11|
9191|            19|            15|
9192|            19|            17|
9193|            19|            19|
9194|            19|            20|
9195|            19|            21|
9196|            79|             1|
9197|           606|             2|
9198|            83|             1|
9199|            83|             2|
9200|            83|             3|
9201|            83|             4|
9202|            83|             5|
9203|            83|             6|
9204|            83|             7|
9205|            83|             8|
9206|            83|            10|
9207|            83|            11|
9209|           210|             1|
9210|           210|             2|
9211|           210|             3|
9212|           210|             4|
9213|           210|             5|
9214|           210|             6|
9215|           210|             7|
9216|           210|             8|
9217|           210|             9|
9218|           210|            10|
9219|           210|            11|
9220|           210|            12|
9221|           210|            13|
9222|           210|            15|
9223|           210|            16|
9224|           210|            17|
9225|           210|            18|
9226|           210|            19|
9227|           210|            20|
9228|           210|            21|
9229|           210|            23|
9230|           210|            26|
9231|           421|             1|
9232|           421|             2|
9233|           421|             3|
9234|           421|             4|
9235|           421|             5|
9236|           421|             6|
9237|           421|             7|
9238|           421|             8|
9239|           421|             9|
9240|           421|            10|
9241|           421|            11|
9242|           421|            12|
9243|           421|            13|
9244|           421|            15|
9245|           421|            16|
9246|           421|            17|
9247|           421|            18|
9248|           421|            20|

CREATE TABLE public.system_users (
     id int4 NOT NULL,
     "name" text NOT NULL,
     login text NOT NULL,
     "password" text NOT NULL,
     email text NULL,
     frontpage_id int4 NULL,
     system_unit_id int4 NULL,
     active bpchar(1) NULL,
     accepted_term_policy bpchar(1) NULL,
     accepted_term_policy_at text NULL,
     two_factor_enabled bpchar(1) DEFAULT 'N'::bpchar NULL,
     two_factor_secret varchar(255) NULL,
     two_factor_type varchar(100) NULL,
     CONSTRAINT system_users_pkey PRIMARY KEY (id),
     CONSTRAINT system_users_frontpage_id_fkey FOREIGN KEY (frontpage_id) REFERENCES public.system_program(id),
     CONSTRAINT system_users_system_unit_id_fkey FOREIGN KEY (system_unit_id) REFERENCES public.system_unit(id)
);
CREATE INDEX sys_user_program_idx ON public.system_users USING btree (frontpage_id);
id |name                                              |login        |password                                                    |email                                   |frontpage_id|system_unit_id|active|accepted_term_policy|accepted_term_policy_at|two_factor_enabled|two_factor_secret|two_factor_type|
---+--------------------------------------------------+-------------+------------------------------------------------------------+----------------------------------------+------------+--------------+------+--------------------+-----------------------+------------------+-----------------+---------------+
202|Bot CVL                                           |1234         |8bb88f80d334b1869781beb89f7b73be                            |qualidade.10@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
213|Carlos Eduardo Silva                              |7087         |5c2631d54272554b181cf21ad2171fa3                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
101|Juliano Marzani                                   |6083         |add5efc3f8de35d6208dc6fc154b59d3                            |gerencia.ngt@carvalima.com.br           |         634|             9|N     |                    |                       |N                 |                 |               |
214|Rebeca Pierre de Souza                            |5957         |9aa42b31882ec039965f3c4923ce901b                            |tst.rh02.sao@carvalima.com.br           |            |             3|Y     |                    |                       |N                 |                 |               |
216|Olga Mustafá Marques (clone)                      |6235         |95f98317dc8907f13998a214306234db                            |qualidade@carvalima.com.br              |         634|             1|N     |                    |                       |N                 |                 |               |
224|David dos Reis Pereira                            |7271         |52fc2aee802efbad698503d28ebd3a1f                            |trafego23.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
240|Leonardo Ivan Bastos Souza                        |7424         |420824960f755f8721c47b6027ead6ab                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
234|Giovanna Cristina Bueno Inocêncio                 |6262         |481fbfa59da2581098e841b7afc122f1                            |                                        |         634|             8|Y     |                    |                       |N                 |                 |               |
 54|Frederico Bathazar Petsch                         |4704         |cdffb1c32960b0b62270d620d041bea2                            |oficina@carvalima.com.br                |         634|             1|N     |                    |                       |N                 |                 |               |
  7|Usuario Padrao Estoque                            |Estoque      |827ccb0eea8a706c4c34a16891f84e7b                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
238|Ivan Araujo Paiva                                 |6928         |20c1945eae4b9868cbbfd09675f7d76e                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
241|Jonathan da Silva Oliveira                        |6905         |0411dab8c5b388e45ec1f93847117a94                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
275|KIOSHI SERVICOS DE MANUTENCAO E REPARACAO MECANICA|2132         |f6e794a75c5d51de081dbefa224304f9                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
154|Danilo Augusto de Souza Gomes                     |6903         |2493469771897bf9bf6df993d1ac0713                            |supervisor.pcm@carvalima.com.br         |         634|             1|N     |                    |                       |N                 |                 |               |
269|Antonio Lucas Freitas Araujo                      |7563         |0ce98f53e3aa229aa2f31b16e5dcbb4b                            |almoxarifado7.cgb@carvalima.com.br      |         634|             1|N     |                    |                       |N                 |                 |               |
230|Marcelo Bof Matheus                               |7425         |1e5186bca8f75fca53960e8cb4a3b973                            |oficina@carvalima.com.br                |         634|             1|N     |                    |                       |N                 |                 |               |
228|Wender Renato Antonio Martins                     |2964         |60ad83801910ec976590f69f638e0d6d                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
  3|Olga Mustafa Marques                              |6235         |95f98317dc8907f13998a214306234db                            |qualidade@carvalima.com.br              |         634|             1|N     |                    |                       |N                 |                 |               |
312|Maykon Charles França Costa                       |7495         |$2y$10$e30ARsCcQLopuXfKsR5e0.vLnIjbCTgtqFgseJ3bbtGiMAZf6CIjS|                                        |         634|            11|Y     |                    |                       |N                 |                 |               |
220|Eder Santana de Moraes                            |6052         |d2a10b0bd670e442b1d3caa3fbf9e695                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
311|Manoel Augusto de Oliveira Arruda                 |7218         |$2y$10$Vkz6djwJwNtHxxQp.JmiiuNWQiIoPiHIt3ZaQ1piVtvRA2iXcYYFa|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
237|Eloir Sespede                                     |2714         |ce4a5f6593b837e1ef4bd984ff377e83                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
283|Luiz Otavio Maia de Arruda                        |7472         |$2y$10$CjQiTi.bE7MB59tlRmHRpOcdyV7nur2OdLfLoR7Ho.Bh/d6/HH3hG|manutencao.frota2@carvalima.com.br      |         634|             1|N     |                    |                       |N                 |                 |               |
260|Usuario Supervisão                                |supervisao   |632b62a30539c7eea1b8fe4eaf133970                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
408|Lauriany de Sousa Cardoso                         |7717         |$2y$10$M9KVRpB7ZUwhUT.9M8GU6eVygph1wA/QnrihaEz/Kat5ZSoo1VO6q|trafego12.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
272|LEANDRO BENITEZ DA SILVA                          |4342         |1f5e7f2748adabf08629a6312ac3bfdd                            |sistemaunitop@consultoria.com.br        |         768|             1|N     |                    |                       |N                 |                 |               |
227|Daiane Pessoa Lima                                |7373         |$2y$10$NX.Ug1iPG7K8Wp.CDcgw9Oxv7DRzw3QBzp2IQLV4s9EOKs3iIwI/i|qualidade.11@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
217|Clenilton de Moura Rodrigues                      |5624         |$2y$10$lbxFelHPjQ77V69vxxct2uXjr3UkGZ4qImH607piCFTg26UTLq2Um|                                        |         634|             4|N     |                    |                       |N                 |                 |               |
207|Emily Lays Santos                                 |1402         |$2y$10$ExjOuT2LAbI0T6tkxuqOo.XsHT6g7LjoiZwQU1vvmjFLv8AngKTfW|Emily.lays@carvalima.com.br             |         634|             1|Y     |                    |                       |N                 |                 |               |
 46|Jessica de Souza                                  |6220         |827ccb0eea8a706c4c34a16891f84e7b                            |controladoria.02@carvalima.com.br       |         634|             1|N     |                    |                       |N                 |                 |               |
212|Daniele de Fatima S de Deus                       |7090         |7cc980b0f894bd0cf05c37c246f215f3                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
287|Claudeci Bernadino dos Santos Gomes               |7420         |$2y$10$EOvE.CGueln3B4GihxR0suSUZgYHWb/0MsWXw.DYoaKhxV0cnMqZS|                                        |         634|            20|Y     |                    |                       |N                 |                 |               |
201| Cleyton Luiz Barboza                             |7170         |$2y$10$IUfhPlfcmQ8SejCup7BePuZARqVz5GjJQmBh2vbpMEyUiil/Mjbna|analista01.cwb@carvalima.com.br         |         634|             7|Y     |                    |                       |N                 |                 |               |
229| Isabely Nalin Ferreira                           |7421         |$2y$10$PVMUggavhKOSJwfsm27oKuOCkvIQPhTGBsBLVrp3ucGyuUDry/9KO|adm.ldb@carvalima.com.br                |         634|            20|Y     |                    |                       |N                 |                 |               |
236|Alex Robertt Lara                                 |4897         |$2y$10$MK65Yu2LvaLAudE0zMsPzOd/CSq/l13C61aKxMJZd5OuWlmX7S3DO|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
222|Andre Benjamim dos Anjos Silva                    |6218         |$2y$10$p7FUpWyANlTTi7QBwVLB2eJEbhCHSyZvqbUpigQ47GPmfm925d6za|trafego20.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
 96|Arlei Koch Flach                                  |3535         |bdf8ed841faea47d80a18e9892b5d560                            |arlei.flach@carvalima.com.br            |         634|             1|N     |                    |                       |N                 |                 |               |
302|Igor da Silva Fernandes                           |6894         |$2y$10$j2s9jbauDb3LZoB6r5KTQuObQhw0HTCvW0KkcPtvUTXhgME7q02Pe|manutencao.cgr@carvalima.com.br         |         634|             2|Y     |                    |                       |N                 |                 |               |
200|S                                                 |707          |29e11ea8ec6c7804a7f939e8e78e9c18                            |supervisorop.pvh@carvalima.com.br       |            |            17|N     |                    |                       |N                 |                 |               |
209|Dayane Fernandes                                  |5106         |173f0f6bb0ee97cf5098f73ee94029d4                            |coleta11.cgb@carvalima.com.br           |         634|             1|N     |                    |                       |N                 |                 |               |
199|Aline Geber de Sa                                 |6977         |312f1ba2a72318edaaa995a67835fad5                            |rh.pvh@carvalima.com.br                 |            |            17|N     |                    |                       |N                 |                 |               |
210|Stephanie Paula                                   |7233         |$2y$10$MyAqZ1iBm1MJ7X47YFWSAeza6ikoXLlw1RJRIvAEWOollrVDNKCRe|qualidade.01@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
203|Wellington Rodrigo Guzzi                          |7202         |f862d13454fd267baa5fedfffb200567                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
204|Mayare Fernanda Rodrigues Costa                   |6992         |9bcb182ab31f229322cff3c8888b2cec                            |adm.ldb@carvalima.com.br                |         634|            20|N     |                    |                       |N                 |                 |               |
205|Carlos Eduardo de Moraes Silva                    |7087         |5c2631d54272554b181cf21ad2171fa3                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
131|Andre Luiz Vargas Correa Almeida                  |4226         |$2y$10$.q.Z1fpqnJUjiXR7zjtN6O.wGaWKLwz3PpKZVYPOFVXouQGcAaLhy|patrimonio@carvalima.com.br             |         634|             1|Y     |                    |                       |N                 |                 |               |
231|David Guilherme Giordano Souza                    |7426         |a0833c8a1817526ac555f8d67727caf6                            |FROTA7.CBA@CARVALIMA.COM.BR             |         634|             1|N     |                    |                       |N                 |                 |               |
246|Luiza Belarmino dos Reis                          |7476         |cd19a3a0867f69f400961b5dd502fbc6                            |financeiro.ngt@carvalima.com.br         |         634|             9|N     |                    |                       |N                 |                 |               |
208|Leandro Benitz da Silva                           |3059         |$2y$10$wp0gYKpT4HXm99.OKXKj4.nBjvRZmvEvRoTdXMPuhukxGTcyCNMxO|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
242|Mateus Lucas                                      |6593         |0765933456f074d2c75bbbad63af95e6                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
104|Lucas de Oliveira Araujo                          |6750         |f7bdb0e100275600f9e183e25d81822d                            |gerencia.drd@carvalima.com.br           |         634|             5|N     |                    |                       |N                 |                 |               |
226|Karen Silva de Oliveira Pessoa                    |7318         |02c27682b80b462437ba4efc71267562                            |Rh.rbo@carvalima.com.br                 |         634|            21|Y     |                    |                       |N                 |                 |               |
276|REVITA MANUTENCOES EM FIBRA LTDA                  |3701         |b181eaa49f5924e16c772dcb718fcd0f                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
190|Bruno Martins Montovani                           |7029         |$2y$10$YdlJQt.KbX/pHjlvHpFxVeWxKrsWKh4VU/iS/OlfpOiWdNqlFLqs.|analista01.mtz@carvalima.com.br         |         634|             1|Y     |                    |                       |N                 |                 |               |
279|ALEF VINICIUS FERREIRA                            |5348         |b32e8760418e68f23c811a1cfd6bda78                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
299|Amauricio Queiroz                                 |9010         |dff1749a367a95e75a84a6385df5dfa9                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
 52|Kleber Garcia                                     |4170         |8aaf4747d997c466b4e3c890a06a88c5                            |frota6.cba@carvalima.com.br             |         634|             1|N     |                    |                       |N                 |                 |               |
195|Mariana Eliza Guilherme Leite                     |7078         |b53477c2821c1bf0da5d40e57b870d35                            |qualidade.03@carvalima.com.br           |         634|             1|N     |                    |                       |N                 |                 |               |
 72|Samuel Santos da Silva                            |6541         |751f915c24612ce66dba400a86a0909b                            |operacional3.cba@carvalima.com.br       |         634|             4|N     |                    |                       |N                 |                 |               |
306|Rita de Kassya da Silva Costa                     |7161         |8860a4e27cbbe4c63821b429211684a3                            |                                        |         634|             5|Y     |                    |                       |N                 |                 |               |
  4|Silvana Izabel                                    |5248         |cd773ad6e5538f91e99cdef66aa4c0a1                            |controladoria.07@carvalima.com.br       |         634|             1|N     |                    |                       |N                 |                 |               |
300|Jorge Leonidio                                    |2030         |2d579dc29360d8bbfbb4aa541de5afa9                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
243|Vinicius da Silva                                 |6969         |7813d1590d28a7dd372ad54b5d29d033                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
 28|Otavio Fedrizze                                   |01644062127  |$2y$10$lOeP5CKl6s00qrusbnAsruviSBsCl4q7ZPsbbFOx7VJyLk1EpsGKS|otavio@carvalima.com.br                 |         634|             1|Y     |                    |                       |N                 |                 |               |
268|Maria Eduarda Rezene da Costa                     |7098         |57342f6b95854ad89e9c4088ab94adcf                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
 56|Wilson Roberto Mendes de Oliveira                 |3819         |$2y$10$BzUJoz8umaPZuFn13Vwuyechc3tVohONZm9S8AgM.MVAPFwe55GXS|instrutor.cgr@carvalima.com.br          |         634|             2|Y     |                    |                       |N                 |                 |               |
 39|Guilherme Paulo Rodrigues de Arruda Silva         |6361         |e10adc3949ba59abbe56e057f20f883e                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
 92|Mariane S Amorim                                  |6767         |$2y$10$y3SH5uUSCn90ae9C.yOl3uK36Ujz9/qzUtXzrw3Iax8diw7FRXjKG|qualidade.07@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
298|Gerson Lima                                       |1020         |65cc2c8205a05d7379fa3a6386f710e1                            |sistemaunitop@consultoria.com.br        |         768|             1|N     |                    |                       |N                 |                 |               |
 60|Inaina Nunes                                      |6632         |$2y$10$5mRy.MnKkXqoHjXrq0sts.U01B9sQNkmZZh2ZewmvNKhSeZ1PxPTC|sac.mtz@carvalima.com.br                |         634|             1|Y     |                    |                       |N                 |                 |               |
181|Mariana Mabilia                                   |4580         |$2y$10$PJLsWo3Mq4ZkOLEtuXN7CeYWYgD78z.dNVJvmqWIiKRZL0nq2w44K|rh.cwb@carvalima.com.br                 |         634|             7|N     |                    |                       |N                 |                 |               |
303|Washington Diego Pereira                          |5884         |7ed2d3454c5eea71148b11d0c25104ff                            |supervisorope.cgb@carvalima.com         |         634|             4|N     |                    |                       |N                 |                 |               |
310|00                                                |00           |2377f9eb902f3c5855aca19197689b14                            |00                                      |         634|             1|N     |                    |                       |N                 |                 |               |
 91|Weverton de Oliveira Santos                       |3006         |03190d8acb3c9c1d70c7ec84523e224d                            |operacional.cgr@carvalima.com.br        |         634|             2|N     |                    |                       |N                 |                 |               |
 29|Rodrigo Prado Faria                               |3942         |$2y$10$p28GVdIDK.eb7kEr2coRBOCJp1J9h2eC./6pBIpujiF2kSODG3Ry6|frota3.cba@carvalima.com.br             |         634|             1|Y     |                    |                       |N                 |                 |               |
 57|Erica Fernanda                                    |2026         |$2y$10$u4.Wk4XtmAlLsmugoYScI.UbbLnygnVn4oroucyfJRApKfeO7ZD6m|pendencias.mtz@carvalima.com.br         |         634|             1|Y     |                    |                       |N                 |                 |               |
  6|Usuario Padrao Compras                            |Compras      |827ccb0eea8a706c4c34a16891f84e7b                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
 25|Lidiane Silva                                     |2309         |$2y$10$dtwx3R4ojhrVKUYIjQ3G9.pQWLwlrAcbrOTS92xO4bBK4v80aNbW2|lidiane@carvalima.com.br                |         634|             1|Y     |                    |                       |N                 |                 |               |
183|Luciana da Silva Provenzano                       |4482         |$2y$10$SGf3K2DeCaNAaugQ11QpM.Gil/HefeICCJYES2jkEA8MVZ2Kbr/.u|contasareceber3@carvalima.com.br        |         634|             1|Y     |                    |                       |N                 |                 |               |
 33|Fernanda Rodrigues                                |4304         |$2y$10$k0XZY62eZGDl0hXX18NnE.8E8AvWVoeDvtZq8H1UHaPPJRnx7pOmO|abastecimento.cgb@carvalima.com.br      |         634|             1|Y     |                    |                       |N                 |                 |               |
304|Bruno Marçal de Souza                             |6920         |$2y$10$zYHLbhM3WI/QREX1lZV7KO/efBuGWTdenYoZc3YNSwak2LWOQoFXm|                                        |         634|             8|Y     |                    |                       |N                 |                 |               |
409|Ricardo Guilherme                                 |6705         |$2y$10$IdsUX/y3v6EMaSQiqWNH4uqPv1UepNHwmdVyETiNi4X0Yc8DOkpp6|ti.sao@carvalima.com.br                 |         634|             3|Y     |                    |                       |N                 |                 |               |
 12|Andre Luiz                                        |2191         |$2y$10$KWwOXGvCLC99d99.A1wDluKJxF3iKQwrhWGpkqMz9FvIB4UNTTuWW|almoxarifado3.cgb@carvalima.com.br      |         634|             1|Y     |                    |                       |N                 |                 |               |
100|Valnei Coelho do Nascimento                       |4565         |$2y$10$mCRXkIa5TH6a9eVG3Qr8nuYMQ3pKqXMAKWVvEAz3uKqtqEmTPdvH2|gerencia.vha@carvalima.com.br           |         634|             6|Y     |                    |                       |N                 |                 |               |
184|Gilbert Eliseu Amador                             |6889         |0a552abf945aad2831a917bd58bbdc70                            |novosnegocios@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
235|Flavia Sousa Soares                               |5864         |7cdace91c487558e27ce54df7cdb299c                            |supervisor2.roo@carvalima.com.br        |         634|             8|Y     |                    |                       |N                 |                 |               |
 31|Jonathan Arruda de Oliveira                       |4500         |$2y$10$VJ0t62oVW0SZzKoMh1c1ou2klsliQNQ20RCBGtPSAURNe/EvzaGHS|trafego04.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
416|Erica Milena da Silva                             |6710         |dffbb6efd376d8dbb22cdf491e481edc                            |administrativo04.cgb@carvalima.com.br   |         634|             4|N     |                    |                       |N                 |                 |               |
 26|Vitor Hugo Farias Morgado                         |4475         |$2y$10$IP5Txxhkh/AOs9HsdHoareDlgFWtmAw91hSknj4vVSliX/Xknuyq6|supervisor.pcm@carvalima.com.br         |         634|             1|N     |                    |                       |N                 |                 |               |
 53|Marcos Sena                                       |5716         |$2y$10$7e5vQcJ/oVJBTf8YWcoFx.xKmvah8GuaktC30eluWdtA3Dd7GNzd2|trafego09.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
 42|Daniel Trindade                                   |6196         |$2y$10$SxMRTf7WCDHTOE0gVK23XOpeMxchrtdKIdSApdAHMo2bo7r1KqitO|controladoria.09@carvalima.com.br       |         634|             1|Y     |                    |                       |N                 |                 |               |
198|Rian da Silva Mendonça                            |6723         |$2y$10$3NRe.N4CdF2sdYDI0sh0xuFTsmKIUgPc.a2OwnFnuHU/RWkTuB1di|progamacao.trafego@carvalima.com.br     |         634|             1|Y     |                    |                       |N                 |                 |               |
 21|Hedynho Bala                                      |6052         |202cb962ac59075b964b07152d234b70                            |almoxarifado7.cgb@carvalima.com.br      |         634|             1|N     |                    |                       |N                 |                 |               |
 48|Douglas Henrique Batista                          |6025         |202cb962ac59075b964b07152d234b70                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
196|Luiz Felippe Silva                                |6930         |2f254e66097fd653a5ca4cfdb33be358                            |oficina@carvalima.com.br                |         634|             1|N     |                    |                       |N                 |                 |               |
 20|Thiago T Neves                                    |4013         |$2y$10$xW.0yq4f8MJ0xVgFGb3PHOo6tmg29A0VlAoMaPy1hNBK8km9Ca5V.|almoxarifado6.cgb@carvalima.com.br      |         634|             1|Y     |                    |                       |N                 |                 |               |
 30|Priscilla Silva                                   |4998         |$2y$10$CzWgtOwYNmLYyExFFSrW1.sK/8eJFmjzxDXvjcLA7tBMzUZR0r.n.|                                        |         634|             1|N     |                    |                       |N                 |                 |               |
 49|Vinicius Leandro                                  |4086         |827ccb0eea8a706c4c34a16891f84e7b                            |controladoria@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
244|Lucimar Carlos de Souza                           |2540         |9657c1fffd38824e5ab0472e022e577e                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
189|CLEBER CRISTIANO SILVA P FIGUEIREDO               |123456       |e10adc3949ba59abbe56e057f20f883e                            |sistemaunitop@consultoria.com.br        |         634|              |Y     |                    |                       |N                 |                 |               |
 59|Edson Meirelles                                   |5993         |32e0bd1497aa43e02a42f47d9d6515ad                            |edson.meireles@carvalima.com.br         |         634|             1|N     |                    |                       |N                 |                 |               |
 51|Matheus Romeu                                     |4602         |fd00d3474e495e7b6d5f9f575b2d7ec4                            |matheus.romeu@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
 80|Miriã Fernandes Amorim                            |6429         |33b3214d792caf311e1f00fd22b392c5                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
 82|Everton Ferro Alves Lopes                         |5250         |$2y$10$0dJRN41Ilfn0UsgNFsP7neUNxTUnrxamQaIYUUg.dGwu96xfWEJa.|analista.fedrizze@carvalima.com.br      |         634|            12|N     |                    |                       |N                 |                 |               |
 47|Joao Alex Sandro Bartko                           |00370176910  |827ccb0eea8a706c4c34a16891f84e7b                            |gerencia.trafego@carvalima.com.br       |         634|             1|N     |                    |                       |N                 |                 |               |
280|ELTON KLEY TEXEIRA                                |3100         |cc3d69ed781b16bce06687822ae56e6d                            |sistemaunitop@consultoria.com.br        |         768|             1|N     |                    |                       |N                 |                 |               |
 18|Demilson De Sampaio                               |5552         |$2y$10$DLfLMoCKutkQcQ2pJGV5c.NAVBkdzF74b/mdj1LEK/4ZnzXtqnH/a|compras01.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
 16|Wanderson Raphael                                 |4467         |$2y$10$NbDXnY5Stnbfc4T1leMZqOw9Lvyzz/ox2IB6EUN6lHBi5lVs6GvTO|Suprimentos@carvalima.com.br            |         634|             1|Y     |                    |                       |N                 |                 |               |
 74|Gislaine Aparecida de Amorim                      |6219         |$2y$10$ejwFI.unzM5PjqcH4p0Xo.EqEVDfKQ1dwKDljzvZ8FfktXfh/R.Dy|operacional5.cba@carvalima.com.br       |         634|             4|N     |                    |                       |N                 |                 |               |
308|Suellen Correa Goncalves de Sena                  |7577         |c57daa0bc9c4d8e35a21e9a2801aecb2                            |tst04.mtz@carvalima.com.br              |         634|             1|N     |                    |                       |N                 |                 |               |
 50|Lucivanio Vagner                                  |1884         |96170cc43bd77a7b385939405c8df626                            |gerencia.frota@carvalima.com.br         |         634|             1|N     |                    |                       |N                 |                 |               |
 41|Fabricio Albuquerque                              |6018         |$2y$10$QN5G9QvlOUpH5lUm78h1Pez5GMkknTn/2mlUgBy.eyou8VBgqzSYK|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
192| Eduardo Thierry                                  |6607         |7caf5e22ea3eb8175ab518429c8589a4                            |operacional3.cba@carvalima.com.br       |         634|             4|N     |                    |                       |N                 |                 |               |
 32|Paulo Bustamante                                  |6441         |$2y$10$/shUE58te/.MEOP8WikYaenLU4WmAfFWqOm7jTLRxpj.YlBLsumja|almoxarifado4.cgb@carvalima.com.br      |         634|             1|Y     |                    |                       |N                 |                 |               |
 17|Gilson Marques                                    |6197         |$2y$10$hxKJgRZNvzBcHf78NjDbMuGthvJIFDVCC5p2T8YnsD5Q7qiv.COxu|compras02.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
 36|Edno Jesus de Matos Franco                        |2200         |$2y$10$g1K3gNTrRp1MJCEvnJK7R.l7Q3zMVX9v4EYoJeB31scsy4kWp3ia6|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
 94|Adriano Espindola Gonçalves                       |3774         |$2y$10$yOE6PY87Vtjf6HOuVRbgFOFNjD5O66t/yy24IVKjaMoed7aUHuiIm|galpao.cgr@carvalima.com.br             |         634|             2|Y     |                    |                       |N                 |                 |               |
 27|Danilo Ferraz                                     |5517         |$2y$10$x0IGFk.Fkg27cXeKyzWcW.vSjld6Vke6GyFkzPiQ2ATOUm9aUIX/C|frota8.cba@carvalima.com.br             |         634|             1|Y     |                    |                       |N                 |                 |               |
 71|Erenilda Custodio                                 |2553         |$2y$10$6CRjBWPXyjwVuC72FR7A7..8DI1BRSkYzXWrDsdCDn/Zg7SuM19JW|financeiro.mtz@carvalima.com.br         |         634|             1|Y     |                    |                       |N                 |                 |               |
103|Beatriz da Cunha Teixeira Coelho                  |5799         |de01d76e793fec3fba32f4401a45fb20                            |supervisorop.jip@carvalima.com.br       |         634|            15|N     |                    |                       |N                 |                 |               |
185|Bernardo Gabriel Romulo Terlecki                  |6314         |ec20019911a77ad39d023710be68aaa1                            |trafego2.cgr@outlook.com.br             |         634|             2|N     |                    |                       |N                 |                 |               |
 69|Debora C. G. Machado                              |1555         |$2y$10$8Ttj9b1idFbcmYX1J6u0rOMVQDlykoiv6Ae0sme6VXSq6wJdosjPe|debora.rh@carvalima.com.br              |         634|             1|Y     |                    |                       |N                 |                 |               |
 10|Victor Santos                                     |5518         |$2y$10$/F29OaQYL3KTo1trKLeBfOMKAyxntyf2HAlpO0oKBfvJZOOphfmMG|supervisor.adm@carvalima.com.br         |         634|             1|Y     |                    |                       |N                 |                 |               |
 75|Paulo Sergio                                      |1933         |827ccb0eea8a706c4c34a16891f84e7b                            |paulo.rh@carvalima.com.br               |         634|             1|N     |                    |                       |N                 |                 |               |
115|Raissyo de Almeida Diniz                          |5022         |e36258b3c74f08054a974a5fe1703f9c                            |supervisor.pa@carvalima.com.br          |         634|             4|N     |                    |                       |N                 |                 |               |
 89|Alexandre Venturini                               |6595         |$2y$10$iRqJMyxE8cTJItXRvdV/Cu7TuvTMvIgDS6LDhwlTUbwMxtZveOXe6|Supervisorop06.sao@carvalima.com.br     |         634|             3|Y     |                    |                       |N                 |                 |               |
179|Ana Paula de Souza Rodrigues                      |6616         |f0467e856f9ee5f05a1815fca47b7787                            |rh.vha@carvalima.com.br                 |         634|             6|N     |                    |                       |N                 |                 |               |
 37|Sinezio Venancio de Souza                         |815          |$2y$10$BD4o7Vi6KyGSj0ZnVFsuM.XiSJsVohqVpIxODXxvSgA5NcJ2kZ3bm|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
114|Armando De Lima Souza                             |3635         |$2y$10$T/9EM/C4V8QBHxAsv.fC7e6osBAxaFSvty2pZZnKnQy3iazzwCdIC|supervisorop.drd@carvalima.com.br       |         634|             5|Y     |                    |                       |N                 |                 |               |
 55|Gleykson de Souza Guimaraes                       |4906         |$2y$10$qd6xQh44KWmwXPnzmJAsJO0zOBfo2Qcn05iT5nCfjyUhKJFExEeNu|trafego1.cgr@carvalima.com.br           |         634|             2|Y     |                    |                       |N                 |                 |               |
 63|JOVANE GONCALO XAVIER DE CAMPOS                   |1932         |$2y$10$N3l2n/pgr6Mo38ogSDUaaeLR2bjfqcnukT9SGwX2TfGheNZOyMQFW|jovane.rh@carvalima.com.br              |         634|             1|Y     |                    |                       |N                 |                 |               |
 95|Douglas  Alexandre  de Souza                      |6465         |$2y$10$6VM4EsiaIJiyk7GYeo5kuuKnbY6l3NbDQamXKNc8PZ0FzQ0FsU5p2|supervisorcoleta.sao@carvalima.com.br   |         634|             3|Y     |                    |                       |N                 |                 |               |
 44|Valdecir M Ferreira                               |1249         |$2y$10$ydfnDfmy8AeYPrBuHs/KOuP0o9vFr8TLkqK2u5O..PbQXX56Ag2H6|valdecir@carvalima.com.br               |         634|             1|Y     |                    |                       |N                 |                 |               |
136|Silmara Costa Leite                               |5208         |$2y$10$iw.34RAeXNlyCs8REZZ8ue5glYxHcnLrfvxvooN/D8b1EkaeYTeCG|administrativo02.cgb@carvalima.com.br   |         634|             4|Y     |                    |                       |N                 |                 |               |
191|Marilza Silveira Martins da Silva                 |7019         |$2y$10$YVwnc2..7maW7BTn9u4fbuIFAM9qyDSBQ8EgoCGxjv42/I1W83uxK|                                        |         634|             1|N     |                    |                       |N                 |                 |               |
 81|Jonatas Vicente de Sousa                          |6419         |288cd2567953f06e460a33951f55daaf                            |trafego04.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
 86|Helen Ferreira Concha                             |6653         |$2y$10$1tbOZszZhX5pe3iZqT8wA.qOOwp4NaD8BYTb0WUm2PY1m6NPNqhWS|supervisorop05.sao@carvalima.com.br     |         634|             3|N     |                    |                       |N                 |                 |               |
 90|Vinicius da Silva Cardoso                         |1068         |2c8432b86e908e5e6804728d29ff041a                            |Supervisorexp.sao@carvalima.com.br      |         634|             3|N     |                    |                       |N                 |                 |               |
187|Luis Felippe da Silva                             |6930         |2f254e66097fd653a5ca4cfdb33be358                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
 88|Antonio Carlos Vitorino                           |665          |84117275be999ff55a987b9381e01f96                            |coordenadorop.sao@carvalima.com.br      |         634|             3|Y     |                    |                       |N                 |                 |               |
108|Cleverson Alves de Souza                          |3517         |b5b0db7f3a77ca4fcf9eca57aa7181ca                            |frota.spo@carvalima.com.br              |         634|             3|Y     |                    |                       |N                 |                 |               |
147|Junia Mara Conceição                              |6212         |c6f8853985d58274c28800cfeb30bddf                            |qualidade.02@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
188|Danilo dos Santos Neves                           |4575         |31784d9fc1fa0d25d04eae50ac9bf787                            |trafego07.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
 77|Jackson Junior Miranda                            |5270         |827ccb0eea8a706c4c34a16891f84e7b                            |trafego04.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
271|Renato da Silva Nascimento                        |4294         |c38c60de91d41b17bdbb5dd970c10c4b                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
124|Milkallaine Rodrigues De Freitas                  |4876         |77bdfcff53815626ecab7f6a1454f007                            |pendencias.cba@carvalima.com.br         |         634|             4|N     |                    |                       |N                 |                 |               |
 73|Shirley da Silva                                  |4080         |354356a539fa013e3dcd0964f193cc67                            |supervisor2.cgb@carvalima.com.br        |         634|             4|N     |                    |                       |N                 |                 |               |
106|Vanessa Borges Marsaro                            |5550         |b71297ae9ada31029287445d779c16af                            |administrativo02.cwb@carvalima.com.br   |         634|             7|N     |                    |                       |N                 |                 |               |
141|Cristina Cristal da Silva Oliveira                |3740         |508c5d64e838badf1cc86b6dbc3beb83                            |financeiro.cgb@carvalima.com.br         |         634|             1|N     |                    |                       |N                 |                 |               |
121|Estefani Lopes Ferreira                           |3738         |16738419b15b05e74e1ecb164430bfa8                            |supervisor5.cgb@carvalima.com.br        |         634|             4|N     |                    |                       |N                 |                 |               |
166|Marilia Gabriele                                  |791          |df7f28ac89ca37bf1abd2f6c184fe1cf                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
146|Flavia Alessandra Batista do Nascimento           |6436         |$2y$10$ysRGzqline6m.p3FDWmNKOXaZ5aYbeBAOTqewMvcU1k9mvv9B2li2|patrimonio03@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
107|Joao Victor Soares                                |6745         |a4ed074907dc9bc3c86cc52904d763e3                            |operacional02.cwb@carvalima.com.br      |         634|             7|N     |                    |                       |N                 |                 |               |
164|Paula Andressa Dias Martins                       |6812         |dc2b690516158a874dd8aabe1365c6a0                            |analistassma.mtz@carvalima.com.br       |         634|             1|N     |                    |                       |N                 |                 |               |
142|Claudia Cristiany Neves de Souza                  |4485         |$2y$10$pxgNHLVWorFMwlGEx0mg.Oz.tE6Lg80HIVw9aVD19MlxTEm8He7oW|contasareceber@carvalima.com.br         |         634|             4|Y     |                    |                       |N                 |                 |               |
125|Leydy Anny Dos Santos Dias                        |1545         |$2y$10$nhQu8z7nEre/FymyRU7nweUOc3PpeffTGmpKYuHLSmZqr8I85tnpO|leydy.rh@carvalima.com.br               |         634|             1|N     |                    |                       |N                 |                 |               |
137|Adriele Alves Garcia                              |1796         |$2y$10$PD0.Lb6wLM8Lv1pxCG4j0uhB3daG16Hi6dyv.LmPPkg8d.OH.nWdC|adm.fedrizze@carvalima.com.br           |         634|            12|Y     |                    |                       |N                 |                 |               |
150|Andre Luiz Pinheiro da Silva                      |1875         |$2y$10$CktQXLyYZ/Oh211Uq1ZPPuI9xEN29BmbNQxvVp3.1yn/TYfLrbNo2|trafego11.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
 65|Fernanda Pulquerio                                |4601         |6d0c932802f6953f70eb20931645fa40                            |supervisormarketing.mtz@carvalima.com.br|         634|             1|N     |                    |                       |N                 |                 |               |
120|Heverton da Silva Barros                          |4754         |$2y$10$OtNSJLi71MeuM/AUPgfUx.4yD9LvN8jnZvmMIaPM/gN8r6uYsWw3C|expedicao10.cba@carvalima.com.br        |         634|             4|Y     |                    |                       |N                 |                 |               |
174|Eva Mendes da Rocha                               |1654         |b3b4d2dbedc99fe843fd3dedb02f086f                            |cobranca1.cgb@carvalima.com.br          |         634|             4|N     |                    |                       |N                 |                 |               |
 85|Lourdes Faria Garcia                              |4884         |c133fb1bb634af68c5088f3438848bfd                            |supervisorop04.cgr@carvalima.com.br     |         634|             2|N     |                    |                       |N                 |                 |               |
149|Jessica Dias                                      |6013         |8fd7f981e10b41330b618129afcaab2d                            |trafego12.cgb@carvalima                 |         634|             1|N     |                    |                       |N                 |                 |               |
197|Lucas Herculano Moura da Costa                    |7017         |$2y$10$lAewxRW8p8oxoKhEoJFGE.202xfpnZT2Yfu4dGIoTNxvJHzcPBj7W|                                        |         634|             2|Y     |                    |                       |N                 |                 |               |
143|Joao Victor Fernandes Martins                     |4066         |$2y$10$jtlJq9Q7lCqJlHkOtww98uTjZpFLm8A7lHyJWaWKdTQMl95FAF/oC|supervisorop.cgb@carvalima.com.br       |         634|             4|Y     |                    |                       |N                 |                 |               |
123|Jehann Fernando Silva Arinos                      |3556         |$2y$10$v7y2PdR3sZXk3SW3oiRjl.mX/t9Cnw44rgasHeXkDNlaPZMkANCEO|expedicao.cba@carvalima.com.br          |         634|             4|Y     |                    |                       |N                 |                 |               |
155|Diogo Antonio da Silva                            |6257         |d4cd91e80f36f8f3103617ded9128560                            |supervisor.adm@carvalima.com.br         |         634|             4|N     |                    |                       |N                 |                 |               |
307|Kamila Ximenes da Silva                           |5939         |$2y$10$2Kgl56UEXPZHcXSL5UALyujyp6ncfBl2/OKDFwg435QCaRlMcNdgG|financeiro01.drd@carvalima.com.br       |         634|             5|Y     |                    |                       |N                 |                 |               |
 66|Edimarcia Dutra                                   |2890         |$2y$10$U3Ts74Kkc5lU1DVVUXbN..U1Y3X5AwdZKEkmO965mkjCzBgn1qZrK|edimarcia@carvalima.com.br              |         634|             1|Y     |                    |                       |N                 |                 |               |
119|Edson Gonçalves Junior                            |4890         |060afc8a563aaccd288f98b7c8723b61                            |eng.rh.cba@carvalima.com.br             |         634|             1|N     |                    |                       |N                 |                 |               |
  2|User                                              |user         |827ccb0eea8a706c4c34a16891f84e7b                            |user@user.net                           |         634|             1|N     |                    |                       |N                 |                 |               |
156|Enedino Nogueira Matos                            |5464         |$2y$10$Qt/2LddGh168Tng3/H0XNeIMfXFmty0fndRCq9oQXzdKBG7GfXlrG|supervisor.trafego.bga@carvalima.com.br |         634|            16|Y     |                    |                       |N                 |                 |               |
411|PATRICIA DE LIMA SAMPAIO                          |8043         |$2y$10$YK2JsfVO9URZGsJrLerGMOH1p5lLeW/ShHqmFuYYw5GlBBSk.Q3XO|                                        |         634|             8|Y     |                    |                       |N                 |                 |               |
 70|Wanessa Lima                                      |6569         |db346ccb62d491029b590bbbf0f5c412                            |coordenadoradm.cgb@carvalima.com.br     |         634|             4|N     |                    |                       |N                 |                 |               |
165|Hubielly Izamar                                   |5446         |$2y$10$S1xXC5R9BTyh1Q3cXTXvMOlS370EyQlzUxraResLUbXWPA4R971si|trafego02.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
126|Yasmim Alencar Sena                               |3614         |$2y$10$3DyNNmR6Ry1GMQ1PqlUiFu2IWwI01vo64qa/GuNLdxQwmEgl9Y0GW|juridico@carvalima.com.br               |         634|             1|Y     |                    |                       |N                 |                 |               |
132|Aryane Tamyres da Silva Ribeiro                   |5249         |$2y$10$im4JUWymIAQK8EOT574C5OsSxZ4qjjOM1/cgLLfZqIgZir4FCQwxe|juridico.adm@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
128|Andre Luiz dos Santos Hermenegildo                |3991         |$2y$10$ow5bW5jgJMH.4yXamK0Ox.1drkg2GyOAdgHHzjWdNg1hBDBpzDi2C|rh10.cba@carvalima.com.br               |         634|             1|Y     |                    |                       |N                 |                 |               |
 78|Murinelly Marini                                  |6727         |$2y$10$9Zy9A6WOSS.55/.80fw21utjJUgaCKD4qftCKNN1NMpQ5QXLLD6Jy|murinelly.marini@carvalima.com.br       |         634|             1|N     |                    |                       |N                 |                 |               |
134|Anderson Luiz Amorim                              |4597         |$2y$10$QHGcVN9tofS.bB7WaPtFF.TM8jTOVVK/hEy4EFA.zgD.ed5FXCAQ2|seguranca.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
133|Cleverson Oliveira Souza Junior                   |5178         |$2y$10$rGuS4hnI6PinX/eGpvoRK.U5Nh7g8e5VB3QkOs4XasGb3mO5FCZGW|analistacomercial.cgb@carvalima.com.br  |         634|             4|Y     |                    |                       |N                 |                 |               |
157|Jiovany Dias Moreira                              |3875         |ccc81a97c1535f9a631b9db584a264e4                            |tst01.mtz@carvalima.com.br              |         634|             1|N     |                    |                       |N                 |                 |               |
159|Michele Aparecida de Almeida                      |5199         |fa7518562603d5c4a7ad69e2e5726f5f                            |rh.ngt@carvalima.com.br                 |         634|             9|N     |                    |                       |N                 |                 |               |
167|Paola Alax Oliveira                               |6674         |4d630f9347177b17ec7a362f19489239                            |rh01.jve@carvalima                      |         634|            10|N     |                    |                       |N                 |                 |               |
170|Ana Carolina Romano                               |4839         |4b0a0290ad7df100b77e86839989a75e                            |coleta.ngt@carvalima.com.br             |         634|             9|N     |                    |                       |N                 |                 |               |
193|Alyni Cassia de Freitas Leite Pimenta             |5847         |$2y$10$OBQ3m2FbM4enUlvkI1CzauHlwBxzHxJJABMsc.OnYcOb9hlu3cMzu|rh16.mtz@carvalima.com.br               |         634|             1|N     |                    |                       |N                 |                 |               |
259|Colaborador ADM                                   |adm          |b09c600fddc573f117449b3723f23d64                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
284|Murilo Rosa Galvao                                |6209         |5446f217e9504bc593ad9dcf2ec88dda                            |analista03.cgb@carvalima.com.br         |         634|             4|N     |                    |                       |N                 |                 |               |
262|EDINAURO ROCHA                                    |1968359      |5c2ef4972e8cc83b2f88f96988adac76                            |                                        |         774|             1|Y     |                    |                       |N                 |                 |               |
270|RONALDO DE LEON LOPES SERVIÇOS MECANICO EPP       |2518         |6ef80bb237adf4b6f77d0700e1255907                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
263|Rodney Fidelis dos Santos                         |7234         |44d47238d7d3e17aa176019eafac82af                            |tst03.mtz@carvalima.com.br              |         634|             1|Y     |                    |                       |N                 |                 |               |
274|FINATTO TRUCK CENTER                              |3297         |7a4bf9ba2bd774068ad50351fb898076                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
285|Rafaela Paiva da Silva                            |4918         |ce65f40e3a20ad19fe352c52ce3bcf51                            |pendencia.roo@carvalima.com.br          |         634|             8|Y     |                    |                       |N                 |                 |               |
273|JONES LIMA DA SILVA                               |2013         |8038da89e49ac5eabb489cfc6cea9fc1                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
277|MARCELO SOARES DA SILVA                           |6164         |2d5951d1e3b31dfb7fd2dcc172df17fd                            |sistemaunitop@consultoria.com.br        |            |             1|Y     |                    |                       |N                 |                 |               |
293|RONALDO PEREIRA DA SILVA                          |7715         |958c530554f78bcd8e97125b70e6973d                            |sistemaunitop@consultoria.com.br        |         298|              |Y     |                    |                       |N                 |                 |               |
294|JOSE LINO SILVA DE JESUS                          |7719         |48d4167a0f3bc10686a1ad20a8008c73                            |sistemaunitop@consultoria.com.br        |         298|              |Y     |                    |                       |N                 |                 |               |
267|Gabriel Alfard Azevedo dos Santos                 |7448         |987b75e2727ae55289abd70d3f5864e6                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
290|Murilo Rosa Galvao                                |6209         |5446f217e9504bc593ad9dcf2ec88dda                            |analista03.cgb@carvalima.com.br         |         634|             4|N     |                    |                       |N                 |                 |               |
313|abastecimento (clone)                             |abastecimento|202cb962ac59075b964b07152d234b70                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
175|Gabriel da Silva Oliveira Brochado                |6035         |4639475d6782a08c1e964f9a4329a254                            |analista1.drd@carvalima.com.br          |         634|             5|N     |                    |                       |N                 |                 |               |
151|Ariane Matos                                      |5932         |3d191ef6e236bd1b9bdb9ff4743c47fe                            |trafego17.cgb@carvalima                 |         634|             1|N     |                    |                       |N                 |                 |               |
257|Evaldo Junior Sampaio                             |5994         |edb446b67d69adbfe9a21068982000c2                            |supervisorop2.cgb@carvalima.com.br      |         634|             4|N     |                    |                       |N                 |                 |               |
135|Roselino Gonçalves de Arruda                      |6188         |30893a5eb454815e3bf4a3406b1b80c0                            |manutencao@carvalima.com.br             |         634|             1|N     |                    |                       |N                 |                 |               |
347|Rogério Francisco de Jesus                        |3896         |4175f2ebb265d58c6d8877841d016d08                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
339|Beatriz da Cunha Teixeira Coelho (clone)          |5799         |de01d76e793fec3fba32f4401a45fb20                            |supervisorop.jip@carvalima.com.br       |         634|            15|N     |                    |                       |N                 |                 |               |
342|Gleiciele Rodrigues de Oliveira                   |8018         |ba638ebf561da3b2313e5d7955c55ea9                            |rh01.vha@carvalima.com.br               |         634|             6|N     |                    |                       |N                 |                 |               |
254|Queite Cristina Alves Lavareda                    |7439         |$2y$10$k/W15zHmRbAILD7dBJuJbeww2wZpH3h5tBZGs0mBPU/X5g0/LxcXu|rh.pvh@carvalima.com.br                 |         634|            17|Y     |                    |                       |N                 |                 |               |
169|Dyego Nathanael Heymanns                          |4902         |$2y$10$u/LVf7PS9CYWwCrjg6.xoeMD.UdS49VPRPExoYaaB6nnnp1/Hl8rW|gerencia.jve@carvalima.com.br           |         634|            10|Y     |                    |                       |N                 |                 |               |
252|Vander de Moraes Camargos                         |6376         |a6869a35be893ac2d85989c5cd605539                            |supervisor2.roo@carvalima.com.br        |         634|             8|N     |                    |                       |N                 |                 |               |
250|Paulo Roberto Loubet                              |3262         |$2y$10$RPLt.UcSWBainXoZZ8I.zugv2MadCcNjENjbAPjge9iLQnBnwIwqC|expedicao8.cgr@carvalima.com.br         |         634|             2|Y     |                    |                       |N                 |                 |               |
314|Andre Lucas C da Silva                            |7637         |$2y$10$TKvuH0/DR1/z5Sbi6idL6eHDe/MA9UEdMkGXR7kqmKujim3r6yqla|supervisorop4.cgb@carvalima.com.br      |         634|             4|Y     |                    |                       |N                 |                 |               |
158|Rosiene Oliveira dos Santos                       |6291         |$2y$10$dqEN0QRqRtKGILBHY.pvTepWV..oj3hZ6Vlp1NyyLjDiRDDacu/bu|lideradm.roo@carvalima.com.br           |         634|             8|Y     |                    |                       |N                 |                 |               |
286|Joao Daniel Mazarin                               |7334         |$2y$10$q2Xjv6g35WHpaE1pBYEGqOAOev327snW0pCkXTxGkhnpKykGNt4Qa|analista01.ldb@carvalima.com.br         |         634|             7|N     |                    |                       |N                 |                 |               |
412|Mario Felipe Junior                               |8325         |$2y$10$F4qfwkpLiLmfk/GaDy6ptevihcQkOf7k3qv7vG7trhPCMrFX9glpi|marketing.@carvalima.com.br             |         634|             1|Y     |                    |                       |N                 |                 |               |
291|Maria Clara Manhani                               |7659         |58b4095fb5335282cc3fde57c643da38                            |qualidade.03@carvalima.com.br           |         634|             1|N     |                    |                       |N                 |                 |               |
288|Bento Geronimo da Silva Junior                    |7383         |$2y$10$Oh2HeOFrOqzah0sBoIfaYOZFAZdGtHau2y8ULEHJuYHofNBP6dfiu|controladoria.12@carvalima.com.br       |         634|             1|Y     |                    |                       |N                 |                 |               |
248|Gleyton Aparecido da Silva                        |5392         |$2y$10$NjxeE1lQGk0qUOTS98Rc1OtpwqcR0rsnICwZ6IR6JRnzdHPruGi1C|gerencia.cgb@carvalima.com.br           |         634|             4|Y     |                    |                       |N                 |                 |               |
264|Angelica Gajardoni                                |7487         |$2y$10$fbqMtr0aBDzinq99dfL/fua7fnzR5yUxCSERJn0oxwXHbICqNptOG|marketing@carvalima.com.br              |         634|             1|Y     |                    |                       |N                 |                 |               |
 38|Matheus Eduardo                                   |5674         |$2y$10$Xs1nrwc3O5DsE.m8sKU8tOeZYzUWrI3rfD/.VrmVMHhG4vxlWfe/O|trafego03.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
297|GERSON LIMA DA CRUZ                               |101          |814f06ab7f40b2cff77f2c7bdffd3415                            |sistemaunitop@consultoria.com.br        |         298|              |N     |                    |                       |N                 |                 |               |
168|Lucas Rodrigo Fagundes                            |6394         |$2y$10$Cx3WR/hObS99QyHmT3cE7ub5KDaX7dI5dHNJjENEANQlpCoOuX6Om|expedicao01.jve@carvalima.com.br        |         634|            10|Y     |                    |                       |N                 |                 |               |
292|Wilson Rodrigo de Oliveira                        |7183         |$2y$10$kOSPYZn7Sa4qnCNQkU1zMe5J3WP1Z21OtTbso9CQnAsWauT0pHpym|                                        |         634|             2|Y     |                    |                       |N                 |                 |               |
413|.                                                 |8            |28f248e9279ac845995c4e9f8af35c2b                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
153|Anderson Dias Ferreira                            |6746         |8c249675aea6c3cbd91661bbae767ff1                            |operacional02.cwb@carvalima.com.br      |         634|             7|N     |                    |                       |N                 |                 |               |
296|Murillo Mariano Cosmo                             |7716         |$2y$10$sDpbmWMiKq2.LtbyVXiJaezEK9B5GoD5KxzhHsUuDbX3tC7ZDaWES|analista01.roo@carvalima.com.br         |         634|             8|Y     |                    |                       |N                 |                 |               |
225|Vanessa Toniote                                   |7296         |$2y$10$0Yl4FdRR9dmuXwToZEla9OHMdfp3Gz/9aaBzM3dRqWAmKjPikwt7m|rh01.jve@carvalima.com.br               |         634|            10|Y     |                    |                       |N                 |                 |               |
161|Jeinne Gabrielly Salazar                          |4900         |b9cfe8b6042cf759dc4c0cccb27a6737                            |financeiro.ngt@carvalima.com.br         |         634|             9|N     |                    |                       |N                 |                 |               |
172|Hemilli Specht da Silva Altero                    |6165         |36452e720502e4da486d2f9f6b48a7bb                            |analista.rh.drd@carvalima.com.br        |         634|             5|N     |                    |                       |N                 |                 |               |
282|SF ACESSÓRIOS                                     |49390        |$2y$10$iiHvntnQGQ5VXXOrXwtr5uBDqVRdX.Hs9jUZPcaRtw3bHPtpHjTZa|sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
261|WANDERLEY SOARES                                  |2783332      |bca2ee929e829fa62b049c55b648da39                            |                                        |         774|             1|Y     |                    |                       |N                 |                 |               |
256|Tamiris Garcia Moreira                            |7499         |a57453cd07a4af1e565dccd280691bf9                            |supervisor.trafego.cgr@carvalima.com.br |         634|             2|N     |                    |                       |N                 |                 |               |
281|Sebastião da Rocha Freita                         |2565         |e6acf4b0f69f6f6e60e9a815938aa1ff                            |sistemaunitop@consultoria.com.br        |         768|             4|N     |                    |                       |N                 |                 |               |
163|Manuel Messias Araujo Filho                       |6244         |21f4c3b5591da245af90a2fd52fa1a55                            |supervisorop.roo@carvalima.com.br       |         634|             8|Y     |                    |                       |N                 |                 |               |
326|Lucivaldo Lopes de Oliveira                       |4971         |a600bd172fcabd688500dac58ebda3a0                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
223|Millena Cristina Barbosa Xavier Marques           |7282         |21c2c25487b9f30af6c4a9f6f10b09b2                            |analista01.cwb@carvalima.com.br         |         634|             7|N     |                    |                       |N                 |                 |               |
328|Sidecley Arruda Brandão                           |4584         |6ba1085b788407963fe0e89c699a7396                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
323|Breno Luiz de Oliveira                            |3719         |9e740b84bb48a64dde25061566299467                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
322|Allyston Paullo de Souza Vieira Arruda            |7385         |ce1aad92b939420fc17005e5461e6f48                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
324|Dagson Garcia da Silva                            |6146         |ba053350fe56ed93e64b3e769062b680                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
334|Andre Garcia Marvulle                             |7686         |fbd3230f4d8dcf11a06b19fd0cfd8ef1                            |                                        |         751|             1|Y     |                    |                       |N                 |                 |               |
325|Diego Marques de Melo                             |5125         |37f65c068b7723cd7809ee2d31d7861c                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
180|Amandha Cristina Santos Estral                    |6944         |ba51e6158bcaf80fd0d834950251e693                            |administrativo03.cgb@carvalima          |         634|             4|N     |                    |                       |N                 |                 |               |
337|Pamela Martins Bandeira                           |7911         |11833d4b4ec685c371ae6d1a65cc341e                            |trafego2.cgr@carvalima.com.br           |         634|             2|Y     |                    |                       |N                 |                 |               |
348|Uedismar Alves da Silva                           |3559         |f11bec1411101c743f64df596773d0b2                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
344|Zaqueu Ferreira Pimentel                          |2613         |6aed000af86a084f9cb0264161e29dd3                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
346|Jorcilei Paiva                                    |7770         |c802ceaa43e6ad9ddc511cab5f34789c                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
182|Raquel Carvalho da Silva                          |6587         |2e2c4bf7ceaa4712a72dd5ee136dc9a8                            |administrativo01.cwb@carvalima.com.br   |         634|             7|N     |                    |                       |N                 |                 |               |
359|Everton dos Santos Ribeiro                        |7432         |48bea99c85bcbaaba618ba10a6f69e44                            |supervissorop07.sao@carvalima.com.br    |         634|             3|Y     |                    |                       |N                 |                 |               |
363|Ricardo Soares                                    |7442         |$2y$10$EUPPbQyDajQG6JqbNnl6.OFWYSrZOn1Dz127UQOiHPPB9Q4nLm9MW|Supervisorop06.sao@carvalima.com.br     |         634|             3|Y     |                    |                       |N                 |                 |               |
353|Lidiane Chamorro                                  |5444         |$2y$10$QWTesFPxTbFHMe1DsmYhHuoUVtztluuwgk2PoT3SIDFeoy8bNp58O|tst.adm.cgr@carvalima.com.br            |         634|             2|Y     |                    |                       |N                 |                 |               |
403|FRANCIELLA DE OLIVEIRA NEVES                      |6768         |9a84a0448b11c17666c7e5db74042219                            |                                        |         634|             8|Y     |                    |                       |N                 |                 |               |
320|Luis Roberto Alves                                |4275         |eaa1da31f7991743d18dadcf5fd1336f                            |                                        |         634|             7|Y     |                    |                       |N                 |                 |               |
350|Arthur Henrik Gomes dos Santos                    |7447         |9e6adb1432c4a75a33d48693328e4159                            |trafego11.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
354|Thaiany Eliza de Souza                            |3014         |$2y$10$4mR0t22lNbP0yWPuFVL9he7cmuHFmRkQ9OWkTup5hsfyPhwD1EBrW|rh.cgr@carvalima.com.br                 |         634|             2|Y     |                    |                       |N                 |                 |               |
366|Valdeson Vieira                                   |6967         |$2y$10$a4QmGDbaX9rPUd20RieCiumhlvuKi.GMFoMUbHrMfLUfkYyx0n.T.|almoxarifado7.carvalima@hotmail.com     |         634|             1|N     |                    |                       |N                 |                 |               |
382|Geandro Fernandes da Silva                        |8251         |c8ecfaea0b7e3aa83b017a786d53b9e8                            |supervisorcomercial.cgr@carvalima.com.br|         634|             2|N     |                    |                       |N                 |                 |               |
360|Julio Thiago Polidori de Carvalho                 |6454         |$2y$10$eAXh4o/Zpk3aO0sm5mM4xO0JrGa0zoNvOo/PuAXDwIz2r5x.S1bYu|pendencias.sao@carvalima.com.br         |         634|             3|Y     |                    |                       |N                 |                 |               |
358|Diego Candido Bezerra                             |3193         |4bb5eb7d019392bd797c471baa56d294                            |supervisorop01.sao@carvalima.com.br     |         634|             3|Y     |                    |                       |N                 |                 |               |
318|Marcos Vinicius Matos Sousa                       |6957         |$2y$10$7I1VI9.riPIHAfzSNZxSZeLTjjBUbyHOOgB045yKSghjFw5hZbHNW|qualidade.05@carvalima.com.br           |         634|             1|N     |                    |                       |N                 |                 |               |
343|Fernanda Tamara Gonçalves Souza Silva             |7933         |18de4beb01f6a17b6e1dfb9813ba6045                            |rh.drd@carvalima.com.br                 |         634|             5|N     |                    |                       |N                 |                 |               |
 35|Mellina Borges                                    |5050         |202cb962ac59075b964b07152d234b70                            |trafego14.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
355|Fernanda Gabrielly Sanches                        |2271         |a376033f78e144f494bfc743c0be3330                            |supervisorcomercial.cgr@carvalima.com.br|         634|             2|N     |                    |                       |N                 |                 |               |
352|Leandro da Silva Santos                           |7744         |$2y$10$l3vEaN0XwKq51w5/RP7hqej3KpB8B8qP4Z.DbhLcGj7QirBoTItLe|encarregado4.cgr@carvalima.com.br       |         634|             2|Y     |                    |                       |N                 |                 |               |
349|Mikael Jose da Silva Rondon                       |5613         |45624a44b89793087e9ef4d076018adb                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
369|Ben-Hur Pereira Borges dos Santos                 |6213         |e275193bc089e9b3ca1aeef3c44be496                            |                                        |            |             1|Y     |                    |                       |N                 |                 |               |
317|Thais Eloiza De Souza Rodrigues                   |7891         |$2y$10$.djiy6UJgfa.4csd6KUX3.rW7RfWz6WrTCl8AO6F93QSb8dWLAmIa|qualidade.00@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
319|Jorge Luis Ferreira                               |6709         |$2y$10$eIdwMiLwled5kAztxYSyXeRXp.V66fNwTrOHkdXpY8ej51ViGLol6|EXPEDICAO02.CWB@CARVALIMA.COM.BR        |         634|             7|Y     |                    |                       |N                 |                 |               |
 87|Luiz Carlos de Oliveira Reis Junior               |6267         |$2y$10$tkZ99dDCZVnSlWXbKDIkBeQo3EwS7m..ELYQsXSpMjgFaIAkUQGZW|supervisorparcerias@carvalima.com.br    |         634|             3|N     |                    |                       |N                 |                 |               |
333|Barbara Balesteiro                                |6268         |$2y$10$czreI1Kd47mvinYE4APk5OO52VIyRS7XGk7zNLQBM4t25BszVvT0q|analista08.mtz@carvalima.com.br         |         634|             1|Y     |                    |                       |N                 |                 |               |
381|Itamar Junior Ramos Figueiredo                    |8127         |$2y$10$wfZS7Y/2mey60g7WzATCNObO2WjOXYyH4Rpon0Xu7rXdG3AyokLG2|                                        |            |             1|Y     |                    |                       |N                 |                 |               |
345|Jesielson Oliveira de Souza                       |8058         |5ee5605917626676f6a285fa4c10f7b0                            |oficina@carvalima.com.br                |         634|             1|Y     |                    |                       |N                 |                 |               |
331|Pâmela Martins Bandeira                           |7911         |11833d4b4ec685c371ae6d1a65cc341e                            |                                        |            |              |N     |                    |                       |N                 |                 |               |
336|Thaise Fabiane K Alves                            |7983         |$2y$10$W5ytVTCM4VxbQFdIY/GWR.sgdmgWJDpouQmwThmJvb.Vkf1uRQpau|novosnegocios.mtz@carvalima.com.br      |         634|             1|Y     |                    |                       |N                 |                 |               |
406|Fernanda Gabrielly Sanches Gregorio               |8700         |3b036b877a6a074d7dbfc706fe868c1d                            |supervisorcomercial.cgr@carvalima.com.br|         634|             2|N     |                    |                       |N                 |                 |               |
332|Tairana Hugo Lima                                 |7998         |$2y$10$8k0WaHW2Qg1odW/S3X7ak.bzlhu0pDIf5WSIG.HvHSNnGA4WFQPi.|rh.ngt@carvalima.com.br                 |         634|             9|Y     |                    |                       |N                 |                 |               |
362|Priscila Ferreira Mariano                         |6283         |$2y$10$K0Q.XN.SgwRdfeY1H8lik.Fp43zyWLF3wJeeTdClMu.5SbVDf9FNe|financeiro.sao@carvalima.com.br         |         634|             3|N     |                    |                       |N                 |                 |               |
327|Matheus Henrique Santos de Siqueira               |4870         |3c565485bbd2c54bb0ebe05c7ec741fc                            |                                        |            |             1|N     |                    |                       |N                 |                 |               |
335|Marcos Ferreira da Silva                          |3787         |f3175210f90bfc7ea82901db0ef7452f                            |                                        |         751|             1|N     |                    |                       |N                 |                 |               |
370|Luis Henrique Camargo dos Santos                  |7528         |f6f154417c4665861583f9b9c4afafa2                            |analistagestao2.sao@carvalima.com.br    |         634|             3|N     |                    |                       |N                 |                 |               |
361|Manoel Jackson Nascimento de Melo                 |1467         |$2y$10$eqBxtI3ELbdvy66K2DJXVuGoqZ58UN9Qrv76vbgSVZP4c129sEMZ2|supervisorop03.sao@carvalima.com.br     |         634|             3|Y     |                    |                       |N                 |                 |               |
341|Valdomiro Junior                                  |5774         |$2y$10$TjxfTMBBfaD1LzMXaoJjp.FQ5fRguk3iN2v0r7RRKBjh3I7Ky8diG|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
357|Isamara Silva Mougenot                            |7349         |ad8d3a0a0f0a084a97fad357c649438c                            |sac03.mtz@carvalima.com.br              |         634|             2|Y     |                    |                       |N                 |                 |               |
376|Mellina Borges (clone)                            |5050         |202cb962ac59075b964b07152d234b70                            |trafego14.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
367|Paulo Bustamante (clone)                          |6441         |226292e677fe34180820bd2b75f45c5f                            |almoxarifado4.cgb@carvalima.com.br      |         634|             1|N     |                    |                       |N                 |                 |               |
368|Paulo Bustamante (clone)                          |6441         |226292e677fe34180820bd2b75f45c5f                            |almoxarifado4.cgb@carvalima.com.br      |         634|             1|N     |                    |                       |N                 |                 |               |
399|Lucas Henrique Gomes                              |8299         |acef5cc0bd5a0c190494e34ea4b04811                            |                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
392|Wendell willian da silva                          |4642         |63c17d596f401acb520efe4a2a7a01ee                            |manutencao@carvalima.com.br             |         634|             1|Y     |                    |                       |N                 |                 |               |
414|Igor Rodrigues Chaves                             |8041         |1a07bcc79f21590b3ed2622d5807bdd0                            |tst.rh.cgr@carvalima.com.br             |         634|            17|Y     |                    |                       |N                 |                 |               |
258|Osmary De Souza Malheiros                         |5656         |ae5eb824ef87499f644c3f11a7176157                            |lidernoturno01.cgb@carvalima.com.br     |         634|             4|N     |                    |                       |N                 |                 |               |
387|Mario Dos Anjos                                   |7252         |$2y$10$PStpfyCOiEtLKR47LKxdpOzkc9lpBEFr0HkFtkirPcXTkkWu6SbTK|marketing4@carvalima.com.br             |         634|             1|N     |                    |                       |N                 |                 |               |
138|Henrique Douglas da Silva                         |2869         |e2c61965b5e23b47b77d7c51611b6d7f                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
112|Jozino Francisco dos Santos                       |2286         |$2y$10$Ifif4/C.DI7g9vgsa10XXuZO2c0osEhJAYi8Jd2U4cKm7isL8ViRe|frota.spo@carvalima.com.br              |         634|             3|Y     |                    |                       |N                 |                 |               |
 19|Renato Pinto de Matos                             |29956285153  |$2y$10$Lf5Aku8/K4aJ6GBYo8gFeuI1cM7F0Hd22WsOCWRDEk8TPv56n5p6y|suprimentos.mtz@carvalima.com.br        |         634|             1|Y     |                    |                       |N                 |                 |               |
379|Leandro Silva leite                               |8168         |3f998e713a6e02287c374fd26835d87e                            |coletas04.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
372|Fabio Agostinho da Silva                          |8026         |e6872f5bbe75073f8c7cfb93de7f6f3a                            |sistemaunitop@consultoria.com.br        |         768|             1|N     |                    |                       |N                 |                 |               |
418|Tiago Ruan Da Silva Biasuz                        |2789         |$2y$10$TtOX4irrJiICthRzL2Fjne5PS.PnhO0qF5efCRD4a9xcBEIKMzouS|supervisorop5.cgb@carvalima.com.br      |         634|             4|Y     |                    |                       |N                 |                 |               |
401|Paulo Roberto Barcellos Junior                    |8312         |e046ede63264b10130007afca077877f                            |                                        |         634|             7|N     |                    |                       |N                 |                 |               |
 23|Mario Cintra                                      |6037         |$2y$10$jlMgnUyUtezJ2HCEgwxU0u2nIzPXkWTNn9kqjlj4xuOY7hcY/Bjsi|frota9.cba@carvalima.com.br             |         634|             1|Y     |                    |                       |N                 |                 |               |
373|Helber Bejarano dos Santos                        |8064         |fa40b3850046b362217c121a274720fd                            |sistemaunitop@consultoria.com.br        |         768|             1|N     |                    |                       |N                 |                 |               |
116|Marcos Antonio da Silva                           |3501         |$2y$10$P8NGNaI2N.sBp5OoVx/.nOF9sRQFcITQDhwQ7CbY1qGbODZMjF7ta|ti@carvalima.com.br                     |         634|             1|Y     |                    |                       |N                 |                 |               |
397|Yuri Rodrigo de Souza Ferreira de Paula           |8301         |$2y$10$G9C2rTrgJGRrDXAYukX98uKg8xQnoLYu2/UfkllFO6GyGPF2BBhoW|                                        |            |             1|Y     |                    |                       |N                 |                 |               |
404|Lucas Nunes do Nascimento                         |6675         |af61f719e08b92d6086996d24fad830c                            |supervisorfiliais01@carvalima.com.br    |         634|             1|N     |                    |                       |N                 |                 |               |
378|Igor Matheus Nunes da Silva                       |8202         |7c6f8dba4a02404f97b5953d2c4172a7                            |                                        |         768|             1|N     |                    |                       |N                 |                 |               |
422|Henrique Hernani Santiago Ferreira                |8218         |08fd71a60fbbe8210cd5853c56d1cd84                            |qualidade.12@carvalima.com.br           |         634|             1|N     |                    |                       |N                 |                 |               |
253|Maria Eduarda de Lima                             |7242         |$2y$10$p2aZFljHIyZjSwyMwj.8CuQ1oMNe5ORorgNhjmb/KRKtFfyE9nWe2|financeiro03.sao.carvalima@hotmail.com  |         634|             3|Y     |                    |                       |N                 |                 |               |
407|Ivan Ricardo Atunes                               |9988         |$2y$10$RgqoL9IATY.fAX1C0nMFMOIqNteMoCveSmnBIb2e/EPdEGtjWlODu|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
371|Fernando Almeida Furtado                          |5571         |$2y$10$3HtODD75A8VcMa6D4q1B2OYBlcMdTcAFQSPhkv6MTpR5l2oFh6lwq|5571                                    |         634|             1|Y     |                    |                       |N                 |                 |               |
415|Lidiane da Silva Fuzi                             |8293         |$2y$10$dxXo6IPM79MJvYCNd8aGD.hFyK5oXwZth.P4KzD58eipxHkdskLIG|rh01.vha@carvalima.com.br               |         634|             6|N     |                    |                       |N                 |                 |               |
388|Tarsis  Franca de Campos                          |7569         |bb48ec536703ce222bf751a441de4a0f                            |ti09@carvalima.com.br                   |         634|             1|Y     |                    |                       |N                 |                 |               |
338|Kariny de Oliveira                                |7299         |$2y$10$tajDIqCLtN0zjKSvUwqZXeS3KHvuwfl6gE.AizdcnNW8YntNMIaSC|patrimonio03@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
295|Ana Karolline de Lima Matos                       |6261         |$2y$10$y6yk09RvksAdqy8fHsh.8OvEtNFi9fPTBUyi6.bmvFoLuQ..tpcY6|supervisorope.cgb@carvalima.com.br      |         634|             4|Y     |                    |                       |N                 |                 |               |
394|Rodrigo  Lima                                     |4176         |$2y$10$pRAlqsZTwTEy1YB9TSmxLeOwpd/jT.jbOLMK5hRDv3L05IMNkHue6|supervisorcomercial.cgb@carvalima.com.br|         634|             4|N     |                    |                       |N                 |                 |               |
393|0                                                 |065          |7dd2ae7db7d18ee7c9425e38df1af5e2                            |                                        |         634|             7|N     |                    |                       |N                 |                 |               |
160|Angélica Suellen Lopes                            |6473         |$2y$10$4gSdtIVuLo0e9TyNzjjmdOsgqp8qLKztx/PPAjTY..fK0GNcA1xVq|financeiro02.sao@carvalima.com.br       |         634|             3|Y     |                    |                       |N                 |                 |               |
389|Sabrina Vieira de Sousa                           |6508         |58d2d622ed4026cae2e56dffc5818a11                            |comercialadm.cgb@carvalima.com.br       |         634|             1|Y     |                    |                       |N                 |                 |               |
384|Brenno de Souza Silva                             |7289         |$2y$10$ETpx9/ke8B2VfbLdnXNazevFAGWB1Nw8QXGSNQ9GfaBsNK4E3OS86|ti08@carvalima.com.br                   |         634|             1|Y     |                    |                       |N                 |                 |               |
385|Eduardo Correa da Silva                           |6619         |$2y$10$5aeX5lZQepm0t/QnVtPTWehgYTGessuFPdzMjrq/3yZzw/Kf/xDB6|ti02@carvalima.com.br                   |         634|             1|Y     |                    |                       |N                 |                 |               |
356|Rafael Favaro Vargas                              |6917         |$2y$10$.8fcLjvDEZ7WngwYY.ya/uldA3NnZt8csNzUYe6fl6bqpiP4hbQ2i|analista01.cgr@carvalima.com.br         |         634|             2|N     |                    |                       |N                 |                 |               |
380|Vinicius Vieira do Carmo                          |8132         |$2y$10$Zn80/jgh3uwNkWGM5yu0A.X59qn4Xb72hcstMDvCLYZKREwjQlPdG|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
391|Eduarda Lima de Brito                             |5094         |$2y$10$EekdUBj5JgB9SVi3tIYM7O.s8QL5Dopx.IHQ3tSbGmmrcuIf.jsHS|trafego4.cgr@carvalima.com.br           |         634|             2|Y     |                    |                       |N                 |                 |               |
377|Emerson Antonio dos Santos                        |7658         |e77910ebb93b511588557806310f78f1                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
374|Willian Robson                                    |2873         |$2y$10$vRDhOdKIgRTP51QcbDTq8.mr5rmarSgXZdiHY.yeumV2StzmqnKXG|coordenador.trafego@carvalima.com.br    |         634|             1|Y     |                    |                       |N                 |                 |               |
375|Micheli                                           |55           |33bdf955c5d92555d8977eff1c5815c5                            |                                        |         634|             4|N     |                    |                       |N                 |                 |               |
402|Carlos Alberto da Silva                           |7549         |$2y$10$nHDAZolzkOXzI53RMUmD4.2Mkv5Jv8UVLDy5pwOFJec55yAq2IBUu|tst.rh02.sao@carvalima.com.br           |         634|             3|Y     |                    |                       |N                 |                 |               |
386|Thiago Strobel Duarte                             |5206         |$2y$10$fjF/D3aZ6hLj0d9TeQ6v6.ywppbTM6VjZZyS14YyHcTx8aFFl8gNi|ti06@carvalima.com.br                   |         634|             1|Y     |                    |                       |N                 |                 |               |
405|Lorena Cristina de Barros                         |7618         |$2y$10$dCCUkpqMd6HGrR.13tNQYOzPZY8R8aOxDGvqm93ktpHpA7SSS.GWm|sac01.mtz@carvalima.com.br              |         634|             1|N     |                    |                       |N                 |                 |               |
420|Elaine Cristina Paniago Rodrigues                 |8093         |$2y$10$2otXhpYIBa.snj7XubJWLegug3oRHJIr7zlI.FQRI8T10NLEznZtm|qualidade@carvalima.com.br              |         634|             1|Y     |                    |                       |N                 |                 |               |
396|Kamila Ferreira Mello                             |6956         |$2y$10$22BsCsIpqqCeKHf2MBzlJu3Rfch9p1QJiRuv3HILA8YgfmWD7/VIG|lideradm.mtz@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
390|Juberth Silva de Farias Prado                     |6775         |$2y$10$NjrifXKzB1Jf8p8X12Bc7ODWcG3Sm2tryQwLGLlT/JU8QYsNWkXxG|supervisorop.cgb@carvalima.com.br       |         634|             4|Y     |                    |                       |N                 |                 |               |
424|Lucimara dos Santos Passos Correa                 |5689         |8ba6c657b03fc7c8dd4dff8e45defcd2                            |                                        |         634|             7|Y     |                    |                       |N                 |                 |               |
321|Vanderley Aparecido Ventura                       |7914         |$2y$10$A22HliSm0uT6cjqYONT18ebYCOCRN3AQDXDDnc35sOx5U6Y02s9C2|supervisoroficina.cgr@carvalima.com.br  |         634|             2|Y     |                    |                       |N                 |                 |               |
448|Dalton da Silva Pereira                           |8642         |$2y$10$CNbf5E29igCkWs2LnlFMWeZMLpHU8SNiXPJAVoB2Q3WFxRmS87tN.|trafego2.cgr@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
447|Arthur Henrik Gomes dos Santos                    |8701         |$2y$10$6niuijQSb22bJ7jQDMf49uc.weMdCrVFVBTvpb50LFMugnlaZ2L.G|trafego22.cgb@carvalima.com.br          |         634|             1|Y     |                    |                       |N                 |                 |               |
441|Flavio de Sousa Neres                             |3139         |$2y$10$SVMHeTdtR9zzRhs6cx6PbuLoFQeC4W0Qbfw0w8w/OuFRDJSsc5VC.|liderrecebimento.sao@carvalima.com.br   |         634|             3|Y     |                    |                       |N                 |                 |               |
430|Milany Ramires da Silva Fernandes                 |6491         |cd17d3ce3b64f227987cd92cd701cc58                            |                                        |         634|            15|Y     |                    |                       |N                 |                 |               |
 45|Emanoelle de Paiva Rodrigues Pereira              |5449         |202cb962ac59075b964b07152d234b70                            |trafego05.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
451|JESSYKA JANINI SOUZA BELIZARIO                    |6482         |$2y$10$uhOQYn4GkMpk2VXWRon1qOJHcvCWN1z3UfBRMK9GUqLvl5oB0TmoS|rh02.mtz@carvalima.com.br               |         634|             1|Y     |                    |                       |N                 |                 |               |
437|Marcos Antonio do Carmo Junior                    |4854         |$2y$10$4C2hiFYT.ChRg6EpnV1uTudF/uFl.0QmtZgMvOjJqjZqaGtUMeUS.|supervisorop2.cgb@carvalima.com.br      |         634|             4|N     |                    |                       |N                 |                 |               |
446|Nayara Gomes de Oliveira                          |7619         |$2y$10$msKGYpLNGl79aNfypOjI1.1ak60MCV2VVzVv9uV8PtcWnMeKe85wy|rh19.mtz@carvalima.com.br               |         634|             1|N     |                    |                       |N                 |                 |               |
428|Rafael Gil Carneiro                               |4882         |c348616cd8a86ee661c7c98800678fad                            |supervisorop01.cwb@carvalima.com.br     |         634|             7|Y     |                    |                       |N                 |                 |               |
431|Rafae                                             |488          |c348616cd8a86ee661c7c98800678fad                            |                                        |         634|             7|N     |                    |                       |N                 |                 |               |
440|Cassio Gama de Sousa Duque                        |3617         |$2y$10$HYelRxNdMBSb03BAALLGEeNftBS/tCpE.T3nHABW8iKLopTXBiq8q|lidermlv.sao@carvalima.com.br           |         634|             3|Y     |                    |                       |N                 |                 |               |
109|Willian Jackson Sousa Silva                       |4685         |$2y$10$Mfu005GYHhRLZxtndr/ImugoiLoVNZMkxa4XpylcCp1jRSSzdguDG|frota.spo@carvalima.com.br              |         634|             3|Y     |                    |                       |N                 |                 |               |
427|Sidnei Aparecido Rodrigues                        |8398         |2e64da0bae6a7533021c760d4ba5d621                            |                                        |         634|             7|N     |                    |                       |N                 |                 |               |
442|Marlon Ferreira Damacena                          |8663         |1f9702dbc66344013ffb884419665816                            |supervisor.frota@carvalima.com.br       |         634|             1|N     |                    |                       |N                 |                 |               |
436|THIAGO DA SILVA OJEDA                             |8639         |e3978ba7ecdecc63be5f5bf0281a0ed6                            |sistemaunitop@consultoria.com.br        |         768|             1|Y     |                    |                       |N                 |                 |               |
445|Paula Isabel La Rosa Palma                        |6167         |$2y$10$l53kDQoC/PAaH8yg1Q1En.xVn/vrn59B/nrRbZvzTLRTDYh3eWM3a|liderope10.cgb.carvalima@hotmail.com    |         634|             4|Y     |                    |                       |N                 |                 |               |
454|William Gabriel Bezerra da Rocha                  |8691         |e22c686bc771d5872150738b15f3e533                            |manutenc@carvalima.com.br               |         634|             2|N     |                    |                       |N                 |                 |               |
383|Itamar da Cruz Barros                             |6734         |$2y$10$mMQlKxC9yS29AE65nlgFg.9cSSUO19x6/AqoMO2IKAZRGIoCGa4Um|ti07@carvalima.com.br                   |         634|             1|Y     |                    |                       |N                 |                 |               |
439|Adriano da silva vitorino                         |4141         |$2y$10$jk6EBn/U0JVw5j7y/2WaA.QJ9E9UiyFPREdEQpDuBIdsf9J6v.5tW|                                        |         634|             3|Y     |                    |                       |N                 |                 |               |
 58|Marcelo Almeida Souza                             |47453621534  |$2y$10$zvm/cglMo9uwsLeRrAYNFO/PDuzeY7MZHyfO3b1wcszKk2WoYMmpq|controladoria.04@carvalima.com.br       |         634|             1|N     |                    |                       |N                 |                 |               |
425|Renata Christina de Andrade                       |5690         |$2y$10$9CZt0Vq5Sa7An/xYfrqFVuQiCQ2nA4ArcHUgaE9JQrBicXk0OCzym|                                        |         634|             7|Y     |                    |                       |N                 |                 |               |
434|Stephanie Lee Ramos Lima                          |8404         |$2y$10$td4vt/iaycIL.X9p.33UuOURJ3Ii6dcLjYzidxEoeyFTAC3o5Z9jq|supervisor.nce@carvalima.com.br         |         634|             1|N     |                    |                       |N                 |                 |               |
127|Weverton Da Silva Gonzaga                         |3427         |$2y$10$lSMsbK0sgKJ4NrqRvJz/Bu3UFQth9TDN93JaBFFcjNsCK0rtCQJZ2|manutencao.cgr@carvalima.com.br         |         634|             2|Y     |                    |                       |N                 |                 |               |
565|Kleber Junior Sousa Santos                        |9745         |$2y$10$wc1G0XM.ezQmgL3iNU7zIOQvc92TZo.TqCOfaoSpX3vTY/Y0iPl2O|kleber@carvalima.com.br                 |         634|             1|Y     |                    |                       |N                 |                 |               |
444|Hugo Leonardo da Silva Vasconcelos                |3245         |04d4a80270998b402d8ec627705b9caf                            |qualidade.06@carvalima.com.br           |         634|             1|Y     |                    |                       |N                 |                 |               |
 99|Marta Matsumoto                                   |2908         |$2y$10$J1zHO5CJ5kpNA6wmatCFGu8PZjW8hNq61UXapYS0M1bK.LXBJQUs6|gerencia.rbo@carvalima.com.br           |         634|            21|Y     |                    |                       |N                 |                 |               |
178|Ademar dos Santos Miotto                          |6910         |$2y$10$hHkdRUR1iYTZSYJyT4kaxuo7EUQBYEOmISZItBBpuYwFl5G7RtkW6|gerencia.pvh@carvalima.com.br           |         634|            17|Y     |                    |                       |N                 |                 |               |
453|Lucilene Gonçalves da Silva                       |8738         |$2y$10$bFxSFp5zw4gS7UHMgWmu4OeZrc309NLqCsZo0buEM.tuqFSGehjR6|supervisoradm.cgr@carvalima.com.br      |         634|             2|N     |                    |                       |N                 |                 |               |
443|Rafaela Oliveira de Araujo                        |8080         |$2y$10$v5WvRIyMytsbEOQXKyk4CumN1aEVIna1qjyDoE7dsGnPMmDDXwJJy|tst03.mtz@carvalima.com.br              |         634|             1|N     |                    |                       |N                 |                 |               |
564|LAILTON DOS SANTOS GALVAO                         |8750         |$2y$10$SI8qMW4Q9ddupzbXdwHkS.kI5EKuU5iZbs6KrAmk3ERgISYlGr1dK|analistaa@carvalima.com.br              |         634|             3|Y     |                    |                       |N                 |                 |               |
429|Leandro Berlande Padilha                          |3063         |1bd4b29a8e0afccd9923fe29cecb4b29                            |                                        |         634|             7|Y     |                    |                       |N                 |                 |               |
567|ANDREW LUIZ DOS SANTOS CASSEMIRO                  |8374         |$2y$10$H4Ngfb4HwRJwyFV1iJwXiumnNg9hhf2oYy8ssdUgCBVyb.O0vO7PS|                                        |         634|             4|Y     |                    |                       |N                 |                 |               |
395|Silvia Cristina Sartoreli Araya de Oliveira       |8237         |$2y$10$Ln.cxakb7QBZ6qIMF9AkROUR2M0vqcHw83NCBXmlVYd7lG.fsnASe|administrativo02.cwb@carvalima.com.br   |         634|             7|Y     |                    |                       |N                 |                 |               |
426|Silmara dos Santos Gonçalves                      |8399         |$2y$10$v.KEzqtjxbXMkzxIMbvCFurceuzfvDqywZq4e2Zc4ZdLLht.Vx5wi|                                        |         634|             7|N     |                    |                       |N                 |                 |               |
452|GRACIELE VILAS BOAS FERREIRA DE OLIVEIRA          |7184         |$2y$10$aiUIppUJ9oRbIIySrMzPhuYPEXOXcv5JJWXGFHtuW3zP9hBpu0aI2|rh06.mtz@carvalima.com.br               |         634|             1|Y     |                    |                       |N                 |                 |               |
561|Gabriel Amaral                                    |9090         |$2y$10$n08.62QQwe9vjVETdneIleA3e0Wn.OVw.W9D6BlAmwuHlf12.sxeC|tst.rh0.sao@carvalima.com.br            |            |             3|Y     |                    |                       |N                 |                 |               |
450|RAPHAELLA APARECIDA PENA RIOS ARAUJO              |7253         |$2y$10$j48XGNoD8k5/3l5tAkHhKuOznVu1rNxXUtIT828aMHuxYOpG5FBR.|rh09.mtz@carvalima.com.br               |         634|             1|Y     |                    |                       |N                 |                 |               |
435|w                                                 |29           |60ad83801910ec976590f69f638e0d6d                            |                                        |         634|             1|N     |                    |                       |N                 |                 |               |
111|Walter Robson Pires Vieira                        |567          |$2y$10$NRa7uFfoya/84xmELFoH0u3.hhEVDqsdrBhxAPEeIq5n2QkLDB/mC|frota.spo@carvalima.com.br              |         634|             3|Y     |                    |                       |N                 |                 |               |
449|Nivaldo Pereira Dos Santos                        |8678         |$2y$10$ElvxKQXRwZ9D9SsIkydD7uBWMf.wnNFHc9skVX0Ln6086wT/ow4Ke|                                        |         634|             5|N     |                    |                       |N                 |                 |               |
438|Andre Pinotti                                     |8619         |b63826f7edd2fc3ad8449add0c04fceb                            |trainee07.cgb@carvalima.com.br          |         634|             1|N     |                    |                       |N                 |                 |               |
432|Silmara Freitas                                   |8399         |56d33021e640f5d64a611a71b5dc30a3                            |supervisorop.cwb@carvalima.com.br       |         634|             7|N     |                    |                       |N                 |                 |               |
206|Wanderson Melo de Brito                           |0281         |$2y$10$GVTF/G4B7Nc4.zGHOuhnmORDjsXFt08j1VmVVoQPI83wDyNe.1bhq|wanderson.brito@carvalima.com.br        |         634|             1|Y     |                    |                       |N                 |                 |               |
459|Jose Carlos da Costa Souza Junior                 |8054         |47e51e9d11cf800ff08674dbb68a48ab                            |coordenador.expansao@carvalima.com.br   |         634|             1|Y     |                    |                       |N                 |                 |               |
471|Lourdes Faria Garcia (clone)                      |4884         |c133fb1bb634af68c5088f3438848bfd                            |supervisorop04.cgr@carvalima.com.br     |         634|             2|N     |                    |                       |N                 |                 |               |
457|Joao Paulo Moro                                   |7710         |$2y$10$1e0TQzvZyrxaHkU7w8hr8.6IS4GUN0jisPSiBN/E0WGoEHo18pBI.|ti04@carvalima.com.br                   |         634|             1|Y     |                    |                       |N                 |                 |               |
570|THIAGO HENRIQUE DE OLIVEIRA GOULART               |3426         |$2y$10$HMxCtslIl6z6EhT3jqIu6Olxkx2AiY7Mf/ThuXs6UjLqBIZ9BDvfe|                                        |         634|             2|Y     |                    |                       |N                 |                 |               |
130|Cleia Soares Silva                                |8747         |fa95123aa5f89781ed4e89a55eb2edcc                            |rh08.mtz@carvalima.com.br               |         634|             1|N     |                    |                       |N                 |                 |               |
 76|Luciano Barreto Garcia                            |6273         |$2y$10$gSgwQYf2oh.xgfrCVfYtUeDtjgxMgrpppRZ9/wzzmPzD2CxfrLXyK|supervisoradm.sao@carvalima.com.br      |         634|             3|Y     |                    |                       |N                 |                 |               |
340|Romario Holanda da Silva                          |7357         |$2y$10$w6wRGOhYYNqURMhqO54gRuj5/nlm7jGbrtC0uCc2RgHHLMWES9yhG|supervisorop.rbo@carvalima.com.br       |         634|            21|Y     |                    |                       |N                 |                 |               |
571|Lidiane Gomes Chamorro                            |9847         |$2y$10$3tgwgZnlPeVwvT9Or55dTu79wTtNP8jEbyNK2GuJLcZPwrUAqFvQe|tst.adm.cgr@carvalima.com.br            |         634|             2|N     |                    |                       |N                 |                 |               |
463|Fabricyo Rafaell                                  |03268558151  |95dadf25f279f41fdcf6bad601d0c749                            |TRAFEGO.CGB@CARVALIMA.COM.BR            |            |              |Y     |                    |                       |N                 |                 |               |
464|Ivan Paiva                                        |02657464181  |51960f8f4e8a94fb3db777030b75c683                            |TRAFEGO.CGB@CARVALIMA.COM.BR            |            |              |Y     |                    |                       |N                 |                 |               |
465|Jose Carlor Jr                                    |07027759160  |a7470d3d37829070099f0fdeac83ac98                            |TRAFEGO.CGB@CARVALIMA.COM.BR            |            |              |Y     |                    |                       |N                 |                 |               |
466|Lucimar de Souza                                  |00859093190  |063ba883f3f960ac6d56ad9a4f990abb                            |TRAFEGO.CGB@CARVALIMA.COM.BR            |            |              |Y     |                    |                       |N                 |                 |               |
467|Rairon Matos                                      |07622656127  |0ab64c85fef82da4a720233c19628c9a                            |TRAFEGO.CGB@CARVALIMA.COM.BR            |            |              |Y     |                    |                       |N                 |                 |               |
468|Rhuan de Aquino                                   |01994678119  |1b2739647f7b980028c5e030da1f549b                            |TRAFEGO.CGB@CARVALIMA.COM.BR            |            |              |Y     |                    |                       |N                 |                 |               |
316|Luciano Silva de Jesus                            |7929         |$2y$10$.j6xwRk0yHyCaROOKECyq.QwfA5Z5.ZjJRohg6aR4TsyC0jNBZVI.|gerencia.ldb@carvalima.com.br           |         634|            20|Y     |                    |                       |N                 |                 |               |
245|Joao Darlan Pedrozo                               |7391         |$2y$10$NhKF1y1HLc0QHrGYRx4JyeWTXXBmkhSTJa4lYpCKGQDKzHF1Jikyi|gerencia.trafego@carvalima.com.br       |         634|             1|N     |                    |                       |N                 |                 |               |
102|Leonardo Meriz Andrietti                          |4883         |$2y$10$0TWdwuub3U55JcBvtZZAaORGdCv4qm2JiDNNxoCNZ6OXwHpeI7AVa|supervisor.ngt@carvalima.com.br         |         634|             9|Y     |                    |                       |N                 |                 |               |
 67|Bruno Gonçalves                                   |5243         |$2y$10$uyG1eeKK2CxTYfAH03ANte9ZPheSjtFBAWPpM1.V6ApHjURMg0srG|operacional6.cba@carvalima.com.br       |         634|             4|Y     |                    |                       |N                 |                 |               |
572|Nina Michaela Duarte da Silva                     |9802         |$2y$10$e3LpQEZfUlyD80jqR.gIGOLlYrAPIMItS7St5/vn2wbUwjj/Ya8vS|09802@Carvalima.com.br                  |         634|             1|Y     |                    |                       |N                 |                 |               |
455|Glauco Henrique Soares Barbosa                    |8771         |$2y$10$HkEuSBj7EE.cIX1FGCD7CuWHf4ra8slzjwTcMsheL2H8bv0265Xja|                                        |         634|             1|Y     |                    |                       |N                 |                 |               |
117|Daiany da Silva Moreira                           |4925         |$2y$10$7AlmTQWlVkiykfZaBO8ihOCDm0XpF.qmjdjOYtX0NF/86ChUQ3CLu|gerencia.roo@carvalima.com.br           |         634|             8|Y     |                    |                       |N                 |                 |               |
562|Guilherme Matheus Alves da Silva                  |5672         |$2y$10$fj8CohLICZg5eEqrKEBCUuzvnv90uhdJmG.0KcosZymSBRcL3gfxu|lidermlv.sao@carvalima.com.br           |            |             3|Y     |                    |                       |N                 |                 |               |
  1|Administrator                                     |admin        |$2y$10$cPMaiV.cyn4U/WvS8pL0X.R8eUwOjMHjdgQT1DBeCQPl2Uy6SJsom|financeiro@unitopconsultoria.com.br     |         634|             1|Y     |                    |                       |N                 |                 |               |
573|Herenna Keler                                     |9196         |$2y$10$P7KyeAOK98xYY/zQciyIC.roosOMEI9TaXS7V28pYaTx9OGXP8InC|fiscalcontabil4@carvalima.com.br        |         634|             1|Y     |                    |                       |N                 |                 |               |
535|Felipe Vigolvino Aparecido Leite                  |9403         |$2y$10$IeQBWsPpSHXq5AW.v6wmYOEV7PLZK/MsTxaCPHVAbbzWu068Klz4W|analista.fedrizze@carvalima.com.br      |         634|            12|N     |                    |                       |N                 |                 |               |
