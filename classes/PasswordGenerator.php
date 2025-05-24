<?php
class PasswordGenerator {
    private $length;
    private $uppercase;
    private $lowercase;
    private $numbers;
    private $special;
    private $usePercentages;

    public function __construct($length = 12, $uppercase = 3, $lowercase = 3, $numbers = 3, $special = 3, $usePercentages = false) {
        $this->length = $length;
        $this->uppercase = $uppercase;
        $this->lowercase = $lowercase;
        $this->numbers = $numbers;
        $this->special = $special;
        $this->usePercentages = $usePercentages;
    }

    public function generate() {
        $uppercaseChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercaseChars = 'abcdefghijklmnopqrstuvwxyz';
        $numberChars = '0123456789';
        $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $password = '';
        
        if ($this->usePercentages) {
            // Calculate character counts based on percentages
            $uppercaseCount = max(1, round($this->length * ($this->uppercase / 100)));
            $lowercaseCount = max(1, round($this->length * ($this->lowercase / 100)));
            $numbersCount = max(1, round($this->length * ($this->numbers / 100)));
            $specialCount = max(1, round($this->length * ($this->special / 100)));
            
            // Adjust counts to match total length
            $total = $uppercaseCount + $lowercaseCount + $numbersCount + $specialCount;
            if ($total > $this->length) {
                $diff = $total - $this->length;
                $specialCount = max(1, $specialCount - $diff);
            }
        } else {
            // Use absolute quantities
            $uppercaseCount = $this->uppercase;
            $lowercaseCount = $this->lowercase;
            $numbersCount = $this->numbers;
            $specialCount = $this->special;
        }

        // Add required characters
        $password .= $this->getRandomChars($uppercaseChars, $uppercaseCount);
        $password .= $this->getRandomChars($lowercaseChars, $lowercaseCount);
        $password .= $this->getRandomChars($numberChars, $numbersCount);
        $password .= $this->getRandomChars($specialChars, $specialCount);

        // Fill remaining length with random characters from all types
        $remainingLength = $this->length - strlen($password);
        if ($remainingLength > 0) {
            $allChars = $uppercaseChars . $lowercaseChars . $numberChars . $specialChars;
            $password .= $this->getRandomChars($allChars, $remainingLength);
        }

        // Shuffle the password
        return str_shuffle($password);
    }

    private function getRandomChars($chars, $length) {
        $result = '';
        $charLength = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $charLength - 1)];
        }
        return $result;
    }

    public function setParameters($length, $uppercase, $lowercase, $numbers, $special, $usePercentages = false) {
        $this->length = $length;
        $this->uppercase = $uppercase;
        $this->lowercase = $lowercase;
        $this->numbers = $numbers;
        $this->special = $special;
        $this->usePercentages = $usePercentages;
    }

    public function validateParameters() {
        if ($this->usePercentages) {
            $total = $this->uppercase + $this->lowercase + $this->numbers + $this->special;
            return $total <= 100;
        } else {
            $total = $this->uppercase + $this->lowercase + $this->numbers + $this->special;
            return $total <= $this->length;
        }
    }
} 