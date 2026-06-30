# Project Context — Ecommerce Despertando

## Identidade

- Projeto ChatGPT: Ecommerce Despertando.
- Nome público: Loja Despertando.
- Domínio planejado: `loja.despertando.com.br`.
- Repositório oficial: `despertandovc/loja-despertando`.
- Stack VPS planejada: `/opt/stacks/wp-loja-despertando`.
- Ambiente inicial: staging primeiro.

## Objetivo

Criar um e-commerce próprio para vender produtos físicos próprios e produtos print on demand com Dimona no MVP 1, evoluindo no MVP 2 para marketplace, produtos afiliados e dropshipping.

## MVP 1

- WordPress + WooCommerce.
- Produtos físicos próprios.
- Dimona 100% integrada via API própria, não por plugin pronto.
- Mercado Pago normal.
- Frete/envio para produtos físicos próprios.
- Plugin próprio `despertando-commerce-core`.
- Logs, rastreamento e reprocessamento de pedidos Dimona.

## MVP 2

- Marketplace com vendedores externos.
- Mercado Pago Split.
- Produtos afiliados.
- Dropshipping.
- Tracking de cliques.
- Regras de carrinho por vendedor e tipo de produto.

## Arquitetura

```text
WordPress + WooCommerce
+
Despertando Commerce Core
```

O WooCommerce será o motor de e-commerce. O plugin próprio concentrará as regras específicas do negócio.

## Fulfillment types

- `own_stock`: produto físico próprio.
- `dimona`: print on demand via API Dimona.
- `marketplace`: vendedor externo.
- `affiliate`: link externo.
- `dropshipping`: fornecedor terceiro.

No MVP 1, ativar somente `own_stock` e `dimona`.

## Staging atual

- URL pública: `https://loja.staging.despertando.com.br`.
- Stack: `/opt/stacks/wp-loja-despertando`.
- DNS Cloudflare: A record DNS-only para `137.131.143.139`.
- TLS: Traefik/Let's Encrypt na VPS.

## Regras de continuidade

- Não correlacionar este projeto com outros projetos salvo pedido explícito.
- Não assumir decisões de outros projetos.
- Não pedir ou expor secrets no chat.
- Persistir decisões importantes em documentação ou ADR.
- Produção, DNS/proxy público, secrets reais, pagamentos reais, bancos/volumes reais e ações destrutivas exigem checkpoint humano.
