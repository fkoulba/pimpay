<?php

class CurrencyValue {
  private $amount;
  private $code;

  public function __construct($amount, $code) {
    $this->amount = $amount;
    $this->code = $code;
  }

  public function add($value) {
    return new Expression($this, $value, 'add');
  }

  public function sub($value) {
    return new Expression($this, $value, 'sub');
  }

  public function mul($value) {
    return new Expression($this, $value, 'mul');
  }

  public function div($value) {
    return new Expression($this, $value, 'div');
  }

  public function describe() {
    return $this->amount . $this->code;
  }

  public function collapse() {
    return [$this->code => $this->amount];
  }

  public function __toString() {
    return $this->describe();
  }
}

class Expression {
  private $value1;
  private $value2;
  private $operation;

  public function __construct($value1, $value2, $operation) {
    $this->value1 = $value1;
    $this->value2 = $value2;
    $this->operation = $operation;
  }

  public function add($value) {
    return new Expression($this, $value, 'add');
  }

  public function sub($value) {
    return new Expression($this, $value, 'sub');
  }

  public function mul($value) {
    return new Expression($this, $value, 'mul');
  }

  public function div($value) {
    return new Expression($this, $value, 'div');
  }

  public function describe() {
    switch ($this->operation) {
    case 'add':
      $description = $this->value1 . ' + ' . $this->value2;
      break;
    case 'sub':
      $description = $this->value1 . ' - ' . $this->value2;
      break;
    case 'mul':
      $description = '(' . $this->value1 . ') * ' . $this->value2;
      break;
    case 'div':
      $description = '(' . $this->value1 . ') / ' . $this->value2;
      break;
    default:
      throw new Exception('Неизвестная операция "' . $this->operation . '"');
    }

    return $description;
  }

  public function collapse() {
    $collapsedValue1 = $this->value1->collapse();

    switch ($this->operation) {
    case 'add':
      $collapsedValue2 = $this->value2->collapse();
      $collapsedResult = $collapsedValue1;
      foreach ($collapsedValue2 as $code => $amount) {
        if (isset($collapsedResult[$code])) {
          $collapsedResult[$code] += $amount;
        } else {
          $collapsedResult[$code] = $amount;
        }
      }
      break;
    case 'sub':
      $collapsedValue2 = $this->value2->collapse();
      $collapsedResult = $collapsedValue1;
      foreach ($collapsedValue2 as $code => $amount) {
        if (isset($collapsedResult[$code])) {
          $collapsedResult[$code] -= $amount;
        } else {
          $collapsedResult[$code] = 0 - $amount;
        }
      }
      break;
    case 'mul':
      $collapsedResult = $collapsedValue1;
      foreach ($collapsedResult as $code => $amount) {
        $collapsedResult[$code] *= $this->value2;
      }
      break;
    case 'div':
      $collapsedResult = $collapsedValue1;
      foreach ($collapsedResult as $code => $amount) {
        $collapsedResult[$code] /= $this->value2;
      }
      break;
    default:
      throw new Exception('Неизвестная операция "' . $this->operation . '"');
    }

    return $collapsedResult;
  }

  public function asFloat($rates) {
    $collapsedResult = $this->collapse();

    $result = 0;

    foreach ($collapsedResult as $code => $amount) {
      $result += $amount * $rates[$code];
    }

    return $result;
  }

  public function __toString() {
    return $this->describe();
  }
}

function RUB($value) {
  return new CurrencyValue($value, 'RUB');
}

function USD($value) {
  return new CurrencyValue($value, 'USD');
}

// NOTE: Скобки не работают в PHP5.5 (https://bugs.php.net/bug.php?id=70663), но с точки зрения алгоритма всё равно погоды не сделают
$expr = (RUB(10)->mul(5)->add(USD(5))->sub(RUB(3)))->mul(2);
// $expr = RUB(10)->mul(5)->add(USD(5))->sub(RUB(3))->mul(2);

echo $expr->describe() . "\n";
print_r($expr->collapse());
echo $expr->asFloat(['RUB' => 1, 'USD' => 63.23]) . "\n";
