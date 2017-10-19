<?php


class PhoneExtractor {
    // TODO: use php7 ds extension for Set
    const DIGITS = array(
      "+" => true,
      "0" => true,
      "1" => true,
      "2" => true,
      "3" => true,
      "4" => true,
      "5" => true,
      "6" => true,
      "7" => true,
      "8" => true,
      "9" => true,
    );

    const VALID_SYMBOLS = array(
       "(" => true,
       ")" => true,
       "-" => true,
       "+" => true,
       " " => true,
       "\t" => true,
    );

    /**
     * Extract phones from raw string
     * @param string $input
     * @return array of phones
     */
    public function extract(string $input) {
        $phones = [];

        $cur_digits = array();

        $l = strlen($input);
        for ($i = 0; $i < $l; $i++){
            $symbol = $input[$i];

            if (array_key_exists($symbol, $this::DIGITS)) {
                $cur_digits[] = $symbol;

                $len = count($cur_digits);

                $is_next_char_digit = $i < $l-1 && array_key_exists($input[$i+1], $this::DIGITS);
                $phone = NULL;

                // +7 ?
                if ($len == 12) {
                    # FIXME: array_slice is not optimal
                    $phone = "8" . join("", array_slice($cur_digits, 2) );
                }
                elseif ($len == 11 && $cur_digits[0] != "+") {
                    $phone = join("", $cur_digits);
                }
                elseif ($len == 10 && $cur_digits[0] != "+" && $cur_digits[0] != "8") {
                    if ($cur_digits[0] == "7" && $is_next_char_digit) {
                        // 7 is a beginig of the number without +
                        array_unshift($cur_digits, "+");
                        continue;
                    }

                    $phone = "8" . join("", $cur_digits);
                }

                if ($phone) {
                    $cur_digits = array();
                    if ($is_next_char_digit) {
                        // this is trash, we have digits more
                        continue;
                    }
                    $phones[] = $phone;
                }

                continue;
            }

            if (array_key_exists($symbol, $this::VALID_SYMBOLS))
                // Ok. inside a phone
                continue;

            // Met some trash inside a phone
            $cur_digits = array();
        }

        return $phones;

    }

}

