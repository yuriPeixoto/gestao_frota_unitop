<?php

use Carbon\Carbon;

function TimestampToDate($timestamp) {
    // Converte o timestamp de milissegundos para segundos (dividindo por 1000)
    $timestampInSeconds = $timestamp / 1000;

    // Formata a data no formato desejado
    return date('Y-m-d H:i:s', $timestampInSeconds);
}