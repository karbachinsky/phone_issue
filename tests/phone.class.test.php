<?php
use PHPUnit\Framework\TestCase;



class PhoneExtractorTest extends TestCase
{
    public function testExtract()
    {
        $samples = array(
            // Sample => Expected
            "89031234567"             => ["89031234567"],
            "+79031234567"            => ["89031234567"],
            "+7 9031234567"           => ["89031234567"],

            # There are people who don't specify leading 8 or +7
            "9031234567"              => ["89031234567"],

            # This is trash
            "1234567891234566"        => [],

            "+7 903 1234568"          => ["89031234568"],
            "+7 903 123 45 68"        => ["89031234568"],
            "+7 903 123-45-69"        => ["89031234569"],
            "+7(903)123-45-69"        => ["89031234569"],
            "8 927 12 555 12"         => ["89271255512"],

            # Saratov. When region + city prefix consists of 4 digits
            "8 8452 121212"           => ["88452121212"],

            "8 9 2 7 1 2 5 5 5 1 2"   => ["89271255512"],
            "+7 (495) 123-45-69"      => ["84951234569"],
            "trash89031234567trash"   => ["89031234567"],
            "trash 89031234567 trash" => ["89031234567"],
            ""                        => [],
            " "                       => [],
            # Can't consider it as a phone. Better to ask input again
            "8 903 abc 1234567"       => [],

            # No leading +
            "7 119 421-68-67"         => ["81194216867"],

            // Multiple phones cases
            "89031234567 89051234567" => ["89031234567", "89051234567"],
            "my phones are 89031234567 and 89051234567" => ["89031234567", "89051234567"],
            "my phones are 8(903) 123-45-77 and 8 905 123 55 67" => ["89031234577", "89051235567"],
        );

        $phoneExtractor = new PhoneExtractor();

        foreach ($samples as $sample => $expectedPhones) {
            $phones = $phoneExtractor->extract($sample);
            $this->assertEquals($expectedPhones, $phones);
        }
    }
}

