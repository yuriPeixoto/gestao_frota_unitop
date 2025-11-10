🚀 Funcionalidades do Comando:
Comandos Disponíveis:
bash# Limpeza padrão (sessões > 24h)
php artisan autosave:limpar-sessoes

# Limpeza forçada (TODAS as sessões)
php artisan autosave:limpar-sessoes --force

# Limpeza personalizada (> 6 horas)
php artisan autosave:limpar-sessoes --older-than=6

# Apenas estatísticas (sem limpar)
php artisan autosave:limpar-sessoes --stats

# Limpar sessões de usuário específico
php artisan autosave:limpar-sessoes --user=123

# Limpar tudo de um usuário específico
php artisan autosave:limpar-sessoes --force --user=123

# Modo verbose (mostra detalhes)
php artisan autosave:limpar-sessoes -v
🔧 Recursos Implementados:
✅ Suporte múltiplos drivers de cache (Redis, File, Database)
✅ Progress bar para operações longas
✅ Estatísticas detalhadas com rankings
✅ Validação de integridade das sessões
✅ Limpeza por usuário específico
✅ Logs detalhados de todas as operações
✅ Modo verbose para debugging
✅ Confirmações de segurança para operações destrutivas
✅ Tratamento de erros robusto
✅ Métricas de performance (uso de memória)
📊 Exemplo de Saída das Estatísticas:
📊 Estatísticas das Sessões Auto-Save
=====================================
┌──────────────────────────┬───────┐
│ Métrica                  │ Valor │
├──────────────────────────┼───────┤
│ Total de sessões         │ 45    │
│ Sessões ativas (< 2h)    │ 12    │
│ Sessões antigas (> 2h)   │ 28    │
│ Sessões inválidas        │ 5     │
│ Total de operações       │ 234   │
│ Média operações/sessão   │ 5.2   │
└──────────────────────────┴───────┘

👥 Top 5 Usuários (por número de sessões):
┌──────────────┬─────────┐
│ Usuário      │ Sessões │
├──────────────┼─────────┤
│ Usuário 1    │ 15      │
│ Usuário 23   │ 8       │
│ Usuário 7    │ 6       │
└──────────────┴─────────┘

💡 Recomendações:
⚠️ Muitas sessões antigas (28). Execute limpeza: php artisan autosave:limpar-sessoes
O comando agora está 100% funcional e pronto para produção! 🎯