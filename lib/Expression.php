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
        throw new Exception('Неизвестный тип выражения "' . $name . '"!');
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

  public function getAmount() {
    return $this->amount;
  }

  public function getCode() {
    return $this->code;
  }

  public function describe() {
    return $this->amount . $this->code;
  }

  public function collapse() {
    return [$this->code => $this->amount];
  }
}

final class AddExpression extends Expression {
  private $term1;
  private $term2;

  public function __construct(Expression $term1, Expression $term2) {
    $this->term1 = $term1;
    $this->term2 = $term2;
  }

  public function getTerm1() {
    return $this->term1;
  }

  public function getTerm2() {
    return $this->term2;
  }

  public function describe() {
    return $this->term1 . ' + ' . $this->term2;
  }

  public function collapse() {
    $collapsedResult = $this->term1->collapse();
    foreach ($this->term2->collapse() as $code => $amount) {
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
  private $subtrahend;
  private $minuend;

  public function __construct(Expression $subtrahend, Expression $minuend) {
    $this->subtrahend = $subtrahend;
    $this->minuend = $minuend;
  }

  public function getSubtrahend() {
    return $this->subtrahend;
  }

  public function getMinuend() {
    return $this->minuend;
  }

  public function describe() {
    return $this->subtrahend . ' - ' . $this->minuend;
  }

  public function collapse() {
    $collapsedResult = $this->subtrahend->collapse();
    foreach ($this->minuend->collapse() as $code => $amount) {
      if (isset($collapsedResult[$code])) {
        $collapsedResult[$code] -= $amount;
      } else {
        $collapsedResult[$code] = -$amount;
      }
    }
    return $collapsedResult;
  }
}

final class MulExpression extends Expression {
  private $term1;
  private $term2;

  public function __construct(Expression $term1, float $term2) {
    $this->term1 = $term1;
    $this->term2 = $term2;
  }

  public function getTerm1() {
    return $this->term1;
  }

  public function getTerm2() {
    return $this->term2;
  }

  public function describe() {
    return '(' . $this->term1 . ') * ' . $this->term2;
  }

  public function collapse() {
    $collapsedResult = $this->term1->collapse();
    foreach ($collapsedResult as $code => $amount) {
      $collapsedResult[$code] = $amount * $this->term2;
    }
    return $collapsedResult;
  }
}

final class DivExpression extends Expression {
  private $dividend;
  private $divisor;

  public function __construct(Expression $dividend, float $divisor) {
    $this->dividend = $dividend;
    $this->divisor = $divisor;
  }

  public function getDividend() {
    return $this->dividend;
  }

  public function getDivisor() {
    return $this->divisor;
  }

  public function describe() {
    return '(' . $this->dividend . ') / ' . $this->divisor;
  }

  public function collapse() {
    $collapsedResult = $this->dividend->collapse();
    foreach ($collapsedResult as $code => $amount) {
      $collapsedResult[$code] = $amount / $this->divisor;
    }
    return $collapsedResult;
  }
}
