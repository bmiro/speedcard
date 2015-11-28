<?php

function ucard2number($ucard) {
    /* Return the number of the given $ucard */
    return hexdec(substr($ucard, -1));
}

function ucard2suit($ucard) {
    /* Return the suit of the given $ucard */
    $suit_code = [
        "A" => "spades",
        "B" => "hearts",
        "C" => "diamonds",
        "D" => "clubs"
    ];
    return $suit_code[substr($ucard, -2, -1)];
}

function ucard2words($ucard) {
    /* @return the human readeable string  (in words) of the given ucard
     */

    $suit = ucard2suit($ucard);
    $number = ucard2number($ucard);

    switch ($number) {
        case 1:
            $number = "ace";
            break;
        case 11:
            $number = "jack";
            break;
        case 12:
            $number = "knight";
            break;
        case 13:
            $number = "queen";
            break;
        case 14:
            $number = "king";
            break;
    }

    return $number . " of " . $suit;    

}

function ucard2html($ucard) {
    /* @return the card in html encoded unicode
    */
    return "&#x$ucard;";
}

function random_card() {
    /* @return random card with Unicode codification:
     * https://en.wikipedia.org/wiki/Playing_cards_in_Unicode
     * 1F0A1 -> 1 of Spades 
     * 1F0A2 -> 2 of Spades 
     * ...   
     * 1F0AE -> K of Spades   
     *
     * 1F0B1 -> 1 of Heards 
     * 1F0C1 -> 1 of Diamonds 
     * 1F0D1 -> 1 of Clubs
     *
     * 1F0A0 -> Card Back
     * 1F0CF -> Black Joker
     * 1F0DF -> White Joker
     */

    $suit = rand(0, 3);
    $suit_code = chr(ord("A") + $suit);
    $number_code = dechex(rand(1, 14));    
   
    $card_code = strtoupper("1F0$suit_code$number_code");
    return $card_code;
}

function controller($args) {
    $ucard = random_card();
    $card_name = t(ucard2words($ucard));
    $card_icon = ucard2html($ucard);
    $card_suit = ucard2suit($ucard);

    page_set_title("$card_icon $card_name");

    $tpl_params = [
        'card_name' => $card_name,
        'card_icon' => $card_icon,
        'card_suit' => $card_suit,
    ];

    $tpl = new Template();
    $tpl->add('main', 'blocks/speedcard', $tpl_params);
    $tpl->setTPL('page');
    return $tpl->render();
}
