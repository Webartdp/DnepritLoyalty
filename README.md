# DnepritLoyalty

Програма лояльності та внутрішній бонусний рахунок для **MODX Revolution 2.8.1 + miniShop2**.

## 0.1.0-beta2

Beta2 виправляє перший реальний UI-баг після встановлення beta1 у MODX Manager:

- вкладка **Налаштування** тепер побудована як повноцінний `MODx.FormPanel` і не згортається до нульової висоти;
- кнопки **Зберегти налаштування** та **Оновити** завжди видимі у верхній панелі;
- поля мають обмежену читабельну ширину замість розтягування на весь екран;
- додані нормальні відступи між секціями, підписами, полями та кнопками;
- довга форма має вертикальну прокрутку;
- налаштування розділені на блоки: основні, нарахування, списання, Lifetime Discount, статуси замовлень;
- додані українські та російські підписи для всіх полів налаштувань.

Пакет:

```text
dnepritloyalty-0.1.0-beta2.transport.zip
```

## Основний функціонал

Компонент закладає базову production-архітектуру:

- внутрішній бонусний рахунок авторизованого покупця;
- доступний та зарезервований баланс;
- незмінна історія операцій;
- ручне нарахування / списання менеджером;
- бонуси за реєстрацію та перше оплачене замовлення;
- нарахування відсотка від замовлення;
- резерв бонусів під час оформлення та повернення при скасуванні;
- захист від подвійного нарахування через `unique_key`;
- рівні Lifetime Discount за сумою сплачених замовлень;
- процентна або фіксована постійна знижка;
- вибір статусів замовлень, що беруть участь у lifetime-розрахунку;
- період lifetime-вибірки від / до;
- обмеження за групами користувачів;
- мінімальна сума поточного замовлення;
- ліміт частки замовлення, яку можна оплатити бонусами;
- CMP: Клієнти, Операції, Рівні, Правила, Налаштування;
- snippets для балансу, кабінету та блоку бонусів у checkout;
- підготовлений listener `OnDnepritNewsletterSubscribe`.

## Установка

Збірка створює:

```text
dnepritloyalty-0.1.0-beta2.transport.zip
```

Встановлюється через стандартний **Extras → Installer** MODX поверх beta1 або як нова установка.

Залежність: встановлений miniShop2.

## Налаштування статусів

Після установки обов'язково вкажіть ID статусів miniShop2:

```text
dnepritloyalty.lifetime_statuses
dnepritloyalty.reward_statuses
dnepritloyalty.cancel_statuses
```

Компонент навмисно не вгадує ID статусів конкретного магазину.

## Checkout

Всередину форми оформлення miniShop2 додайте:

```modx
[[!DnepritLoyaltyCart]]
```

Snippet показує баланс, постійну знижку та поле `dneprit_loyalty_points`.

## Кабінет

```modx
[[!DnepritLoyaltyBalance]]
[[!DnepritLoyaltyAccount]]
```

## Lifetime Discount

Рівні задаються в CMP, наприклад:

```text
5 000 грн  → 3%
15 000 грн → 5%
30 000 грн → 7%
```

## Бонуси

Основні параметри:

```text
dnepritloyalty.point_value             1
dnepritloyalty.order_reward_percent    5
dnepritloyalty.max_spend_percent       30
dnepritloyalty.min_spend_points        100
dnepritloyalty.min_order_for_spend     0
dnepritloyalty.discount_min_order      0
```

`point_value=1` означає: 1 бонус = 1 одиниця валюти магазину.

## Безпека обліку

Кожна зміна балансу має окрему транзакцію. Системні дії мають унікальні ключі на кшталт:

```text
rule:registration:42
rule:first_order:42
order_reward:1555
order_spend:1555
```

Повторна обробка одного й того ж event не створює повторне нарахування.

## miniShop2 events

Plugin використовує:

```text
OnUserSave
msOnSubmitOrder
msOnGetOrderCost
msOnBeforeCreateOrder
msOnCreateOrder
msOnChangeOrderStatus
OnDnepritNewsletterSubscribe
```

## Резерви бонусів

Якщо створення замовлення перерветься після резервування, резерв має TTL 30 хвилин.
Рекомендований cron раз на 5 хвилин:

```cron
*/5 * * * * /usr/bin/php /path/to/site/core/components/dnepritloyalty/cron/release-reservations.php
```

## Вимоги

- MODX Revolution 2.8.1
- PHP 7.4+
- MySQL / MariaDB
- miniShop2
- ExtJS 3.4 / MODExt

## Ліцензія

MIT.
