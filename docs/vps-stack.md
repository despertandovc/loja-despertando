# Stack VPS

## Caminho planejado

```text
/opt/stacks/wp-loja-despertando
```

## Serviços

- WordPress.
- MariaDB.
- Redis.
- WP-CLI.

## Ambiente

Staging primeiro. DNS/proxy público e produção exigem checkpoint humano.

## Caminhos esperados

```text
/opt/stacks/wp-loja-despertando/compose.yaml
/opt/stacks/wp-loja-despertando/.env
/opt/stacks/wp-loja-despertando/wp-data/
/opt/stacks/wp-loja-despertando/db-data/
/opt/stacks/wp-loja-despertando/backups/
/opt/stacks/wp-loja-despertando/logs/
```

O `.env` real não deve ser versionado nem exposto no chat.
