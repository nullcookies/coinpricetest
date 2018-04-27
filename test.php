<?php
$arrayTest  =   [
    'inline_keyboard' => [
        [
            ['text' => '1', 'callback_data' => 'k=1'],
            ['text' => '2', 'callback_data' => 'k=2'],
            ['text' => '3', 'callback_data' => 'k=3'],
        ],
        [
            ['text' => '4', 'callback_data' => 'k=4'],
            ['text' => '5', 'callback_data' => 'k=5'],
            ['text' => '6', 'callback_data' => 'k=6'],
        ],
        [
            ['text' => '7', 'callback_data' => 'k=7'],
            ['text' => '8', 'callback_data' => 'k=8'],
            ['text' => '9', 'callback_data' => 'k=9'],
        ],
        [
            ['text' => '0', 'callback_data' => 'k=0'],
        ],
    ]
];

$arrayTest2     =   array('inline_keyboard' => 
                        array(
                            array(
                                array(
                                    'text' => '1',
                                    'callback_data' => 'k=1'
                                ),
                                array(
                                    'text' => '2',
                                    'callback_data' => 'k=2'
                                ),
                                array(
                                    'text' => '3',
                                    'callback_data' => 'k=3'
                                ),
                            ),
                            array(
                                array(
                                    'text' => '1',
                                    'callback_data' => 'k=1'
                                ),
                                array(
                                    'text' => '2',
                                    'callback_data' => 'k=2'
                                ),
                                array(
                                    'text' => '3',
                                    'callback_data' => 'k=3'
                                ),
                            ),
                            
                        ),
                    );
$arrayTest3     =   '{"inline_keyboard":[[{"text":"buzz","callback_data":"print_buzz"}],{"1":{"text":"xgox","callback_data":"print_xgox"}},{"2":{"text":"hold","callback_data":"print_hold"}},{"3":{"text":"magic","callback_data":"print_magic"}},{"4":{"text":"opc","callback_data":"print_opc"}},{"5":{"text":"dvrs","callback_data":"print_dvrs"}},{"6":{"text":"liza","callback_data":"print_liza"}},{"7":{"text":"bullcoin","callback_data":"print_bullcoin"}}]}';
echo '<pre>';
print_r(json_decode($arrayTest3));
echo '</pre>';