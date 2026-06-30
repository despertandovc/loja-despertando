# Fluxo Dimona — MVP 1

## Decisão atual

A integração Dimona será tratada como parte central do MVP 1, não como complemento posterior.

Requisitos confirmados:

- usar catálogo oficial de SKUs da Dimona/Dropsimples;
- calcular frete pelo endpoint da Dimona desde o começo;
- criar pedidos Dimona via API;
- configurar webhook para atualização de status;
- implementar rastreamento completo e profissional;
- manter logs, retry, idempotência e reprocessamento manual.

## Fonte do catálogo SKU

Planilha recebida no projeto:

```text
Dropsimples _ Catálogo de SKU's _ Atualizado 15 de Agosto de 2025.xlsx
```

Estrutura identificada:

```text
Aba: SKUs 2025
Colunas: Código SKU, Nome, Estilo, Cor, Tamanho, NCM
Registros úteis: 985
Linhas de produto: 38
Estilos: 19
Cores: 30
Tamanhos: 28
NCMs: 13
```

Essa planilha não deve virar mapeamento manual solto. Ela será tratada como catálogo importável/versionável para alimentar o plugin.

## Modelo de cadastro do produto

Produto WooCommerce com fulfillment:

```text
fulfillment_type = dimona
```

Campos por produto ou variação:

```text
dimona_sku
dimona_product_name
dimona_style
dimona_color
dimona_size
dimona_ncm
dimona_print_area
dimona_artwork_url
dimona_mockup_url
```

Para produto variável, o SKU Dimona deve ficar na variação, não apenas no produto pai.

Exemplo:

```text
Camiseta Despertando — Eu Sou
  P / Branco  -> SKU Dimona A
  M / Branco  -> SKU Dimona B
  G / Branco  -> SKU Dimona C
  P / Preto   -> SKU Dimona D
```

## Fluxo de compra

```text
Cliente escolhe produto/tamanho/cor
  ↓
WooCommerce monta carrinho
  ↓
Plugin identifica itens Dimona
  ↓
Plugin consulta endpoint de frete Dimona
  ↓
WooCommerce apresenta método/prazo/preço Dimona no checkout
  ↓
Cliente paga
  ↓
Pagamento aprovado
  ↓
Plugin cria pedido Dimona via API
  ↓
Plugin salva dimona_order_id e status inicial
  ↓
Webhook Dimona atualiza produção/envio/rastreio
  ↓
Cliente acompanha rastreamento na loja
```

## Frete Dimona desde o começo

O MVP 1 deve implementar um método de entrega WooCommerce próprio:

```text
Dimona Shipping Method
```

Responsabilidades:

- montar cotação com CEP de destino;
- enviar itens Dimona, quantidades e SKUs;
- consultar endpoint de frete Dimona;
- transformar resposta em rates WooCommerce;
- salvar transportadora, serviço, prazo e custo estimado no pedido;
- bloquear checkout se não houver cotação válida para item Dimona;
- registrar erro operacional sem expor credenciais.

## Carrinho misto

Carrinho com item Dimona + item de estoque próprio exige regra explícita.

Regra inicial recomendada:

- se houver item Dimona, calcular frete Dimona para os itens Dimona;
- itens de estoque próprio terão método próprio separado;
- informar ao cliente que o pedido pode chegar em entregas separadas.

Na implementação inicial, se o WooCommerce não conseguir representar os pacotes de forma clara, bloquear carrinho misto até a regra ficar segura.

## Criação de pedido Dimona

Após pagamento aprovado:

```text
woocommerce_payment_complete
woocommerce_order_status_processing
```

O plugin deve:

1. verificar se o pedido tem itens Dimona;
2. verificar idempotência;
3. montar payload real;
4. criar pedido na API Dimona;
5. salvar `dimona_order_id`;
6. salvar status inicial;
7. registrar log sanitizado;
8. liberar reprocessamento em caso de erro.

## Idempotência

Nunca criar pedido Dimona duplicado para o mesmo item.

Chave sugerida:

```text
woo_order_id + woo_order_item_id + dimona_sku
```

Metadados esperados:

```text
_dcc_dimona_order_id
_dcc_dimona_status
_dcc_dimona_idempotency_key
_dcc_dimona_last_payload_hash
_dcc_dimona_last_attempt_at
_dcc_dimona_attempt_count
```

## Webhook Dimona

Criar endpoint público próprio no WordPress:

```text
/wp-json/despertando-commerce/v1/dimona/webhook
```

Responsabilidades:

- receber atualizações da Dimona;
- validar assinatura/segredo quando disponível;
- validar estrutura do payload;
- localizar pedido por `dimona_order_id` ou referência externa;
- salvar evento bruto sanitizado;
- atualizar status operacional;
- atualizar rastreamento;
- adicionar nota interna no pedido;
- retornar resposta idempotente.

## Segurança do webhook

O webhook precisa usar uma ou mais proteções:

```text
DCC_DIMONA_WEBHOOK_SECRET
```

ou validação por assinatura oficial, se a Dimona oferecer.

O segredo real deve ficar fora do chat e fora do repositório.

## Rastreamento profissional

Criar tabela/eventos para tracking Dimona:

```text
wp_dcc_dimona_tracking_events
```

Campos sugeridos:

```text
id
order_id
dimona_order_id
carrier
tracking_code
tracking_url
status
status_label
event_datetime
raw_event_hash
created_at
```

Metadados no pedido:

```text
_dcc_dimona_tracking_code
_dcc_dimona_tracking_url
_dcc_dimona_carrier
_dcc_dimona_last_tracking_status
_dcc_dimona_last_tracking_at
```

## Estados internos

```text
pending_api_integration
freight_quoted
freight_failed
queued_to_dimona
sent_to_dimona
accepted_by_dimona
in_production
production_done
shipped
in_transit
out_for_delivery
delivered
delivery_failed
failed
manual_reprocess_requested
cancelled
```

## Comunicação com o cliente

O cliente deve ver status amigável, não estados técnicos.

Exemplos:

```text
Recebemos seu pedido
Pedido enviado para produção
Seu produto está em produção
Pedido despachado
Pedido em transporte
Saiu para entrega
Entregue
Tivemos um problema na entrega
```

## Próxima fase técnica

Implementar `despertando-commerce-core` v0.3.0 com:

- importador de catálogo SKU;
- campos Dimona por variação;
- shipping method Dimona;
- cliente HTTP Dimona;
- estrutura de webhook;
- tabela de tracking;
- logs e retries;
- testes/smoke sem chamada real quando credencial ausente.

A chamada real aos endpoints depende da configuração segura de:

```text
DIMONA_API_KEY
DIMONA_WEBHOOK_SECRET
```
