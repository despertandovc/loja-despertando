# Contrato Dimona API V2/V3

Fonte operacional: documentação pública Dimona API V2 capturada em 30/06/2026 e anexada ao projeto como `documentacao-completa-api-dimona-v2.docx`.

## Base e autenticação

Base exibida pela documentação:

```text
https://admin.camisadimona.com.br
```

Autenticação:

```http
api-key: {{api-key}}
Accept: application/json
Content-Type: application/json
```

No projeto, a credencial real deve ser configurada fora do chat e fora do repositório:

```text
DIMONA_API_KEY
```

## Pedido — criar pedido

Endpoint principal v2:

```http
POST {{api-domain}}/api/v2/order
```

Endpoint v3 para designs/mocks com atributos nomeados:

```http
POST {{api-domain}}/api/v3/order
```

Decisão do projeto: preferir `POST /api/v3/order` para produtos com áreas nomeadas de impressão, porque o payload aceita `designs` e `mocks` como objetos com chaves como:

```text
front
back
left_sleeve
right_sleeve
inner_label
outer_label
```

Payload base esperado:

```json
{
  "shipping_speed": "pac",
  "delivery_method_id": "177",
  "order_id": "woo-123",
  "customer_name": "Fulano da Silva",
  "customer_document": "123.456.789-13",
  "customer_email": "cliente@example.com",
  "webhook_url": "https://loja.staging.despertando.com.br/wp-json/despertando-commerce/v1/dimona/webhook",
  "items": [
    {
      "name": "Camisa Polo M Branca",
      "sku": "woo-item-sku",
      "qty": 2,
      "dimona_sku_id": "010603110109",
      "designs": {
        "front": "https://url-to-front",
        "back": "https://url-to-back"
      },
      "mocks": {
        "front": "https://url-to-front-mock",
        "back": "https://url-to-back-mock"
      }
    }
  ],
  "address": {
    "street": "Rua Buenos Aires",
    "number": "334",
    "complement": "Loja",
    "city": "Rio de Janeiro",
    "state": "RJ",
    "zipcode": "20061000",
    "neighborhood": "Centro",
    "phone": "21 21093661",
    "country": "BR"
  }
}
```

Resposta v3 de exemplo:

```json
{
  "order": "064-435-556"
}
```

## Pedido duplicado

A documentação mostra resposta `422 Unprocessable Entity` com corpo:

```text
"O pedido 1234 já existe"
```

O plugin deve tratar essa resposta como caso de idempotência quando o `order_id` enviado for a referência do pedido WooCommerce.

## Consultar pedido

```http
GET {{api-domain}}/api/v2/order/{{order-id}}
```

Resposta de exemplo contém:

```json
{
  "dimona_id": "123-123-123",
  "seller_id": "abcd",
  "status": "Aguardando Imagens",
  "shipping_cost": 17.99,
  "shipping_method_name": "Correios Sedex",
  "total_value": 67.99,
  "status_id": 18,
  "created_at": "01/01/2000",
  "tracking_url": "http://status.ondeestameupedido.com/tracking/1168/123123123",
  "tracking_code": null
}
```

## Tracking

```http
GET {{api-domain}}/api/v2/order/{{order-id}}/tracking
```

Retorna histórico de rastreamento com campos como:

```json
[
  {
    "status_name": "Entregue",
    "micro_status_name": "ENTREGUE NO LOCAL DE RETIRADA",
    "micro_status_description": "A carga está em processo de transferência entre filiais.",
    "message": "FL RIO DE JANEIRO",
    "event_date": {
      "date": "2018-08-08 17:54:00.000000",
      "timezone_type": 3,
      "timezone": "America/Sao_Paulo"
    },
    "created_at": "2018-08-09 14:33:57"
  }
]
```

## Timeline

```http
GET {{api-domain}}/api/v2/order/{{order-id}}/timeline
```

A timeline mistura eventos de status, produção, ações automáticas, transação e tracking.

## Disponibilidade

```http
GET {{api-domain}}/api/v2/sku/{{sku_reference}}/availability
```

Descrição da documentação: espera product ID e retorna objeto no formato:

```text
{idCor: {idTamanho1: qty, idTamanho2: qty}}
```

## Frete

```http
POST {{api-domain}}/api/v2/shipping
```

Payload documentado:

```json
{
  "zipcode": "20061001",
  "quantity": "1"
}
```

Resposta de exemplo:

```json
[
  {"name": "Correios Sedex", "value": 10.34, "business_days": 6, "delivery_method_id": 2},
  {"name": "Jadlog Econômica", "value": 15.35, "business_days": 10, "delivery_method_id": 177},
  {"name": "Jadlog Express", "value": 15.57, "business_days": 9, "delivery_method_id": 176},
  {"name": "Buslog", "value": 24.32, "business_days": 5, "delivery_method_id": 780}
]
```

Decisão do projeto: usar esse endpoint desde o MVP 1. O `delivery_method_id` escolhido no checkout deve ser salvo no pedido e enviado no `Create Order`. Quando `delivery_method_id` for enviado, a documentação informa que `shipping_speed` será ignorado.

## Webhook

A documentação informa que a URL é configurada em:

```text
www.camisadimona.com.br/loja/conta/avancado
```

A Dimona envia POST a cada atualização de status.

Payload documentado:

```json
{
  "api_key": "YOUR_API_KEY",
  "dimona_id": "462-424-866",
  "status_id": 13,
  "name": "Faturado",
  "seller_id": "1524594756",
  "tracking_url": "http://status.ondeestameupedido.com/tracking/1168/462424866"
}
```

Endpoint do projeto:

```text
/wp-json/despertando-commerce/v1/dimona/webhook
```

URL staging:

```text
https://loja.staging.despertando.com.br/wp-json/despertando-commerce/v1/dimona/webhook
```

## Implicação de segurança do webhook

O payload documentado inclui `api_key`, mas o plugin não deve gravar esse valor em log. O webhook deve validar a chave recebida com a credencial configurada de forma segura, redigir o campo em logs e salvar apenas hash/evento sanitizado.

## Variáveis previstas

```text
DIMONA_API_KEY
DIMONA_API_BASE_URL=https://admin.camisadimona.com.br
DIMONA_WEBHOOK_SECRET opcional/local, caso seja necessário adicionar proteção própria
```

Como a documentação oficial usa `api_key` no corpo do webhook, a validação primária poderá ser por comparação segura com `DIMONA_API_KEY`.
