<?php

namespace App\Services;

use App\Exceptions\OperationFailedException;
use Exception;

class KafkaService
{

    public function sendMessage($key, $message, $topic)
    {
        if(!env('KAFKA_BROKERS')) {
            return;
        }

        $producer = new \RdKafka\Producer();
        $producer->setLogLevel(LOG_DEBUG);

        if ($producer->addBrokers(env('KAFKA_BROKERS')) < 1) {
            throw new OperationFailedException("No se pudo añadir brokers");
        }

        $topic = $producer->newTopic($topic);

        try {
             for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
                $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($message), $key);
                $producer->poll(0);

                if ($producer->flush(1000) === RD_KAFKA_RESP_ERR_NO_ERROR) {
                    break;
                }
            }

            if ($flushRetries > 10) {
                throw new OperationFailedException("No se pudo enviar el mensaje");
            }
        } catch (Exception) {
            throw new OperationFailedException("No se pudo enviar el mensaje");
        }
    }
}
