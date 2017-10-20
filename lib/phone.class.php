<?php


class PhoneExtractor {
    // TODO: use php7 ds extension for Set
    const DIGITS = array(
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

    const PLUS = '+';
    const DEFAULT_CITY_PREFIX = "495";

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

            if ($this->_is_digit($symbol)) {
                $cur_digits[] = $symbol;

                $len = count($cur_digits);

                $is_next_char_digit = $i < $l-1 && $this->_is_digit($input[$i+1]);
                $is_next_char_special = $i < $l-1 && $this->_is_valid_inside_phone($input[$i+1]);

                $is_next_char_valid_inside_phone = $is_next_char_digit || $is_next_char_special;

                $phone = NULL;

                if ($len == 7 && !$is_next_char_valid_inside_phone) {
                    $phone = "8". $this::DEFAULT_CITY_PREFIX . "" . join("", $cur_digits);
                }

                elseif ($len == 11) {
                    if ($cur_digits[0] == "7") {
                        $cur_digits[0] = "8";
                    }
                    $phone = join("", $cur_digits);
                }
                elseif ($len == 10 && $cur_digits[0] != "8") {
                    if ($cur_digits[0] == "7" && $is_next_char_digit) {
                        // 7 is a beginig of the number without +
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

            if ($this->_is_valid_inside_phone($symbol))
                // Ok. inside a phone
                continue;

            // Met some trash inside a phone
            $cur_digits = array();
        }

        return $phones;
    }

    private function _is_digit($symbol) {
        return array_key_exists($symbol, $this::DIGITS);
    }

    private function _is_valid_inside_phone($symbol) {
        return array_key_exists($symbol, $this::VALID_SYMBOLS);
    }
}

