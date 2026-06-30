# Loja Despertando

Repositório oficial do projeto **Ecommerce Despertando**, cuja loja pública será a **Loja Despertando**.

## Status

Bootstrap documental. Ainda não há código de aplicação.

## Decisões principais

- Base: WordPress + WooCommerce.
- Plugin próprio: `despertando-commerce-core`.
- MVP 1: produtos físicos próprios e Dimona integrada 100% via API.
- MVP 2: marketplace com vendedores externos, Mercado Pago Split, produtos afiliados e dropshipping.
- Ambiente inicial: staging primeiro.
- Stack VPS planejada: `/opt/stacks/wp-loja-despertando`.
- Domínio planejado: `loja.despertando.com.br`.

## Documentação

- [Contexto do projeto](PROJECT_CONTEXT.md)
- [Escopo do produto](docs/product-scope.md)
- [Arquitetura](docs/architecture.md)
- [Roadmap MVP](docs/mvp-roadmap.md)
- [Stack VPS](docs/vps-stack.md)
- [Estratégia WooCommerce](docs/woocommerce-strategy.md)
- [Integração Dimona API](docs/dimona-api-integration.md)
- [Fluxo Dimona MVP 1](docs/dimona-flow.md)
- [Marketplace MVP 2](docs/marketplace-mvp2.md)
- [Produtos afiliados MVP 2](docs/affiliate-products-mvp2.md)
- [Dropshipping MVP 2](docs/dropshipping-mvp2.md)
- [ADRs](docs/adr/)

## Staging atual

```text
https://loja.staging.despertando.com.br
```

Documentação operacional:

- [Acesso ao staging](docs/operations/staging-access.md)
- [Deploy do plugin](docs/operations/deploy-plugin.md)
- [Backup e restore](docs/operations/backup-restore.md)
- [Hardening do staging](docs/operations/staging-hardening.md)
- [DNS do staging](docs/operations/dns-staging.md)

## Guardrails

- Nunca alterar `main` diretamente após o bootstrap inicial do repositório.
- Toda alteração versionada deve passar por branch `autonomous/*`, PR, checks e revisão.
- Não versionar secrets, tokens, senhas, arquivos `.env` ou credenciais reais.
- DNS, proxy público, produção, pagamentos reais, split em produção e dados reais exigem checkpoint humano.
