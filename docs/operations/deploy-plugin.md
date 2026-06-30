# Deploy do plugin Despertando Commerce Core no staging

O plugin versionado fica em:

```text
plugins/despertando-commerce-core
```

O staging usa o plugin copiado para:

```text
/opt/stacks/wp-loja-despertando/wp-data/wp-content/plugins/despertando-commerce-core
```

## Fluxo recomendado

1. Alterar código em branch `autonomous/*`.
2. Abrir PR.
3. Aguardar checks `docs-smoke` e `php-lint`.
4. Fazer merge seguro.
5. Copiar a versão da `main` para o staging.
6. Ativar/validar via WP-CLI.

## Validações mínimas

```bash
cd /opt/stacks/wp-loja-despertando
WP='docker compose run --rm --user 0 wpcli --allow-root'
$WP plugin is-active woocommerce
$WP plugin is-active despertando-commerce-core
$WP plugin get despertando-commerce-core --field=version
curl -I https://loja.staging.despertando.com.br/
```
