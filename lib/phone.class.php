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

        $curDigits = array();

        $l = strlen($input);
        for ($i = 0; $i < $l; $i++){
            $symbol = $input[$i];

            if ($this->isDigit($symbol)) {
                $curDigits[] = $symbol;

                $len = count($curDigits);

                $isNextCharDigit = $i < $l-1 && $this->isDigit($input[$i+1]);
                $isNextCharSpecial = $i < $l-1 && $this->isValidInsidePhone($input[$i+1]);

                $isNextCharValidInsidePhone = $isNextCharDigit || $isNextCharSpecial;

                $phone = NULL;

                if ($len == 7 && !$isNextCharValidInsidePhone) {
                    $phone = "8". $this::DEFAULT_CITY_PREFIX . "" . join("", $curDigits);
                }

                elseif ($len == 11) {
                    if ($curDigits[0] == "7") {
                        $curDigits[0] = "8";
                    }
                    $phone = join("", $curDigits);
                }
                elseif ($len == 10 && $curDigits[0] != "8") {
                    if ($curDigits[0] == "7" && $isNextCharDigit) {
                        // 7 is a beginig of the number without +
                        continue;
                    }

                    $phone = "8" . join("", $curDigits);
                }

                if ($phone) {
                    $curDigits = array();
                    if ($isNextCharDigit) {
                        // this is trash, we have digits more
                        continue;
                    }
                    $phones[] = $phone;
                }

                continue;
            }

            if ($this->isValidInsidePhone($symbol))
                // Ok. inside a phone
                continue;

            // Met some trash inside a phone
            $cur_digits = array();
        }

        return $phones;
    }

    private function isDigit($symbol) {
        return array_key_exists($symbol, $this::DIGITS);
    }

    private function isValidInsidePhone($symbol) {
        return array_key_exists($symbol, $this::VALID_SYMBOLS);
    }
}

