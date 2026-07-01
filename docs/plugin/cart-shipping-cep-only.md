# Carrinho — cálculo de frete apenas por CEP

O staging usa o calculador de frete do WooCommerce com UX simplificada para Brasil.

## Decisão

No carrinho, o usuário deve preencher apenas o CEP.

Campos ocultados visualmente no calculador de frete:

- país;
- estado;
- cidade.

Campo visível:

- CEP.

## Implementação

O plugin `despertando-commerce-core` aplica filtros WooCommerce:

```text
woocommerce_shipping_calculator_enable_country = false
woocommerce_shipping_calculator_enable_state = false
woocommerce_shipping_calculator_enable_city = false
woocommerce_shipping_calculator_enable_postcode = true
```

Como o WooCommerce precisa do país para encontrar a zona de entrega, o plugin também injeta no formulário:

```html
<input type="hidden" name="calc_shipping_country" value="BR">
```

E, quando o cliente calcula/troca o CEP, reforça internamente:

```text
shipping_country = BR
billing_country = BR
```

## Troca de CEP

O link padrão do WooCommerce é traduzido para:

```text
Trocar CEP
```

Assim o usuário pode recalcular o frete usando outro CEP sem precisar preencher país, estado ou cidade.

## Observação

No checkout, dados completos de entrega ainda podem ser necessários para finalizar pedido e criar payload Dimona. Este ajuste é apenas para cálculo inicial de frete no carrinho.
