# Carrinho — cálculo de frete apenas por CEP

O staging usa o calculador de frete do WooCommerce com UX simplificada para Brasil.

## Decisão

No carrinho, o usuário deve preencher apenas o CEP.

Campos ocultados no calculador de frete:

- país;
- estado;
- cidade.

Campo mantido:

- CEP.

## Implementação

O plugin `despertando-commerce-core` aplica filtros WooCommerce:

```text
woocommerce_shipping_calculator_enable_country = false
woocommerce_shipping_calculator_enable_state = false
woocommerce_shipping_calculator_enable_city = false
woocommerce_shipping_calculator_enable_postcode = true
```

Para requisições do calculador de frete, o país é preenchido internamente como `BR`.

## Observação

No checkout, dados completos de entrega ainda podem ser necessários para finalizar pedido e criar payload Dimona. Este ajuste é apenas para cálculo inicial de frete no carrinho.
