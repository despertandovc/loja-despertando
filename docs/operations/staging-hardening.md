# Hardening do staging

## Estado esperado

- `WP_ENVIRONMENT_TYPE=staging`.
- `DISALLOW_FILE_EDIT=true`.
- `AUTOMATIC_UPDATER_DISABLED=true`.
- Site com `blog_public=0` para noindex.
- HTTPS via Traefik + Let's Encrypt.
- DNS-only na Cloudflare para sub-subdomínio `loja.staging.despertando.com.br`.
- `.env` real apenas na VPS.
- Backups locais antes de mudanças relevantes.
- `wpcli` fica em `profiles: ["tools"]` para não aparecer como serviço encerrado no Dockge.

## Plugins

Manter o mínimo necessário:

- WooCommerce.
- Despertando Commerce Core.

Não instalar plugins de marketplace, afiliado, dropshipping, cache ou pagamento antes de decisão técnica específica.
