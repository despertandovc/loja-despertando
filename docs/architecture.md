# Arquitetura

A Loja Despertando usará WordPress + WooCommerce como motor de e-commerce e um plugin próprio chamado `despertando-commerce-core` para regras específicas do negócio.

```text
Cliente
  ↓
WordPress + WooCommerce
  ↓
Despertando Commerce Core
  ↓
Integrações:
  - Dimona API
  - Mercado Pago
  - fornecedores futuros
  - afiliados futuros
```

## Responsabilidades do WooCommerce

- Produtos.
- Categorias.
- Carrinho.
- Checkout.
- Pedidos.
- Cupons.
- Cliente.
- Painel administrativo.
- Pagamento padrão.

## Responsabilidades do plugin próprio

- Campo `fulfillment_type` por produto.
- Roteamento de fulfillment.
- Integração Dimona via API.
- Logs técnicos por pedido.
- Retry e reprocessamento.
- Idempotência.
- Futuro marketplace.
- Futuro split.
- Futuros afiliados.
- Futuro dropshipping.

## Observabilidade

O plugin deve registrar eventos operacionais sanitizados por pedido e nunca registrar secrets, tokens, Authorization headers ou credenciais reais.
