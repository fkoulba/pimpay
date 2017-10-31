<?php

class ExpressionFactory {
  public function buildExpression($name, $args) {
    switch($name) {
      case 'val':
        return new ValueExpression($args[0], $args[1]);
      case 'add':
        return new AddExpression($args[0], $args[1]);
      case 'sub':
        return new SubExpression($args[0], $args[1]);
      case 'mul':
        return new MulExpression($args[0], $args[1]);
      case 'div':
        return new DivExpression($args[0], $args[1]);
      default:
        throw new Exception('Неизвестная операция "' . $name . '"');
    }
  }
}

abstract class Expression {
  abstract public function describe();

  abstract public function collapse();

  public function asFloat(array $rates) {
    $collapsedResult = $this->collapse();

    $result = 0;

    foreach ($collapsedResult as $code => $amount) {
      $result += $amount * $rates[$code];
    }

    return $result;
  }

  public function __call($name, $args) {
    $factory = new ExpressionFactory();
    array_unshift($args, $this);
    return $factory->buildExpression($name, $args);
  }

  public function __toString() {
    return $this->describe();
  }
}

final class ValueExpression extends Expression {
  private $amount;
  private $code;

  public function __construct(float $amount, string $code) {
    $this->amount = $amount;
    $this->code = $code;
  }

  public function describe() {
    return $this->amount . $this->code;
  }

  public function collapse() {
    return [$this->code => $this->amount];
  }
}

final class AddExpression extends Expression {
  private $value1;
  private $value2;

  public function __construct(Expression $value1, Expression $value2) {
    $this->value1 = $value1;
    $this->value2 = $value2;
  }

  public function describe() {
    return $this->value1 . ' + ' . $this->value2;
  }

  public function collapse() {
    $collapsedValue1 = $this->value1->collapse();
    $collapsedValue2 = $this->value2->collapse();
    $collapsedResult = $collapsedValue1;
    foreach ($collapsedValue2 as $code => $amount) {
      if (isset($collapsedResult[$code])) {
        $collapsedResult[$code] += $amount;
      } else {
        $collapsedResult[$code] = $amount;
      }
    }
    return $collapsedResult;
  }
}

final class SubExpression extends Expression {
  private $value1;
  private $value2;

  public function __construct(Expression $value1, Expression $value2) {
    $this->value1 = $value1;
    $this->value2 = $value2;
  }

  public function describe() {
    return $this->value1 . ' - ' . $this->value2;
  }

  public function collapse() {
    $collapsedValue1 = $this->value1->collapse();
    $collapsedValue2 = $this->value2->collapse();
    $collapsedResult = $collapsedValue1;
    foreach ($collapsedValue2 as $code => $amount) {
      if (isset($collapsedResult[$code])) {
        $collapsedResult[$code] -= $amount;
      } else {
        $collapsedResult[$code] = $amount;
      }
    }
    return $collapsedResult;
  }
}

final class MulExpression extends Expression {
  private $value1;
  private $value2;

  public function __construct(Expression $value1, float $value2) {
    $this->value1 = $value1;
    $this->value2 = $value2;
  }

  public function describe() {
    return '(' . $this->value1 . ') * ' . $this->value2;
  }

  public function collapse() {
    $collapsedResult = $this->value1->collapse();
    foreach ($collapsedResult as $code => $amount) {
      $collapsedResult[$code] = $amount * $this->value2;
    }
    return $collapsedResult;
  }
}

final class DivExpression extends Expression {
  private $value1;
  private $value2;

  public function __construct(Expression $value1, float $value2) {
    $this->value1 = $value1;
    $this->value2 = $value2;
  }

  public function describe() {
    return '(' . $this->value1 . ') / ' . $this->value2;
  }

  public function collapse() {
    $collapsedResult = $this->value1->collapse();
    foreach ($collapsedResult as $code => $amount) {
      $collapsedResult[$code] = $amount / $this->value2;
    }
    return $collapsedResult;
  }
}

function RUB($value) {
  $factory = new ExpressionFactory();
  return $factory->buildExpression('val', [$value, 'RUB']);
}

function USD($value) {
  $factory = new ExpressionFactory();
  return $factory->buildExpression('val', [$value, 'USD']);
}

$expr = (RUB(10)->mul(5)->add(USD(5))->sub(RUB(3)))->mul(2);

echo $expr->describe() . "\n";

print_r($expr->collapse());

echo $expr->asFloat(['RUB' => 1, 'USD' => 63.23]) . "\n";
