# Backup e restore

## Backup manual

A stack tem diretório local:

```text
/opt/stacks/wp-loja-despertando/backups
```

O script recomendado é:

```bash
/opt/stacks/wp-loja-despertando/bin/backup.sh
```

Cada backup deve conter:

- `database.sql`;
- `wp-data.tar.gz`;
- `compose.yaml`;
- `SHA256SUMS`.

## Restore

Restore envolve dados persistentes e deve ser tratado como checkpoint humano quando houver risco de sobrescrever banco, uploads ou configuração real.

Nunca rodar restore destrutivo sem backup anterior e confirmação explícita.
