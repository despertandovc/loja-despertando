# Gateway de pagamento de teste — staging

O plugin `despertando-commerce-core` inclui um gateway de pagamento sem cobrança real para validar checkout completo no staging.

## Gateway

```text
DCC Pagamento de Teste
ID: dcc_test_payment
```

## Quando aparece

O gateway só fica disponível quando:

```text
wp_get_environment_type() != production
DCC Dimona Create Order real = desligado
DCC Dimona Dry-run = ligado
```

Ou seja, ele não deve aparecer em produção nem quando a criação real de pedido Dimona estiver habilitada.

## O que faz

- não cobra cartão;
- aprova o pedido WooCommerce;
- move o pedido para `processing`;
- dispara o fluxo Dimona em dry-run;
- salva payload/hash no pedido;
- mantém `_dcc_dimona_order_id` vazio.

## Objetivo

Validar o fluxo:

```text
produto → carrinho → frete Dimona → checkout → pedido WooCommerce → payload Dimona dry-run
```

sem criar pedido real na Dimona.
