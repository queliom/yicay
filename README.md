## Sobre a aplicação

Esta aplicação representa o funcionamento simplificado de uma carteira digital. O fluxo de uma transação entre dois tipos de usuários (Consumer -> Retailer) e usuários de mesmo tipo (Consumer -> Consumer).

### Corpo da requisição

- Rota: 

POST api/transaction

- Exemplo:

```json
{
    "amount" : 100.00,
    "payer" : "a286d5c0-4dd7-48ca-9a33-9b426515e3f3",
    "payee" : "548da5fe-06bd-4f15-87b3-851336e57723"
}
```