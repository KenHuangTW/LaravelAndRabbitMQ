# 使用RabbitMQ 代理 Laravel Queue

在 Laravel 中，Queue的設定其實非常簡單

不過 Laravel 本身的 Application Server 是屬於同步阻塞式，沒有仰賴外部服務(Ex: Apache, Nginx...)

基本上做不到異步非阻塞

如果在這時候，某些Controller需要寄送Email，我們沒有去把 `.env` 把 `QUEUE_CONNECTION` 的 `sync`換掉，人只要多起來，那阻塞的程度堪比我的腦血管

所以今天要來介紹一個好用的工具 RabbitMQ 以及 Queue的相關設定
<!--more-->


# RabbitMQ
RabbitMQ 是一個開源的訊息代理軟體(Message Broker)，它實現了高級訊息隊列協定(AMQP)的標準
它是一個在分散式系統中傳遞訊息的中介軟體，用於在應用程式、系統或服務之間傳遞訊息
RabbitMQ 提供了可靠的訊息傳遞機制，可以在不同的應用程式和服務之間進行通信，並支援各種不同的訊息通訊模式，包括點對點通信和發布/訂閱模式等。

使用 RabbitMQ 可以幫助開發者構建高效、靈活和可擴展的系統架構，尤其是在需要處理大量訊息和實現解耦合的情況下。它提供了多種客戶端庫和插件，可以與各種語言和框架集成，包括 Python、Java、Ruby、.NET 等
此外也提供了豐富的管理和監控工具，使得系統管理員可以輕鬆地監控訊息流量、配置設定和管理用戶。

簡單來說就是專門處理 Message Queue 的軟體，並且可以透過方法來做監控

至於RabbitMQ的教學其他地方有，這邊也不贅述
這邊給一個簡單的Docker啟動RabbitMQ的指令就好

```bash
docker run --name rabbitmq -d -p 15672:15672 -p 5672:5672 -e RABBITMQ_DEFAULT_USER=guest -e RABBITMQ_DEFAULT_PASS=guest rabbitmq:management
```
跑完之後去 http://localhost:15672 看看是否啟動成功
user和password都是 guest

## 與 Redis 的差別
本質上就完全不同，Redis 是一個Key-value的非關聯式資料庫，而 RabbitMQ 是一個訊息代理軟體
這邊引用[AWS的文章](https://aws.amazon.com/tw/compare/the-difference-between-rabbitmq-and-redis/)
>|          | RabbitMQ                                             | Redis                                                |
>|----------|------------------------------------------------------|------------------------------------------------------|
>| 訊息交付 | 保證訊息傳遞。支援複雜的邏輯。                       | 不保證訊息傳遞。需要來自訂閱者的作用中連線。        |
>| 訊息大小 | 訊息大小限制在 128MB 以內。可以處理大型訊息。         | 無訊息限制，但是處理大型訊息 (大於 1 MB) 時效能會降低。|
>| 訊息持久性 | 支援持久性和暫時性訊息。將持久性訊息寫入磁碟。        | 預設不支援持久性訊息。                                |
>| 訊息加密 | 支援 SSL 加密。                                        | SSL 加密適用於 Redis 6.0 版及以上版本。               |
>| 速度     | 每秒最多傳送數萬條訊息。                             | 每秒最多傳送數百萬條訊息。                           |
>| 可用性   | 在叢集中建立多個對等節點。                           | 在叢集中使用領導人-追隨者模型。                       |

所以我們可以發現，大部分的時間點RabbitMQ都比起Redis好，除非今天有需要有非常大量的訊息處理，或是即時資料處理那才會改用Redis

## Laravel 安裝

首先，需要安裝 `php-amqplib` 再安裝 `laravel-queue-rabbitmq`
```bash
composer require php-amqplib/php-amqplib
composer require vladimir-yuldashev/laravel-queue-rabbitmq
```

再來去 `config/queue.php` 進行設定
```php
'connections' => [
    // ...

    'rabbitmq' => [
    
       'driver' => 'rabbitmq',
       'hosts' => [
           [
               'host' => env('RABBITMQ_HOST', '127.0.0.1'),
               'port' => env('RABBITMQ_PORT', 5672),
               'user' => env('RABBITMQ_USER', 'guest'),
               'password' => env('RABBITMQ_PASSWORD', 'guest'),
               'vhost' => env('RABBITMQ_VHOST', '/'),
           ],
           // ...
       ],

       // ...
    ],

    // ...    
],
```

記得 .env 也要改掉

```sh
QUEUE_CONNECTION=rabbitmq
```

然後發送 request 給 Laravel Server

再來我們就可以看到RabbitMQ有任務進來了
![rabbitMQ-exec](rabbitMQ-exec.png)
當然正常可能沒機會看到，這邊是因為我故意沒有啟動queue所以才看的到，queue work之後就會看到他被消化掉了

如此一來我們的 Laravel 使用的 Queue 就會是RabbitMQ了


# 參考資料
[Laravel Queues](https://laravel.com/docs/10.x/queues)
[laravel queues - how sync driver works?](https://stackoverflow.com/questions/43467680/laravel-queues-how-sync-driver-works-does-it-executes-in-a-separate-process-t)
[RabbitMQ](https://en.wikipedia.org/wiki/RabbitMQ)
[RabbitMQ 基本介紹、安裝教學](https://kucw.github.io/blog/2020/11/rabbitmq/)
[laravel-queue-rabbitmq](https://github.com/vyuldashev/laravel-queue-rabbitmq)
[RabbitMQ 與 Redis 之間有何差異？](https://aws.amazon.com/tw/compare/the-difference-between-rabbitmq-and-redis/)


</style>
<hr class="style-one" />

如果有錯誤的部份，歡迎指正，謝謝。
如果你喜歡這篇文章，請幫我拍手
只需要註冊會員就可以囉，完全不用花費任何一毛錢就可以用來鼓裡創作者囉


<div>
    <script type="text/javascript">
    document.write(
        "<iframe scrolling='no' frameborder='0' sandbox='allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-storage-access-by-user-activation' style='height: 212px; width: 100%;' src='https://button.like.co/in/embed/wtf81905/button?referrer=" +
        encodeURIComponent(location.href.split("?")[0].split("#")[0]) + "'></iframe>");
    </script>
</div>