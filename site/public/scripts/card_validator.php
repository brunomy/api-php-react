<?php

if (isset($_GET['cardnum'])) {
    $cartao = preg_replace("/[^0-9]/", "", $_GET['cardnum']);
    $cvc = isset($_GET['cvc']) ? preg_replace("/[^0-9]/", "", $_GET['cvc']) : false;

    $cartoes = [
        'visa'       => ['len' => [13, 16, 19], 'cvc' => 3],
        'mastercard' => ['len' => [16],         'cvc' => 3],
        'amex'       => ['len' => [15],         'cvc' => 4],
        'discover'   => ['len' => [16, 19],     'cvc' => 3],
        'elo'        => ['len' => [16],         'cvc' => 3],
        'hipercard'  => ['len' => [13, 16, 19], 'cvc' => 3],
        'diners'     => ['len' => [14, 16],     'cvc' => 3],
        'aura'       => ['len' => [16],         'cvc' => 3],
        'jcb'        => ['len' => [16],         'cvc' => 3],
        'nenhum'     => 0
    ];

    $bandeira = 'nenhum';

    if (preg_match('/^(4011(78|79)|431274|438935|451416|457393|4576|504175|5067|506699|509|627780|636297|636368|6504|6505|6509|6516|6550|655021|655000|651652|6503|6507)/', $cartao)) {
        $bandeira = 'elo';
    } elseif (preg_match('/^6011|^622|^64|^65/', $cartao)) {
        $bandeira = 'discover';
    } elseif (preg_match('/^(606282|3841)/', $cartao)) {
        $bandeira = 'hipercard';
    } elseif (preg_match('/^3(0[0-5]|[68])/', $cartao)) {
        $bandeira = 'diners';
    } elseif (preg_match('/^34|^37/', $cartao)) {
        $bandeira = 'amex';
    } elseif (preg_match('/^50/', $cartao)) {
        $bandeira = 'aura';
    } elseif (preg_match('/^35/', $cartao)) {
        $bandeira = 'jcb';
    } elseif (preg_match('/^4/', $cartao)) {
        $bandeira = 'visa';
    } elseif (preg_match('/^(5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)/', $cartao)) {
        $bandeira = 'mastercard';
    }

    $dados_cartao = $cartoes[$bandeira];

    if (!is_array($dados_cartao)) {
        echo json_encode([false, false, false]);
    } else {
        $valid = in_array(strlen($cartao), $dados_cartao['len']);
        $valid_cvc = $cvc && strlen($cvc) <= $dados_cartao['cvc'] && strlen($cvc) != 0;
        echo json_encode([$bandeira, $valid, $valid_cvc]);
    }
} else {
    echo 0;
}
